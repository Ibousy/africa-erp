<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1f2937; background:#fff; }
.page { padding:30px 36px; }
.header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:28px; border-bottom:2px solid #8b5cf6; padding-bottom:18px; }
.company-name { font-size:20px; font-weight:bold; color:#8b5cf6; }
.company-info { font-size:10px; color:#6b7280; margin-top:3px; line-height:1.5; }
.doc-title { text-align:right; }
.doc-type { font-size:18px; font-weight:bold; color:#111827; }
.doc-ref { font-size:11px; color:#6b7280; margin-top:4px; }
.parties { display:flex; justify-content:space-between; margin-bottom:24px; gap:20px; }
.party-block { flex:1; }
.party-label { font-size:9px; font-weight:bold; text-transform:uppercase; color:#9ca3af; margin-bottom:5px; }
.party-name { font-size:13px; font-weight:bold; color:#111827; }
.party-info { font-size:10px; color:#6b7280; margin-top:2px; line-height:1.5; }
.dates { display:flex; gap:20px; margin-bottom:20px; }
.date-block { background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:8px 14px; }
.date-label { font-size:9px; color:#9ca3af; font-weight:bold; text-transform:uppercase; }
.date-val { font-size:12px; font-weight:bold; color:#111827; margin-top:2px; }
table { width:100%; border-collapse:collapse; margin-bottom:18px; }
thead th { background:#f9fafb; border-bottom:2px solid #e5e7eb; padding:8px 10px; text-align:left; font-size:9px; font-weight:bold; text-transform:uppercase; color:#6b7280; }
thead th.right { text-align:right; }
tbody td { padding:8px 10px; border-bottom:1px solid #f3f4f6; font-size:10px; }
tbody td.right { text-align:right; }
.totals { margin-left:auto; width:220px; }
.total-row { display:flex; justify-content:space-between; padding:4px 0; font-size:11px; color:#374151; }
.total-row.final { font-size:13px; font-weight:bold; color:#111827; border-top:2px solid #e5e7eb; margin-top:4px; padding-top:6px; }
.validity { margin-top:12px; font-size:10px; color:#6b7280; font-style:italic; }
.footer { position:fixed; bottom:20px; left:36px; right:36px; border-top:1px solid #e5e7eb; padding-top:6px; display:flex; justify-content:space-between; font-size:9px; color:#9ca3af; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <div class="company-name">{{ $quote->tenant?->company_name ?? 'Entreprise' }}</div>
            <div class="company-info">
                {{ $quote->tenant?->address ?? '' }}@if($quote->tenant?->city), {{ $quote->tenant->city }}@endif<br>
                @if($quote->tenant?->phone)Tél: {{ $quote->tenant->phone }}@endif
            </div>
        </div>
        <div class="doc-title">
            <div class="doc-type">DEVIS</div>
            <div class="doc-ref">{{ $quote->reference }}</div>
        </div>
    </div>

    <div class="parties">
        <div class="party-block">
            <div class="party-label">Émis par</div>
            <div class="party-name">{{ $quote->tenant?->company_name }}</div>
        </div>
        <div class="party-block" style="text-align:right">
            <div class="party-label">Destinataire</div>
            <div class="party-name">{{ $quote->client->name }}</div>
            <div class="party-info">{{ $quote->client->phone ?? '' }}</div>
        </div>
    </div>

    <div class="dates">
        <div class="date-block">
            <div class="date-label">Date d'émission</div>
            <div class="date-val">{{ $quote->issue_date->format('d/m/Y') }}</div>
        </div>
        @if($quote->valid_until)
        <div class="date-block">
            <div class="date-label">Valide jusqu'au</div>
            <div class="date-val">{{ $quote->valid_until->format('d/m/Y') }}</div>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right" style="width:60px">Qté</th>
                <th class="right" style="width:90px">Prix unit.</th>
                <th class="right" style="width:60px">Remise</th>
                <th class="right" style="width:90px">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="right">{{ number_format($item->quantity, 2) }}</td>
                <td class="right">{{ money($item->unit_price) }}</td>
                <td class="right">{{ $item->discount_pct > 0 ? $item->discount_pct.'%' : '—' }}</td>
                <td class="right">{{ money($item->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row"><span>Sous-total HT</span><span>{{ money($quote->subtotal) }}</span></div>
        @if($quote->tax_rate > 0)
        <div class="total-row"><span>TVA ({{ $quote->tax_rate }}%)</span><span>{{ money($quote->tax_amount) }}</span></div>
        @endif
        <div class="total-row final"><span>TOTAL TTC</span><span>{{ money($quote->total) }}</span></div>
    </div>

    @if($quote->valid_until)
    <div class="validity">Ce devis est valable jusqu'au {{ $quote->valid_until->format('d/m/Y') }}.</div>
    @endif

    @if($quote->notes)
    <div style="margin-top:14px; padding:10px 14px; background:#f9fafb; border-left:3px solid #8b5cf6; border-radius:0 6px 6px 0; font-size:10px;">
        <strong>Notes :</strong> {{ $quote->notes }}
    </div>
    @endif
</div>
<div class="footer">
    <span>{{ $quote->reference }} — {{ $quote->tenant?->company_name }}</span>
    <span>Généré le {{ now()->format('d/m/Y') }}</span>
</div>
</body>
</html>
