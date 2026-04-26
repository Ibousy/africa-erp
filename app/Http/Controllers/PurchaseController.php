<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier')->where('tenant_id', $this->tid());
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->supplier_id);

        $orders    = $query->orderByDesc('order_date')->paginate(20)->withQueryString();
        $suppliers = Supplier::where('tenant_id', $this->tid())->orderBy('name')->get();

        $base  = PurchaseOrder::where('tenant_id', $this->tid());
        $stats = [
            'total'     => (clone $base)->count(),
            'brouillon' => (clone $base)->where('status', 'brouillon')->count(),
            'en_cours'  => (clone $base)->whereIn('status', ['envoye', 'confirme'])->count(),
            'recu'      => (clone $base)->where('status', 'recu')->count(),
        ];

        return view('purchases.index', compact('orders', 'suppliers', 'stats'));
    }

    public function create()
    {
        $suppliers = Supplier::where('tenant_id', $this->tid())->orderBy('name')->get();
        $products  = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'reference'            => 'required|string|max:50',
            'status'               => 'required|in:brouillon,envoye,confirme,recu,annule',
            'order_date'           => 'required|date',
            'expected_date'        => 'nullable|date',
            'notes'                => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.description'  => 'required|string|max:255',
            'items.*.quantity'     => 'required|numeric|min:0.0001',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.product_id'   => 'nullable|exists:products,id',
        ]);

        $total = collect($request->items)
            ->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));

        $order = PurchaseOrder::create([
            'tenant_id'     => $this->tid(),
            'supplier_id'   => $request->supplier_id,
            'reference'     => $request->reference,
            'status'        => $request->status,
            'order_date'    => $request->order_date,
            'expected_date' => $request->expected_date,
            'total_amount'  => $total,
            'notes'         => $request->notes,
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id'        => $item['product_id'] ?: null,
                'description'       => $item['description'],
                'quantity'          => $item['quantity'],
                'unit_price'        => $item['unit_price'],
            ]);
        }

        if ($request->status === 'recu') {
            $this->receiveStock($order);
        }

        return redirect()->route('purchases.show', $order)->with('success', 'Commande fournisseur créée.');
    }

    public function show(PurchaseOrder $purchase)
    {
        abort_if($purchase->tenant_id !== $this->tid(), 403);
        $purchase->load(['supplier', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchase)
    {
        abort_if($purchase->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:brouillon,envoye,confirme,recu,annule']);

        $wasRecu = $purchase->status === 'recu';
        $purchase->update(['status' => $request->status]);

        if (!$wasRecu && $request->status === 'recu') {
            $purchase->load('items.product');
            $this->receiveStock($purchase);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    private function receiveStock(PurchaseOrder $order): void
    {
        foreach ($order->items as $item) {
            if (!$item->product_id) continue;
            $product = $item->product ?? Product::find($item->product_id);
            if (!$product) continue;

            $product->increment('quantity_in_stock', $item->quantity);
            StockMovement::create([
                'tenant_id'  => $this->tid(),
                'product_id' => $product->id,
                'user_id'    => auth()->id(),
                'type'       => 'entree',
                'quantity'   => $item->quantity,
                'reason'     => 'Réception BC ' . $order->reference,
            ]);
        }
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:191',
            'contact_name' => 'nullable|string|max:191',
            'email'        => 'nullable|email|max:191',
            'phone'        => 'nullable|string|max:30',
            'country'      => 'nullable|string|max:100',
            'address'      => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
        ]);

        Supplier::create(array_merge(
            $request->only(['name', 'contact_name', 'email', 'phone', 'country', 'address', 'notes']),
            ['tenant_id' => $this->tid()]
        ));

        return redirect()->route('purchases.index')->with('success', 'Fournisseur ajouté.');
    }

    public function destroy(PurchaseOrder $purchase)
    {
        abort_if($purchase->tenant_id !== $this->tid(), 403);
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Commande supprimée.');
    }
}
