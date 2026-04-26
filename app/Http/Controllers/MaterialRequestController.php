<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    // Production: list my requests
    public function index(Request $request)
    {
        $query = MaterialRequest::with(['items.product', 'requester'])
            ->where('tenant_id', $this->tid());

        if (!auth()->user()->isAdmin()) {
            $query->where('requested_by', auth()->id());
        }
        if ($request->filled('status')) $query->where('status', $request->status);

        $requests = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'pending'  => MaterialRequest::where('tenant_id', $this->tid())->where('status', 'pending')->count(),
            'approved' => MaterialRequest::where('tenant_id', $this->tid())->where('status', 'approved')->count(),
            'partial'  => MaterialRequest::where('tenant_id', $this->tid())->where('status', 'partial')->count(),
            'rejected' => MaterialRequest::where('tenant_id', $this->tid())->where('status', 'rejected')->count(),
        ];

        return view('material-requests.index', compact('requests', 'stats'));
    }

    public function create()
    {
        $products    = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        $productions = ProductionOrder::where('tenant_id', $this->tid())
            ->whereIn('status', ['brouillon', 'en_cours'])->with('product')->latest()->get();
        return view('material-requests.create', compact('products', 'productions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'production_order_id'     => 'nullable|exists:production_orders,id',
            'notes'                   => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity_needed' => 'required|numeric|min:0.01',
        ]);

        $count = MaterialRequest::where('tenant_id', $this->tid())->count();
        $ref   = 'MR-' . date('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $mr = MaterialRequest::create([
            'tenant_id'           => $this->tid(),
            'production_order_id' => $request->production_order_id ?: null,
            'requested_by'        => auth()->id(),
            'reference'           => $ref,
            'status'              => 'pending',
            'notes'               => $request->notes,
        ]);

        foreach ($request->items as $item) {
            MaterialRequestItem::create([
                'material_request_id' => $mr->id,
                'product_id'          => $item['product_id'],
                'quantity_needed'     => $item['quantity_needed'],
            ]);
        }

        // Notify all logistics users
        $logisticsUsers = User::where('tenant_id', $this->tid())
            ->where(function ($q) {
                $q->where('department', 'logistique')->orWhere('role', 'admin');
            })->get();

        foreach ($logisticsUsers as $lu) {
            ErpNotification::notify(
                $lu->id, $this->tid(),
                'material_request',
                'Nouvelle demande matières — ' . $ref,
                auth()->user()->name . ' a soumis une demande de matières pour la production.',
                route('material-requests.logistics'),
                'truck'
            );
        }

        return redirect()->route('material-requests.index')->with('success', 'Demande ' . $ref . ' envoyée à la logistique.');
    }

    public function show(MaterialRequest $materialRequest)
    {
        abort_if($materialRequest->tenant_id !== $this->tid(), 403);
        $materialRequest->load(['items.product', 'requester', 'handler', 'productionOrder']);
        return view('material-requests.show', compact('materialRequest'));
    }

    // Logistics: list all pending requests
    public function logistics(Request $request)
    {
        $query = MaterialRequest::with(['items.product', 'requester', 'productionOrder'])
            ->where('tenant_id', $this->tid());
        if ($request->filled('status')) $query->where('status', $request->status);

        $requests = $query->latest()->paginate(20)->withQueryString();
        $pending  = MaterialRequest::where('tenant_id', $this->tid())->where('status', 'pending')->count();

        return view('material-requests.logistics', compact('requests', 'pending'));
    }

    // Logistics: respond to a request
    public function respond(Request $request, MaterialRequest $materialRequest)
    {
        abort_if($materialRequest->tenant_id !== $this->tid(), 403);

        $request->validate([
            'logistics_notes'           => 'nullable|string',
            'items'                     => 'required|array',
            'items.*.item_status'       => 'required|in:available,insufficient,unavailable',
            'items.*.quantity_available'=> 'nullable|numeric|min:0',
            'items.*.logistics_note'    => 'nullable|string|max:255',
        ]);

        foreach ($request->items as $itemId => $data) {
            MaterialRequestItem::where('id', $itemId)
                ->where('material_request_id', $materialRequest->id)
                ->update([
                    'item_status'        => $data['item_status'],
                    'quantity_available' => $data['quantity_available'] ?? null,
                    'logistics_note'     => $data['logistics_note'] ?? null,
                ]);
        }

        // Compute overall status
        $items    = $materialRequest->fresh()->items;
        $allOk    = $items->every(fn($i) => $i->item_status === 'available');
        $allBad   = $items->every(fn($i) => in_array($i->item_status, ['insufficient', 'unavailable']));
        $newStatus = $allOk ? 'approved' : ($allBad ? 'rejected' : 'partial');

        $materialRequest->update([
            'status'          => $newStatus,
            'logistics_notes' => $request->logistics_notes,
            'handled_by'      => auth()->id(),
            'responded_at'    => now(),
        ]);

        // Notify the requester
        $icon  = $newStatus === 'approved' ? 'check' : ($newStatus === 'rejected' ? 'x' : 'warning');
        $title = match($newStatus) {
            'approved' => 'Demande ' . $materialRequest->reference . ' approuvée ✓',
            'rejected' => 'Demande ' . $materialRequest->reference . ' refusée ✗',
            default    => 'Demande ' . $materialRequest->reference . ' — disponibilité partielle',
        };

        ErpNotification::notify(
            $materialRequest->requested_by, $this->tid(),
            'request_response',
            $title,
            'La logistique a répondu à votre demande de matières.',
            route('material-requests.show', $materialRequest),
            $icon
        );

        return redirect()->route('material-requests.logistics')->with('success', 'Réponse enregistrée.');
    }

    public function destroy(MaterialRequest $materialRequest)
    {
        abort_if($materialRequest->tenant_id !== $this->tid(), 403);
        abort_if($materialRequest->status !== 'pending', 403);
        $materialRequest->items()->delete();
        $materialRequest->delete();
        return redirect()->route('material-requests.index')->with('success', 'Demande supprimée.');
    }
}
