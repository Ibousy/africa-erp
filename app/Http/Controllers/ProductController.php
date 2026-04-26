<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Product::where('tenant_id', $this->tid());
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->get('low_stock')) {
            $query->whereColumn('quantity_in_stock', '<=', 'min_stock_alert')->where('min_stock_alert', '>', 0);
        }
        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Product::where('tenant_id', $this->tid())->distinct()->pluck('category')->filter()->sort()->values();
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Product::where('tenant_id', $this->tid())->distinct()->pluck('category')->filter()->sort()->values();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'              => 'required|string|unique:products',
            'name'              => 'required|string|max:255',
            'category'          => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'unit'              => 'required|string|max:20',
            'quantity_in_stock' => 'required|numeric|min:0',
            'min_stock_alert'   => 'required|numeric|min:0',
            'unit_price'        => 'required|numeric|min:0',
            'status'            => 'required|in:actif,inactif',
        ]);
        $data['tenant_id'] = $this->tid();
        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Produit créé avec succès.');
    }

    public function edit(Product $product)
    {
        abort_if($product->tenant_id !== $this->tid(), 403);
        $categories = Product::where('tenant_id', $this->tid())->distinct()->pluck('category')->filter()->sort()->values();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== $this->tid(), 403);
        $data = $request->validate([
            'code'              => ['required', 'string', Rule::unique('products')->ignore($product->id)],
            'name'              => 'required|string|max:255',
            'category'          => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'unit'              => 'required|string|max:20',
            'quantity_in_stock' => 'required|numeric|min:0',
            'min_stock_alert'   => 'required|numeric|min:0',
            'unit_price'        => 'required|numeric|min:0',
            'status'            => 'required|in:actif,inactif',
        ]);
        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        abort_if($product->tenant_id !== $this->tid(), 403);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produit supprimé.');
    }
}
