<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class LogisticsController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Shipment::with('product')->where('tenant_id', $this->tid());
        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);

        $shipments = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $base  = Shipment::where('tenant_id', $this->tid());
        $stats = [
            'total'      => (clone $base)->count(),
            'en_attente' => (clone $base)->where('status', 'en_attente')->count(),
            'en_transit' => (clone $base)->where('status', 'en_transit')->count(),
            'livre'      => (clone $base)->where('status', 'livre')->count(),
        ];

        return view('logistics.index', compact('shipments', 'stats'));
    }

    public function create()
    {
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('logistics.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reference'          => 'required|string|max:50',
            'product_id'         => 'nullable|exists:products,id',
            'quantity'           => 'nullable|numeric|min:0.01',
            'type'               => 'required|in:entrant,sortant,retour',
            'carrier'            => 'nullable|string|max:100',
            'contact_name'       => 'required|string|max:191',
            'origin_destination' => 'required|string|max:191',
            'status'             => 'required|in:en_attente,en_transit,livre,annule',
            'departure_date'     => 'nullable|date',
            'arrival_date'       => 'nullable|date',
            'weight_kg'          => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $data['tenant_id'] = $this->tid();
        $shipment = Shipment::create($data);

        if ($data['status'] === 'livre' && !empty($data['product_id']) && !empty($data['quantity'])) {
            $this->processStockMovement($shipment);
        }

        return redirect()->route('logistics.index')->with('success', 'Expédition enregistrée.');
    }

    public function updateStatus(Request $request, Shipment $logistic)
    {
        abort_if($logistic->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:en_attente,en_transit,livre,annule']);

        $logistic->status = $request->status;

        if ($request->status === 'livre' && $logistic->product_id && $logistic->quantity > 0 && !$logistic->stock_processed) {
            $this->processStockMovement($logistic);
        }

        $logistic->save();
        return back()->with('success', 'Statut mis à jour.');
    }

    public function document(Shipment $logistic)
    {
        abort_if($logistic->tenant_id !== $this->tid(), 403);
        $logistic->load('product');
        $tenant = auth()->user()->load('tenant')->tenant;
        return view('logistics.document', compact('logistic', 'tenant'));
    }

    public function stock(Request $request)
    {
        $query = Product::where('tenant_id', $this->tid())->where('status', 'actif');
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('alert') && $request->alert === '1') {
            $query->whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0);
        }

        $products   = $query->orderBy('category')->orderBy('name')->get();
        $categories = Product::where('tenant_id', $this->tid())->distinct()->pluck('category')->filter()->sort()->values();

        $stats = [
            'total_products' => Product::where('tenant_id', $this->tid())->where('status', 'actif')->count(),
            'low_stock'      => Product::where('tenant_id', $this->tid())->where('status', 'actif')
                ->whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0)->count(),
            'total_value'    => Product::where('tenant_id', $this->tid())->where('status', 'actif')
                ->selectRaw('SUM(quantity_in_stock * unit_price) as val')->value('val') ?? 0,
        ];

        return view('logistics.stock', compact('products', 'categories', 'stats'));
    }

    public function edit(Shipment $logistic)
    {
        abort_if($logistic->tenant_id !== $this->tid(), 403);
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('logistics.edit', compact('logistic', 'products'));
    }

    public function update(Request $request, Shipment $logistic)
    {
        abort_if($logistic->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'reference'          => 'required|string|max:50',
            'product_id'         => 'nullable|exists:products,id',
            'quantity'           => 'nullable|numeric|min:0.01',
            'type'               => 'required|in:entrant,sortant,retour',
            'carrier'            => 'nullable|string|max:100',
            'contact_name'       => 'required|string|max:191',
            'origin_destination' => 'required|string|max:191',
            'status'             => 'required|in:en_attente,en_transit,livre,annule',
            'departure_date'     => 'nullable|date',
            'arrival_date'       => 'nullable|date',
            'weight_kg'          => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $logistic->update($data);
        return redirect()->route('logistics.index')->with('success', 'Expédition mise à jour.');
    }

    public function destroy(Shipment $logistic)
    {
        abort_if($logistic->tenant_id !== $this->tid(), 403);
        $logistic->delete();
        return redirect()->route('logistics.index')->with('success', 'Expédition supprimée.');
    }

    private function processStockMovement(Shipment $shipment): void
    {
        if (!$shipment->product_id || !$shipment->quantity) return;

        $movementType = in_array($shipment->type, ['entrant', 'retour']) ? 'entree' : 'sortie';

        StockMovement::create([
            'tenant_id'  => $shipment->tenant_id,
            'product_id' => $shipment->product_id,
            'type'       => $movementType,
            'quantity'   => $shipment->quantity,
            'reference'  => $shipment->reference,
            'reason'     => ucfirst($shipment->type) . ' — ' . $shipment->contact_name,
            'user_id'    => auth()->id(),
        ]);

        $product = Product::find($shipment->product_id);
        if ($movementType === 'entree') {
            $product->increment('quantity_in_stock', $shipment->quantity);
        } else {
            $product->decrement('quantity_in_stock', max(0, $shipment->quantity));
        }

        $shipment->stock_processed = true;
    }
}
