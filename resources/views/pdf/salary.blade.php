<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1f2937; background:#fff; }
.page { padding:36px 40px; }
.header { text-align:center; margin-bottom:28px; border-bottom:2px solid #10b981; padding-bottom:18px; }
.title { font-size:18px; font-weight:bold; color:#111827; }
.subtitle { font-size:11px; color:#6b7280; margin-top:3px; }
.company { font-size:13px; font-weight:bold; color:#10b981; margin-top:6px; }
.info-grid { display:flex; gap:30px; margin-bottom:24px; }
.info-block { flex:1; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px 16px; }
.info-label { font-size:9px; font-weight:bold; text-transform:uppercase; color:#9ca3af; margin-bottom:4px; }
.info-val { font-size:13px; font-weight:bold; color:#111827; }
.info-sub { font-size:10px; color:#6b7280; margin-top:2px; }
.salary-table { width:100%; border-collapse:collapse; margin:20px 0; }
.salary-table tr { border-bottom:1px solid #e5e7eb; }
.salary-table td { padding:10px 14px; font-size:11px; }
.salary-table td:last-child { text-align:right; font-weight:600; }
.salary-table .positive { color:#059669; }
.salary-table .negative { color:#dc2626; }
.total-row td { background:#f9fafb; font-size:13px; font-weight:bold; border-top:2px solid #e5e7eb; }
.status-badge { display:inline-block; padding:3px 12px; border-radius:999px; font-size:10px; font-weight:bold; }
.status-paye { background:#d1fae5; color:#065f46; }
.status-brouillon { background:#fef3c7; color:#92400e; }
.footer { position:fixed; bottom:20px; left:40px; right:40px; border-top:1px solid #e5e7eb; padding-top:6px; display:flex; justify-content:space-between; font-size:9px; color:#9ca3af; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="company">{{ $salary->tenant?->company_name ?? 'Entreprise' }}</div>
        <div class="title">BULLETIN DE PAIE</div>
        <div class="subtitle">Période : {{ $salary->period }}</div>
    </div>

    <div class="info-grid">
        <div class="info-block">
            <div class="info-label">Employé</div>
            <div class="info-val">{{ $salary->employee->name }}</div>
            <div class="info-sub">{{ $salary->employee->position ?? '' }}</div>
            <div class="info-sub">{{ $salary->employee->department ?? '' }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Période</div>
            <div class="info-val">{{ $salary->period }}</div>
            <div class="info-sub">Généré le {{ now()->format('d/m/Y') }}</div>
        </div>
        <div class="info-block">
            <div class="info-label">Statut</div>
            <div class="info-val">
                <span class="status-badge {{ $salary->status === 'paye' ? 'status-paye' : 'status-brouillon' }}">
                    {{ $salary->status === 'paye' ? 'PAYÉ' : 'BROUILLON' }}
                </span>
            </div>
            @if($salary->paid_at)
            <div class="info-sub">Payé le {{ $salary->paid_at->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <table class="salary-table">
        <tr>
            <td>Salaire de base</td>
            <td class="positive">+ {{ money($salary->base_salary) }}</td>
        </tr>
        @if($salary->bonuses > 0)
        <tr>
            <td>Primes / Bonus</td>
            <td class="positive">+ {{ money($salary->bonuses) }}</td>
        </tr>
        @endif
        @if($salary->deductions > 0)
        <tr>
            <td>Retenues / Déductions</td>
            <td class="negative">- {{ money($salary->deductions) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>SALAIRE NET À PAYER</td>
            <td>{{ money($salary->net_salary) }}</td>
        </tr>
    </table>

    @if($salary->notes)
    <div style="margin-top:16px; padding:10px 14px; background:#f9fafb; border-left:3px solid #10b981; border-radius:0 6px 6px 0; font-size:10px; color:#374151;">
        <strong>Notes :</strong> {{ $salary->notes }}
    </div>
    @endif

    <div style="margin-top:40px; display:flex; justify-content:space-between;">
        <div style="text-align:center; width:200px;">
            <div style="border-top:1px solid #9ca3af; padding-top:6px; font-size:10px; color:#6b7280;">Signature employeur</div>
        </div>
        <div style="text-align:center; width:200px;">
            <div style="border-top:1px solid #9ca3af; padding-top:6px; font-size:10px; color:#6b7280;">Signature employé</div>
        </div>
    </div>
</div>
<div class="footer">
    <span>{{ $salary->employee->name }} — {{ $salary->period }} — {{ $salary->tenant?->company_name }}</span>
    <span>Document confidentiel</span>
</div>
</body>
</html>
