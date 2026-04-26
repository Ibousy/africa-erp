<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1f2937; background:#fff; }
.page { padding:30px 36px; }
.header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; border-bottom:2px solid #f97316; padding-bottom:18px; }
.company-name { font-size:20px; font-weight:bold; color:#f97316; }
.company-info { font-size:10px; color:#6b7280; margin-top:3px; line-height:1.5; }
.doc-title { text-align:right; }
.doc-type { font-size:18px; font-weight:bold; color:#111827; }
.doc-ref { font-size:11px; color:#6b7280; margin-top:4px; }
.doc-status { display:inline-block; padding:2px 8px; border-radius:999px; font-size:10px; font-weight:bold; background:#fef3c7; color:#92400e; margin-top:4px; }
.parties { display:flex; justify-content:space-between; margin-bottom:24px; gap:20px; }
.party-block { flex:1; }
.party-label { font-size:9px; font-weight:bold; text-transform:uppercase; color:#9ca3af; margin-bottom:5px; letter-spacing:0.5px; }
.party-name { font-size:13px; font-weight:bold; color:#111827; }
.party-info { font-size:10px; color:#6b7280; margin-top:2px; line-height:1.5; }
.dates { display:flex; gap:20px; margin-bottom:20px; }
.date-block { background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:8px 14px; }
.date-label { font-size:9px; color:#9ca3af; font-weight:bold; text-transform:uppercase; }
.date-val { font-size:12px; font-weight:bold; color:#111827; margin-top:2px; }
table { width:100%; border-collapse:collapse; margin-bottom:18px; }
thead th { background:#f9fafb; border-bottom:2px solid #e5e7eb; padding:8px 10px; text-align:left; font-size:9px; font-weight:bold; text-transform:uppercase; color:#6b7280; letter-spacing:0.5px; }
thead th.right { text-align:right; }
tbody td { padding:8px 10px; border-bottom:1px solid #f3f4f6; font-size:10px; }
tbody td.right { text-align:right; }
.totals { margin-left:auto; width:220px; }
.total-row { display:flex; justify-content:space-between; padding:4px 0; font-size:11px; color:#374151; }
.total-row.final { font-size:13px; font-weight:bold; color:#111827; border-top:2px solid #e5e7eb; margin-top:4px; padding-top:6px; }
.notes { margin-top:20px; padding:10px 14px; background:#f9fafb; border-left:3px solid #f97316; border-radius:0 6px 6px 0; }
.notes-label { font-size:9px; font-weight:bold; color:#9ca3af; text-transform:uppercase; margin-bottom:3px; }
.footer { position:fixed; bottom:20px; left:36px; right:36px; border-top:1px solid #e5e7eb; padding-top:6px; display:flex; justify-content:space-between; font-size:9px; color:#9ca3af; }
</style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <div>
            <div class="company-name">{{ $invoice->tenant?->company_name ?? 'Entreprise' }}</div>
            <div class="company-info">
                {{ $invoice->tenant?->address ?? '' }}@if($invoice->tenant?->city), {{ $invoice->tenant->city }}@endif<br>
                @if($invoice->tenant?->phone)Tél: {{ $invoice->tenant->phone }}@endif
                @if($invoice->tenant?->email) — {{ $invoice->tenant->email }}@endif
            </div>
        </div>
        <div class="doc-title">
            <div class="doc-type">{{ $invoice->type === 'facture' ? 'FACTURE' : 'DEVIS' }}</div>
            <div class="doc-ref">{{ $invoice->reference }}</div>
            <div class="doc-status">{{ strtoupper($invoice->status) }}</div>
        </div>
    </div>

    <!-- Parties -->
    <div class="parties">
        <div class="party-block">
            <div class="party-label">Émetteur</div>
            <div class="party-name">{{ $invoice->tenant?->company_name }}</div>
        </div>
        <div class="party-block" style="text-align:right">
            <div class="party-label">Client</div>
            <div class="party-name">{{ $invoice->client->name }}</div>
            <div class="party-info">
                {{ $invoice->client->address ?? '' }}<br>
                @if($invoice->client->phone)Tél: {{ $invoice->client->phone }}@endif
            </div>
        </div>
    </div>

    <!-- Dates -->
    <div class="dates">
        <div class="date-block">
            <div class="date-label">Date d'émission</div>
            <div class="date-val">{{ $invoice->issue_date->format('d/m/Y') }}</div>
        </div>
        @if($invoice->due_date)
        <div class="date-block">
            <div class="date-label">Date d'échéance</div>
            <div class="date-val">{{ $invoice->due_date->format('d/m/Y') }}</div>
        </div>
        @endif
    </div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right" style="width:60px">Qté</th>
                <th class="right" style="width:90px">Prix unit.</th>
                <th class="right" style="width:90px">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="right">{{ number_format($item->quantity, 2) }}</td>
                <td class="right">{{ money($item->unit_price) }}</td>
                <td class="right">{{ money($item->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div class="total-row"><span>Sous-total HT</span><span>{{ money($invoice->subtotal) }}</span></div>
        @if($invoice->tax_rate > 0)
        <div class="total-row"><span>TVA ({{ $invoice->tax_rate }}%)</span><span>{{ money($invoice->tax_amount) }}</span></div>
        @endif
        <div class="total-row final"><span>TOTAL TTC</span><span>{{ money($invoice->total) }}</span></div>
    </div>

    @if($invoice->notes)
    <div class="notes">
        <div class="notes-label">Notes</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif
</div>
<div class="footer">
    <span>{{ $invoice->reference }} — {{ $invoice->tenant?->company_name }}</span>
    <span>Généré le {{ now()->format('d/m/Y') }}</span>
</div>
</body>
</html>
