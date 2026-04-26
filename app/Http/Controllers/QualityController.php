<?php

namespace App\Http\Controllers;

use App\Models\Defect;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\QualityControl;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = QualityControl::with(['product', 'productionOrder', 'checker'])->where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);
        $controls = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'         => QualityControl::where('tenant_id', $this->tid())->count(),
            'passed'        => QualityControl::where('tenant_id', $this->tid())->where('status', 'passe')->count(),
            'failed'        => QualityControl::where('tenant_id', $this->tid())->where('status', 'echoue')->count(),
            'defects_total' => Defect::whereHas('qualityControl', fn($q) => $q->where('tenant_id', $this->tid()))->count(),
        ];

        return view('quality.index', compact('controls', 'stats'));
    }

    public function create()
    {
        $products    = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        $productions = ProductionOrder::where('tenant_id', $this->tid())->where('status', 'en_cours')->with('product')->latest()->get();
        return view('quality.create', compact('products', 'productions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'production_order_id'  => 'nullable|exists:production_orders,id',
            'product_id'           => 'required|exists:products,id',
            'check_date'           => 'required|date',
            'quantity_checked'     => 'required|numeric|min:0.01',
            'quantity_defective'   => 'required|numeric|min:0',
            'notes'                => 'nullable|string',
            'defects'              => 'nullable|array',
            'defects.*.type'       => 'required_with:defects|string',
            'defects.*.quantity'   => 'required_with:defects|numeric|min:0',
            'defects.*.severity'   => 'required_with:defects|in:faible,moyen,grave',
            'defects.*.description' => 'nullable|string',
        ]);

        $data['tenant_id']  = $this->tid();
        $data['checked_by'] = auth()->id();
        $data['status'] = ($data['quantity_defective'] / $data['quantity_checked']) <= 0.05 ? 'passe' : 'echoue';

        $control = QualityControl::create($data);

        if (!empty($data['defects'])) {
            foreach ($data['defects'] as $defect) {
                Defect::create(array_merge($defect, ['quality_control_id' => $control->id]));
            }
        }

        return redirect()->route('quality.index')->with('success', 'Contrôle qualité enregistré.');
    }

    public function show(QualityControl $quality)
    {
        abort_if($quality->tenant_id !== $this->tid(), 403);
        $quality->load(['product', 'productionOrder', 'checker', 'defects']);
        return view('quality.show', compact('quality'));
    }

    public function edit(QualityControl $quality)
    {
        abort_if($quality->tenant_id !== $this->tid(), 403);
        $products    = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        $productions = ProductionOrder::where('tenant_id', $this->tid())->with('product')->latest()->get();
        return view('quality.edit', compact('quality', 'products', 'productions'));
    }

    public function update(Request $request, QualityControl $quality)
    {
        abort_if($quality->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'production_order_id' => 'nullable|exists:production_orders,id',
            'product_id'          => 'required|exists:products,id',
            'check_date'          => 'required|date',
            'quantity_checked'    => 'required|numeric|min:0.01',
            'quantity_defective'  => 'required|numeric|min:0',
            'notes'               => 'nullable|string',
        ]);

        $data['status'] = ($data['quantity_defective'] / $data['quantity_checked']) <= 0.05 ? 'passe' : 'echoue';
        $quality->update($data);

        return redirect()->route('quality.show', $quality)->with('success', 'Contrôle mis à jour.');
    }

    public function destroy(QualityControl $quality)
    {
        abort_if($quality->tenant_id !== $this->tid(), 403);
        $quality->defects()->delete();
        $quality->delete();
        return redirect()->route('quality.index')->with('success', 'Contrôle supprimé.');
    }
}
