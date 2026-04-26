<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Quote::with('client')->where('tenant_id', $this->tid());
        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('client_id', $request->client_id);

        $quotes  = $query->latest()->paginate(20)->withQueryString();
        $clients = Client::where('tenant_id', $this->tid())->orderBy('name')->get();

        $base  = Quote::where('tenant_id', $this->tid());
        $stats = [
            'total'    => (clone $base)->count(),
            'envoye'   => (clone $base)->where('status', 'envoye')->count(),
            'accepte'  => (clone $base)->where('status', 'accepte')->count(),
            'ca'       => (clone $base)->where('status', 'accepte')->sum('total'),
        ];

        return view('sales.quotes.index', compact('quotes', 'clients', 'stats'));
    }

    public function create()
    {
        $clients  = Client::where('tenant_id', $this->tid())->orderBy('name')->get();
        $products = Product::where('tenant_id', $this->tid())->where('status', 'actif')->orderBy('name')->get();
        return view('sales.quotes.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'issue_date'  => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:issue_date',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'notes'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.0001',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.discount_pct'=> 'nullable|numeric|min:0|max:100',
            'items.*.product_id'  => 'nullable|exists:products,id',
        ]);

        Client::where('id', $request->client_id)->where('tenant_id', $this->tid())->firstOrFail();

        $count = Quote::where('tenant_id', $this->tid())->count();
        $ref   = 'DEV-' . date('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $taxRate  = $request->tax_rate ?? 0;
        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += ($item['quantity'] * $item['unit_price']) * (1 - ($item['discount_pct'] ?? 0) / 100);
        }
        $taxAmt = round($subtotal * $taxRate / 100, 2);

        $quote = Quote::create([
            'tenant_id'   => $this->tid(),
            'client_id'   => $request->client_id,
            'reference'   => $ref,
            'status'      => 'brouillon',
            'issue_date'  => $request->issue_date,
            'valid_until' => $request->valid_until,
            'tax_rate'    => $taxRate,
            'subtotal'    => $subtotal,
            'tax_amount'  => $taxAmt,
            'total'       => $subtotal + $taxAmt,
            'notes'       => $request->notes,
        ]);

        foreach ($request->items as $item) {
            QuoteItem::create([
                'quote_id'     => $quote->id,
                'product_id'   => $item['product_id'] ?: null,
                'description'  => $item['description'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'discount_pct' => $item['discount_pct'] ?? 0,
            ]);
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis créé avec la référence ' . $ref);
    }

    public function show(Quote $quote)
    {
        abort_if($quote->tenant_id !== $this->tid(), 403);
        $quote->load(['client', 'items.product']);
        return view('sales.quotes.show', compact('quote'));
    }

    public function pdf(Quote $quote)
    {
        abort_if($quote->tenant_id !== $this->tid(), 403);
        $quote->load(['client', 'items.product', 'tenant']);
        $pdf = Pdf::loadView('pdf.quote', compact('quote'))->setPaper('a4', 'portrait');
        return $pdf->download($quote->reference . '.pdf');
    }

    public function updateStatus(Request $request, Quote $quote)
    {
        abort_if($quote->tenant_id !== $this->tid(), 403);
        $request->validate(['status' => 'required|in:brouillon,envoye,accepte,refuse,expire']);
        $quote->update(['status' => $request->status]);
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Quote $quote)
    {
        abort_if($quote->tenant_id !== $this->tid(), 403);
        $quote->delete();
        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }

    public function convertToOrder(Quote $quote)
    {
        abort_if($quote->tenant_id !== $this->tid(), 403);

        $count = \App\Models\CustomerOrder::where('tenant_id', $this->tid())->count();
        $ref   = 'CMD-' . date('Y') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $order = \App\Models\CustomerOrder::create([
            'tenant_id'    => $this->tid(),
            'client_id'    => $quote->client_id,
            'quote_id'     => $quote->id,
            'reference'    => $ref,
            'status'       => 'confirme',
            'order_date'   => now(),
            'total_amount' => $quote->total,
            'notes'        => 'Converti depuis devis ' . $quote->reference,
        ]);

        $quote->load('items');
        foreach ($quote->items as $item) {
            \App\Models\CustomerOrderItem::create([
                'customer_order_id' => $order->id,
                'product_id'        => $item->product_id,
                'description'       => $item->description,
                'quantity'          => $item->quantity,
                'unit_price'        => $item->unit_price,
            ]);
        }

        $quote->update(['status' => 'accepte']);
        return redirect()->route('orders.show', $order)->with('success', 'Commande créée depuis le devis.');
    }
}
