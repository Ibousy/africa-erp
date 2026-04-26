<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Invoice::with('client')->where('tenant_id', $this->tid());
        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);
        $invoices = $query->latest()->paginate(15)->withQueryString();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $clients  = Client::where('tenant_id', $this->tid())->orderBy('name')->get();
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('invoices.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'type'                   => 'required|in:devis,facture',
            'issue_date'             => 'required|date',
            'due_date'               => 'nullable|date',
            'tax_rate'               => 'required|numeric|min:0|max:100',
            'notes'                  => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.description'    => 'required|string',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ]);

        $count  = Invoice::where('tenant_id', $this->tid())->count();
        $prefix = $data['type'] === 'facture' ? 'FAC' : 'DEV';
        $data['reference'] = $prefix . '-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $data['tenant_id'] = $this->tid();
        $data['user_id']   = auth()->id();
        $data['status']    = 'brouillon';

        $invoice = Invoice::create($data);

        foreach ($data['items'] as $i => $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'product_id'  => $request->items[$i]['product_id'] ?? null,
                'description' => $item['description'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total'       => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $invoice->recalculateTotals();
        return redirect()->route('invoices.show', $invoice)->with('success', 'Document créé.');
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);
        $invoice->load(['client', 'items.product', 'user']);
        return view('invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);
        $invoice->load(['client', 'items.product', 'user', 'tenant']);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))->setPaper('a4', 'portrait');
        return $pdf->download($invoice->reference . '.pdf');
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:brouillon,envoye,paye,annule']);
        $invoice->update(['status' => $request->status]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Document supprimé.');
    }
}
