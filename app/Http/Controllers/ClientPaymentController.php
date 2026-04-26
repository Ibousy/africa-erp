<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = ClientPayment::with(['client', 'invoice'])
            ->where('client_payments.tenant_id', $this->tid())
            ->join('clients', 'clients.id', '=', 'client_payments.client_id')
            ->select('client_payments.*');

        if ($request->filled('client_id')) $query->where('client_payments.client_id', $request->client_id);
        if ($request->filled('method'))    $query->where('client_payments.method', $request->method);

        $payments = $query->orderBy('client_payments.payment_date', 'desc')->paginate(20)->withQueryString();
        $clients  = Client::where('tenant_id', $this->tid())->orderBy('name')->get();

        $stats = [
            'total_recu'   => ClientPayment::where('tenant_id', $this->tid())->sum('amount'),
            'ce_mois'      => ClientPayment::where('tenant_id', $this->tid())
                ->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        return view('sales.payments.index', compact('payments', 'clients', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'    => 'required|exists:clients,id',
            'invoice_id'   => 'nullable|exists:invoices,id',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'method'       => 'required|in:especes,virement,cheque,mobile,carte',
            'reference'    => 'nullable|string|max:100',
            'notes'        => 'nullable|string',
        ]);

        Client::where('id', $data['client_id'])->where('tenant_id', $this->tid())->firstOrFail();

        $payment = ClientPayment::create(array_merge($data, ['tenant_id' => $this->tid()]));

        // Update client balance
        $client = Client::find($data['client_id']);
        $client->decrement('balance_due', $data['amount']);

        // If linked invoice, check if fully paid
        if (!empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice) {
                $paid = ClientPayment::where('invoice_id', $invoice->id)->sum('amount');
                if ($paid >= $invoice->total) {
                    $invoice->update(['status' => 'paye']);
                }
            }
        }

        return back()->with('success', 'Paiement de ' . number_format($data['amount'], 0, ',', ' ') . ' FCFA enregistré.');
    }

    public function destroy(ClientPayment $payment)
    {
        abort_if($payment->tenant_id !== $this->tid(), 403);
        $payment->client->increment('balance_due', $payment->amount);
        $payment->delete();
        return back()->with('success', 'Paiement supprimé.');
    }
}
