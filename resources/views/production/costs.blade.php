@extends('layouts.app')
@section('title', 'Coûts de production')
@section('page-title', 'Tableau de bord — Coûts de Production')
@section('header-actions')
    <a href="{{ route('production.index') }}" class="btn-secondary text-xs">Ordres de fabrication</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">

    <!-- Mensuel activité -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 text-sm mb-4">Activité de production {{ now()->year }}</h3>
        <div class="grid grid-cols-12 gap-1 items-end h-24">
            @php $moisLabels = ['J','F','M','A','M','J','J','A','S','O','N','D'];
            $maxQty = $monthlyProduction->max('qty') ?: 1; @endphp
            @for($m = 1; $m <= 12; $m++)
            @php $data = $monthlyProduction->get($m); $h = $data ? round($data->qty / $maxQty * 80) : 0; @endphp
            <div class="flex flex-col items-center gap-1">
                <div class="w-full rounded-t" style="height: {{ max($h, 2) }}px; background-color: var(--clr-p); opacity: {{ $data ? '1' : '0.2' }}"></div>
                <span class="text-xs text-gray-500">{{ $moisLabels[$m-1] }}</span>
                @if($data)<span class="text-xs font-bold text-gray-700">{{ $data->count }}</span>@endif
            </div>
            @endfor
        </div>
        <p class="text-xs text-gray-400 mt-2">Nombre d'ordres terminés par mois</p>
    </div>

    <!-- Analyse des coûts par produit -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Analyse coûts / marges par produit (nomenclature BOM)</h3>
        </div>
        @if($productCosts->isEmpty())
        <p class="px-5 py-12 text-center text-gray-400">Aucun produit avec nomenclature définie. <a href="{{ route('products.index') }}" class="text-blue-500 hover:underline">Configurer les BOM →</a></p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit fini</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Coût BOM</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Prix de vente</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Marge</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Marge %</th>
                        <th class="px-5 py-3 w-36"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($productCosts as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $row['product']->name }}</p>
                            <p class="text-xs text-gray-400">{{ $row['product']->bomItems->count() }} composant(s)</p>
                        </td>
                        <td class="px-5 py-3 text-right text-red-500 font-medium">{{ money($row['bom_cost']) }}</td>
                        <td class="px-5 py-3 text-right text-gray-700 font-medium">{{ money($row['sell_price']) }}</td>
                        <td class="px-5 py-3 text-right font-bold {{ $row['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($row['margin'] >= 0 ? '+' : '') . money($row['margin']) }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $row['margin_pct'] >= 20 ? 'bg-green-100 text-green-700' : ($row['margin_pct'] >= 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $row['margin_pct'] }}%
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @php $pct = $row['sell_price'] > 0 ? min(100, round($row['bom_cost'] / $row['sell_price'] * 100)) : 100; @endphp
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-red-400 h-2 rounded-l-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $pct }}% du prix = coût matière</p>
                        </td>
                    </tr>
                    @foreach($row['product']->bomItems as $bom)
                    <tr class="bg-gray-50/50">
                        <td class="px-5 py-1.5 pl-10 text-xs text-gray-500">↳ {{ $bom->component->name ?? '?' }} × {{ $bom->quantity }}</td>
                        <td class="px-5 py-1.5 text-right text-xs text-gray-400">{{ money($bom->quantity * ($bom->component->unit_price ?? 0)) }}</td>
                        <td colspan="4"></td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Ordres récents terminés -->
    @if($ordersThisYear->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 text-sm">Ordres terminés cette année</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produit</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Qté produite</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date fin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($ordersThisYear as $of)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $of->reference }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $of->product->name ?? '?' }}</td>
                    <td class="px-5 py-3 text-right font-medium text-gray-700">{{ number_format($of->quantity_produced, 2) }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $of->updated_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection
