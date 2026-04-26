<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = SupplierPayment::with(['supplier', 'purchaseOrder'])
            ->where('supplier_payments.tenant_id', $this->tid())
            ->join('suppliers', 'suppliers.id', '=', 'supplier_payments.supplier_id')
            ->select('supplier_payments.*');

        if ($request->filled('supplier_id')) $query->where('supplier_payments.supplier_id', $request->supplier_id);

        $payments  = $query->orderBy('supplier_payments.payment_date', 'desc')->paginate(20)->withQueryString();
        $suppliers = Supplier::where('tenant_id', $this->tid())->orderBy('name')->get();
        $orders    = PurchaseOrder::where('tenant_id', $this->tid())
            ->whereIn('status', ['confirme', 'recu'])->orderByDesc('order_date')->get();

        $stats = [
            'total_paye' => SupplierPayment::where('tenant_id', $this->tid())->sum('amount'),
            'ce_mois'    => SupplierPayment::where('tenant_id', $this->tid())
                ->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        return view('purchases.supplier-payments', compact('payments', 'suppliers', 'orders', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'       => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'amount'            => 'required|numeric|min:0.01',
            'payment_date'      => 'required|date',
            'method'            => 'required|in:especes,virement,cheque,mobile',
            'reference'         => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        Supplier::where('id', $data['supplier_id'])->where('tenant_id', $this->tid())->firstOrFail();

        SupplierPayment::create(array_merge($data, ['tenant_id' => $this->tid()]));

        $supplier = Supplier::find($data['supplier_id']);
        $supplier->decrement('balance_due', $data['amount']);

        return back()->with('success', 'Paiement fournisseur enregistré.');
    }

    public function destroy(SupplierPayment $payment)
    {
        abort_if($payment->tenant_id !== $this->tid(), 403);
        $payment->supplier->increment('balance_due', $payment->amount);
        $payment->delete();
        return back()->with('success', 'Paiement supprimé.');
    }
}
