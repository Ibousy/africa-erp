<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $docTitles = [
            'entrant' => 'BON DE RÉCEPTION',
            'sortant' => 'BON DE SORTIE',
            'retour'  => 'BON DE RETOUR',
        ];
        $docTitle = $docTitles[$logistic->type] ?? 'BON DE MOUVEMENT';
    @endphp
    <title>{{ $docTitle }} — {{ $logistic->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #1f2937; background: #fff; padding: 32px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1f2937; padding-bottom: 20px; margin-bottom: 24px; }
        .company-name { font-size: 22px; font-weight: 800; color: #1f2937; }
        .company-sub { font-size: 11px; color: #6b7280; margin-top: 4px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 20px; font-weight: 700; color: #1f2937; letter-spacing: 1px; }
        .doc-title .ref { font-size: 14px; color: #6b7280; margin-top: 4px; }
        .doc-title .date { font-size: 11px; color: #9ca3af; margin-top: 2px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .field { margin-bottom: 10px; }
        .field label { font-size: 10px; text-transform: uppercase; color: #9ca3af; display: block; margin-bottom: 2px; }
        .field span { font-size: 13px; font-weight: 500; color: #1f2937; }
        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .product-table th { background: #f9fafb; text-align: left; padding: 8px 12px; font-size: 10px; text-transform: uppercase; color: #6b7280; border: 1px solid #e5e7eb; }
        .product-table td { padding: 10px 12px; border: 1px solid #e5e7eb; }
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; background: #dcfce7; color: #166534; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; }
        .sig-box { border-top: 1px solid #e5e7eb; padding-top: 8px; }
        .sig-box label { font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .sig-space { height: 60px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        @media print {
            body { padding: 16px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#f3f4f6;padding:12px 20px;margin:-32px -32px 24px;display:flex;align-items:center;justify-content:space-between;">
    <span style="font-size:13px;color:#374151;">Aperçu du document — {{ $docTitle }}</span>
    <button onclick="window.print()" style="background:#1f2937;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:13px;cursor:pointer;">🖨 Imprimer</button>
</div>

<!-- En-tête -->
<div class="header">
    <div>
        <div class="company-name">{{ $tenant->company_name }}</div>
        <div class="company-sub">
            {{ $tenant->address ?: '' }}
            @if($tenant->city) · {{ $tenant->city }} @endif
            @if($tenant->phone) · {{ $tenant->phone }} @endif
        </div>
    </div>
    <div class="doc-title">
        <h1>{{ $docTitle }}</h1>
        <div class="ref">Réf. {{ $logistic->reference }}</div>
        <div class="date">Émis le {{ now()->format('d/m/Y') }}</div>
    </div>
</div>

<!-- Infos générales -->
<div class="grid2 section">
    <div>
        <div class="section-title">Informations du mouvement</div>
        <div class="field"><label>Type</label><span>@label($logistic->type)</span></div>
        <div class="field"><label>Statut</label><span class="status-badge">@label($logistic->status)</span></div>
        @if($logistic->carrier)
        <div class="field"><label>Transporteur</label><span>{{ $logistic->carrier }}</span></div>
        @endif
        @if($logistic->weight_kg)
        <div class="field"><label>Poids</label><span>{{ number_format($logistic->weight_kg, 2) }} kg</span></div>
        @endif
    </div>
    <div>
        <div class="section-title">Contact & Destination</div>
        <div class="field"><label>Contact</label><span>{{ $logistic->contact_name }}</span></div>
        <div class="field"><label>{{ $logistic->type === 'entrant' ? 'Origine' : 'Destination' }}</label><span>{{ $logistic->origin_destination }}</span></div>
        @if($logistic->departure_date)
        <div class="field"><label>Date départ</label><span>{{ $logistic->departure_date->format('d/m/Y') }}</span></div>
        @endif
        @if($logistic->arrival_date)
        <div class="field"><label>Date arrivée prévue</label><span>{{ $logistic->arrival_date->format('d/m/Y') }}</span></div>
        @endif
    </div>
</div>

<!-- Détail produit -->
<div class="section">
    <div class="section-title">Détail des articles</div>
    <table class="product-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Référence</th>
                <th>Unité</th>
                <th style="text-align:right">Quantité</th>
            </tr>
        </thead>
        <tbody>
            @if($logistic->product && $logistic->quantity)
            <tr>
                <td>{{ $logistic->product->name }}</td>
                <td>{{ $logistic->product->code }}</td>
                <td>{{ $logistic->product->unit }}</td>
                <td style="text-align:right;font-weight:700;">{{ number_format($logistic->quantity, 2) }}</td>
            </tr>
            @else
            <tr><td colspan="4" style="color:#9ca3af;text-align:center;padding:20px;">Aucun article lié</td></tr>
            @endif
        </tbody>
    </table>
</div>

@if($logistic->notes)
<div class="section">
    <div class="section-title">Observations</div>
    <p style="color:#374151;font-size:13px;">{{ $logistic->notes }}</p>
</div>
@endif

<!-- Signatures -->
<div class="signatures">
    <div class="sig-box">
        <label>Émetteur (nom & signature)</label>
        <div class="sig-space"></div>
        <span style="font-size:11px;color:#9ca3af;">Date : ___/___/______</span>
    </div>
    <div class="sig-box">
        <label>Récepteur (nom & signature)</label>
        <div class="sig-space"></div>
        <span style="font-size:11px;color:#9ca3af;">Date : ___/___/______</span>
    </div>
</div>

<div class="footer">{{ $tenant->company_name }} — Document généré le {{ now()->format('d/m/Y à H:i') }}</div>

</body>
</html>
