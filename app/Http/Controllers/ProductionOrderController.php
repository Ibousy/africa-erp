<?php

namespace App\Http\Controllers;

use App\Models\BomItem;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionTask;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = ProductionOrder::with(['product', 'user'])->where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);
        $orders = $query->latest()->paginate(15)->withQueryString();
        return view('production.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('production.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity_planned' => 'required|numeric|min:0.01',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'notes'            => 'nullable|string',
        ]);

        $count = ProductionOrder::where('tenant_id', $this->tid())->count();
        $data['reference'] = 'OF-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $data['tenant_id'] = $this->tid();
        $data['user_id']   = auth()->id();
        $data['status']    = 'brouillon';

        ProductionOrder::create($data);
        return redirect()->route('production.index')->with('success', 'Ordre de fabrication créé.');
    }

    public function show(ProductionOrder $production)
    {
        abort_if($production->tenant_id !== $this->tid(), 403);
        $production->load(['product.bomItems.component', 'user', 'tasks.assignedUser', 'qualityControls.product']);
        $users = User::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        return view('production.show', compact('production', 'users'));
    }

    public function edit(ProductionOrder $production)
    {
        abort_if($production->tenant_id !== $this->tid(), 403);
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('production.edit', compact('production', 'products'));
    }

    public function update(Request $request, ProductionOrder $production)
    {
        abort_if($production->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'product_id'        => 'required|exists:products,id',
            'quantity_planned'  => 'required|numeric|min:0.01',
            'quantity_produced' => 'nullable|numeric|min:0',
            'status'            => 'required|in:brouillon,en_cours,termine,annule',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date',
            'notes'             => 'nullable|string',
        ]);
        $wasTermine = $production->status === 'termine';
        $production->update($data);

        // Auto-deduct BOM components and add finished product to stock when first marked terminé
        if (!$wasTermine && $data['status'] === 'termine') {
            $qty = $data['quantity_produced'] ?? $production->quantity_produced;
            $production->load('product.bomItems.component');

            foreach ($production->product->bomItems as $bom) {
                $consumed = $bom->quantity * $qty;
                $bom->component->decrement('quantity_in_stock', $consumed);
                StockMovement::create([
                    'tenant_id'  => $this->tid(),
                    'product_id' => $bom->component_id,
                    'user_id'    => auth()->id(),
                    'type'       => 'sortie',
                    'quantity'   => $consumed,
                    'reason'     => 'Consommation OF ' . $production->reference,
                ]);
            }

            // Add finished product to stock
            $production->product->increment('quantity_in_stock', $qty);
            StockMovement::create([
                'tenant_id'  => $this->tid(),
                'product_id' => $production->product_id,
                'user_id'    => auth()->id(),
                'type'       => 'entree',
                'quantity'   => $qty,
                'reason'     => 'Production OF ' . $production->reference,
            ]);
        }

        return redirect()->route('production.show', $production)->with('success', 'Ordre mis à jour.');
    }

    public function destroy(ProductionOrder $production)
    {
        abort_if($production->tenant_id !== $this->tid(), 403);
        $production->delete();
        return redirect()->route('production.index')->with('success', 'Ordre supprimé.');
    }

    public function costs()
    {
        // All finished products that have a BOM
        $products = Product::where('tenant_id', $this->tid())
            ->where('status', 'actif')
            ->with(['bomItems.component'])
            ->whereHas('bomItems')
            ->orderBy('name')
            ->get();

        // Calculate theoretical cost per product from BOM
        $productCosts = $products->map(function ($product) {
            $bomCost = $product->bomItems->sum(fn($b) => $b->quantity * ($b->component->unit_price ?? 0));
            return [
                'product'      => $product,
                'bom_cost'     => $bomCost,
                'sell_price'   => $product->unit_price ?? 0,
                'margin'       => ($product->unit_price ?? 0) - $bomCost,
                'margin_pct'   => ($product->unit_price ?? 0) > 0
                    ? round((($product->unit_price - $bomCost) / $product->unit_price) * 100, 1)
                    : 0,
            ];
        });

        // Production orders stats (terminated this year)
        $ordersThisYear = ProductionOrder::where('tenant_id', $this->tid())
            ->where('status', 'termine')
            ->whereYear('updated_at', now()->year)
            ->with('product')
            ->latest()
            ->limit(20)
            ->get();

        // Monthly production count
        $monthlyProduction = ProductionOrder::where('tenant_id', $this->tid())
            ->where('status', 'termine')
            ->whereYear('updated_at', now()->year)
            ->selectRaw('MONTH(updated_at) as month, COUNT(*) as count, SUM(quantity_produced) as qty')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        return view('production.costs', compact('productCosts', 'ordersThisYear', 'monthlyProduction'));
    }

    public function storeTask(Request $request, ProductionOrder $production)
    {
        abort_if($production->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        $data['production_order_id'] = $production->id;
        ProductionTask::create($data);
        return back()->with('success', 'Tâche ajoutée.');
    }

    public function updateTask(Request $request, ProductionTask $task)
    {
        $data = $request->validate(['status' => 'required|in:todo,en_cours,fait']);
        if ($data['status'] === 'en_cours' && !$task->started_at)   $data['started_at']   = now();
        if ($data['status'] === 'fait'     && !$task->completed_at) $data['completed_at'] = now();
        $task->update($data);
        return back()->with('success', 'Tâche mise à jour.');
    }
}
