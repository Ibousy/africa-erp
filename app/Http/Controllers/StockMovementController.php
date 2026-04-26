<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'user'])->where('tenant_id', $this->tid());
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);
        $movements = $query->latest()->paginate(25)->withQueryString();
        $products  = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('stock.index', compact('movements', 'products'));
    }

    public function create()
    {
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('stock.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:entree,sortie',
            'quantity'   => 'required|numeric|min:0.01',
            'reference'  => 'nullable|string|max:100',
            'reason'     => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($data['product_id']);
        abort_if($product->tenant_id !== $this->tid(), 403);

        if ($data['type'] === 'sortie' && $product->quantity_in_stock < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Stock insuffisant. Stock actuel: ' . $product->quantity_in_stock . ' ' . $product->unit]);
        }

        $data['tenant_id'] = $this->tid();
        $data['user_id']   = auth()->id();
        StockMovement::create($data);

        if ($data['type'] === 'entree') {
            $product->increment('quantity_in_stock', $data['quantity']);
        } else {
            $product->decrement('quantity_in_stock', $data['quantity']);
        }

        $this->checkLowStock($product->fresh());

        return redirect()->route('stock.index')->with('success', 'Mouvement de stock enregistré.');
    }

    public function edit(StockMovement $stock)
    {
        abort_if($stock->tenant_id !== $this->tid(), 403);
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('stock.edit', compact('stock', 'products'));
    }

    public function update(Request $request, StockMovement $stock)
    {
        abort_if($stock->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:entree,sortie',
            'quantity'   => 'required|numeric|min:0.01',
            'reference'  => 'nullable|string|max:100',
            'reason'     => 'nullable|string|max:255',
        ]);

        $newProduct = Product::findOrFail($data['product_id']);
        abort_if($newProduct->tenant_id !== $this->tid(), 403);

        // Reverse old movement
        $oldProduct = Product::findOrFail($stock->product_id);
        if ($stock->type === 'entree') {
            $oldProduct->decrement('quantity_in_stock', $stock->quantity);
        } else {
            $oldProduct->increment('quantity_in_stock', $stock->quantity);
        }

        // Apply new movement
        if ($data['type'] === 'entree') {
            $newProduct->increment('quantity_in_stock', $data['quantity']);
        } else {
            $newProduct->decrement('quantity_in_stock', $data['quantity']);
        }

        $stock->update($data);
        $this->checkLowStock($newProduct->fresh());
        return redirect()->route('stock.index')->with('success', 'Mouvement mis à jour.');
    }

    public function destroy(StockMovement $stock)
    {
        abort_if($stock->tenant_id !== $this->tid(), 403);

        // Reverse the movement before deleting
        $product = Product::findOrFail($stock->product_id);
        if ($stock->type === 'entree') {
            $product->decrement('quantity_in_stock', $stock->quantity);
        } else {
            $product->increment('quantity_in_stock', $stock->quantity);
        }

        $stock->delete();
        return redirect()->route('stock.index')->with('success', 'Mouvement supprimé et stock corrigé.');
    }

    private function checkLowStock(Product $product): void
    {
        if (!$product->isLowStock()) return;

        $recipients = User::where('tenant_id', $this->tid())
            ->where(function ($q) {
                $q->where('department', 'logistique')->orWhere('role', 'admin');
            })->get();

        foreach ($recipients as $u) {
            ErpNotification::notify(
                $u->id,
                $this->tid(),
                'low_stock',
                'Stock bas — ' . $product->name,
                'Niveau actuel: ' . number_format($product->quantity_in_stock, 2) . ' ' . $product->unit . ' (seuil: ' . $product->min_stock_alert . ')',
                route('logistics.stock'),
                'low_stock'
            );
        }
    }
}
