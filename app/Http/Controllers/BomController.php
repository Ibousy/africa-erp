<?php

namespace App\Http\Controllers;

use App\Models\BomItem;
use App\Models\Product;
use Illuminate\Http\Request;

class BomController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function show(Product $product)
    {
        abort_if($product->tenant_id !== $this->tid(), 403);
        $product->load('bomItems.component');

        $components = Product::where('tenant_id', $this->tid())
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get();

        return view('production.bom', compact('product', 'components'));
    }

    public function store(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'component_id' => 'required|exists:products,id|different:' . $product->id,
            'quantity'     => 'required|numeric|min:0.0001',
        ]);

        // Verify component belongs to this tenant
        $component = Product::where('id', $data['component_id'])
            ->where('tenant_id', $this->tid())
            ->firstOrFail();

        BomItem::updateOrCreate(
            ['product_id' => $product->id, 'component_id' => $component->id],
            ['tenant_id' => $this->tid(), 'quantity' => $data['quantity']]
        );

        return back()->with('success', 'Composant ajouté à la nomenclature.');
    }

    public function destroy(Product $product, BomItem $bom)
    {
        abort_if($product->tenant_id !== $this->tid(), 403);
        abort_if($bom->product_id !== $product->id, 403);
        $bom->delete();
        return back()->with('success', 'Composant retiré.');
    }
}
