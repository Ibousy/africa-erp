<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Return_;
use App\Models\ReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Return_::where('tenant_id', $this->tid());
        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);

        $returns = $query->with(['client', 'supplier', 'items'])->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'    => Return_::where('tenant_id', $this->tid())->count(),
            'client'   => Return_::where('tenant_id', $this->tid())->where('type', 'client')->count(),
            'fournisseur' => Return_::where('tenant_id', $this->tid())->where('type', 'fournisseur')->count(),
            'traite'   => Return_::where('tenant_id', $this->tid())->where('status', 'traite')->count(),
        ];

        return view('logistics.returns.index', compact('returns', 'stats'));
    }

    public function create()
    {
        $clients   = Client::where('tenant_id', $this->tid())->orderBy('name')->get();
        $suppliers = Supplier::where('tenant_id', $this->tid())->orderBy('name')->get();
        $products  = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('logistics.returns.create', compact('clients', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'        => 'required|in:client,fournisseur',
            'client_id'   => 'nullable|exists:clients,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reason'      => 'nullable|string',
            'notes'       => 'nullable|string',
            'return_date' => 'required|date',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
            'items.*.condition'  => 'required|in:bon,defectueux,a_reparer',
        ]);

        $count = Return_::where('tenant_id', $this->tid())->count();
        $ref   = 'RET-' . date('Ymd') . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $return = Return_::create([
            'tenant_id'   => $this->tid(),
            'type'        => $data['type'],
            'client_id'   => $data['client_id'] ?? null,
            'supplier_id' => $data['supplier_id'] ?? null,
            'reference'   => $ref,
            'status'      => 'en_attente',
            'reason'      => $data['reason'] ?? null,
            'notes'       => $data['notes'] ?? null,
            'return_date' => $data['return_date'],
        ]);

        foreach ($data['items'] as $item) {
            ReturnItem::create([
                'return_id'  => $return->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'condition'  => $item['condition'],
            ]);
        }

        return redirect()->route('returns.index')->with('success', 'Retour ' . $ref . ' enregistré.');
    }

    public function processReturn(Return_ $return)
    {
        abort_if($return->tenant_id !== $this->tid(), 403);
        if ($return->status === 'traite') return back()->with('error', 'Déjà traité.');

        $return->load('items');
        foreach ($return->items as $item) {
            $type = $return->type === 'client' ? 'entree' : 'sortie';
            if ($item->condition === 'bon') {
                $product = Product::find($item->product_id);
                if ($product) {
                    if ($type === 'entree') $product->increment('quantity_in_stock', $item->quantity);
                    else                    $product->decrement('quantity_in_stock', $item->quantity);
                    StockMovement::create([
                        'tenant_id'  => $this->tid(),
                        'product_id' => $item->product_id,
                        'user_id'    => auth()->id(),
                        'type'       => $type,
                        'quantity'   => $item->quantity,
                        'reason'     => 'Retour ' . $return->reference,
                    ]);
                }
            }
        }

        $return->update(['status' => 'traite']);
        return back()->with('success', 'Retour traité et stock mis à jour.');
    }

    public function destroy(Return_ $return)
    {
        abort_if($return->tenant_id !== $this->tid(), 403);
        $return->delete();
        return redirect()->route('returns.index')->with('success', 'Retour supprimé.');
    }
}
