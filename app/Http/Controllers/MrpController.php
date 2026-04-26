<?php

namespace App\Http\Controllers;

use App\Models\MrpPlan;
use App\Models\Product;
use Illuminate\Http\Request;

class MrpController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index()
    {
        $plans = MrpPlan::with('product')->where('tenant_id', $this->tid())->orderByDesc('planned_date')->paginate(20);
        $base  = MrpPlan::where('tenant_id', $this->tid());
        $stats = [
            'ouvert'   => (clone $base)->where('status', 'ouvert')->count(),
            'confirme' => (clone $base)->where('status', 'confirme')->count(),
            'shortage' => (clone $base)->where('shortage', '>', 0)->count(),
            'total'    => (clone $base)->count(),
        ];
        return view('mrp.index', compact('plans', 'stats'));
    }

    public function create()
    {
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('mrp.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'quantity_needed' => 'required|numeric|min:0.01',
            'planned_date'    => 'required|date',
            'status'          => 'required|in:ouvert,confirme,cloture',
            'notes'           => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        abort_if($product->tenant_id !== $this->tid(), 403);

        $data['tenant_id']          = $this->tid();
        $data['quantity_available'] = $product->quantity_in_stock;
        $data['shortage']           = max(0, $data['quantity_needed'] - $data['quantity_available']);

        MrpPlan::create($data);

        $msg = $data['shortage'] > 0
            ? 'Plan MRP créé. Manque détecté : ' . number_format($data['shortage'], 2) . ' ' . $product->unit
            : 'Plan MRP créé. Stock suffisant.';

        return redirect()->route('mrp.index')->with('success', $msg);
    }

    public function recalculate()
    {
        $plans = MrpPlan::where('tenant_id', $this->tid())->where('status', '!=', 'cloture')->with('product')->get();
        foreach ($plans as $plan) {
            $qty = $plan->product->quantity_in_stock;
            $plan->update([
                'quantity_available' => $qty,
                'shortage'           => max(0, $plan->quantity_needed - $qty),
            ]);
        }
        return back()->with('success', $plans->count() . ' plan(s) recalculé(s) depuis le stock actuel.');
    }

    public function edit(MrpPlan $mrp)
    {
        abort_if($mrp->tenant_id !== $this->tid(), 403);
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('mrp.edit', compact('mrp', 'products'));
    }

    public function update(Request $request, MrpPlan $mrp)
    {
        abort_if($mrp->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'product_id'      => 'required|exists:products,id',
            'quantity_needed' => 'required|numeric|min:0.01',
            'planned_date'    => 'required|date',
            'status'          => 'required|in:ouvert,confirme,cloture',
            'notes'           => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        abort_if($product->tenant_id !== $this->tid(), 403);

        $data['quantity_available'] = $product->quantity_in_stock;
        $data['shortage']           = max(0, $data['quantity_needed'] - $data['quantity_available']);

        $mrp->update($data);
        return redirect()->route('mrp.index')->with('success', 'Plan MRP mis à jour.');
    }

    public function destroy(MrpPlan $mrp)
    {
        abort_if($mrp->tenant_id !== $this->tid(), 403);
        $mrp->delete();
        return redirect()->route('mrp.index')->with('success', 'Plan supprimé.');
    }
}
