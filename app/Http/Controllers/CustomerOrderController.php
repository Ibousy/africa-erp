<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = CustomerOrder::with('client')->where('tenant_id', $this->tid());
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $orders  = $query->latest()->paginate(20)->withQueryString();
        $clients = Client::where('tenant_id', $this->tid())->orderBy('name')->get();

        $base  = CustomerOrder::where('tenant_id', $this->tid());
        $stats = [
            'total'    => (clone $base)->count(),
            'nouveau'  => (clone $base)->where('status', 'nouveau')->count(),
            'en_cours' => (clone $base)->whereIn('status', ['confirme', 'en_preparation'])->count(),
            'livre'    => (clone $base)->where('status', 'livre')->count(),
            'ca'       => (clone $base)->whereNotIn('status', ['annule'])->sum('total_amount'),
        ];

        return view('sales.orders.index', compact('orders', 'clients', 'stats'));
    }

    public function create()
    {
        $clients  = Client::where('tenant_id', $this->tid())->orderBy('name')->get();
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('sales.orders.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'order_date'       => 'required|date',
            'delivery_date'    => 'nullable|date',
            'delivery_address' => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.0001',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.product_id'  => 'nullable|exists:products,id',
        ]);

        Client::where('id', $request->client_id)->where('tenant_id', $this->tid())->firstOrFail();

        $count = CustomerOrder::where('tenant_id', $this->tid())->count();
        $ref   = 'CMD-' . date('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $total = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_price']);

        $order = CustomerOrder::create([
            'tenant_id'        => $this->tid(),
            'client_id'        => $request->client_id,
            'reference'        => $ref,
            'status'           => 'nouveau',
            'order_date'       => $request->order_date,
            'delivery_date'    => $request->delivery_date,
            'total_amount'     => $total,
            'delivery_address' => $request->delivery_address,
            'notes'            => $request->notes,
        ]);

        foreach ($request->items as $item) {
            CustomerOrderItem::create([
                'customer_order_id' => $order->id,
                'product_id'        => $item['product_id'] ?: null,
                'description'       => $item['description'],
                'quantity'          => $item['quantity'],
                'unit_price'        => $item['unit_price'],
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Commande ' . $ref . ' créée.');
    }

    public function show(CustomerOrder $order)
    {
        abort_if($order->tenant_id !== $this->tid(), 403);
        $order->load(['client', 'items.product', 'quote']);
        return view('sales.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, CustomerOrder $order)
    {
        abort_if($order->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:nouveau,confirme,en_preparation,livre,annule']);

        $wasLivre = $order->status === 'livre';
        $order->update(['status' => $request->status]);

        // Deduct stock on delivery
        if (!$wasLivre && $request->status === 'livre') {
            $order->load('items.product');
            foreach ($order->items as $item) {
                if (!$item->product_id) continue;
                $item->product->decrement('quantity_in_stock', $item->quantity);
                StockMovement::create([
                    'tenant_id'  => $this->tid(),
                    'product_id' => $item->product_id,
                    'user_id'    => auth()->id(),
                    'type'       => 'sortie',
                    'quantity'   => $item->quantity,
                    'reason'     => 'Livraison ' . $order->reference,
                ]);
            }
            // Add loyalty points to client
            $points = (int) ($order->total_amount / 1000);
            $order->client->increment('loyalty_points', $points);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(CustomerOrder $order)
    {
        abort_if($order->tenant_id !== $this->tid(), 403);
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Commande supprimée.');
    }
}
