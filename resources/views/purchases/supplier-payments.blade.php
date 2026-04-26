@extends('layouts.app')
@section('title', 'Paiements fournisseurs')
@section('page-title', 'Paiements Fournisseurs')

@section('content')
<div class="mt-2 space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total payé (cumulé)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ money($stats['total_paye']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Payé ce mois</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--clr-p)">{{ money($stats['ce_mois']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Formulaire -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Enregistrer un paiement</h3>
            <form method="POST" action="{{ route('supplier-payments.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label text-xs">Fournisseur *</label>
                    <select name="supplier_id" required class="form-input text-xs">
                        <option value="">Sélectionner...</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                            @if($s->balance_due > 0)
                            (dû: {{ money($s->balance_due) }})
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Commande liée (optionnel)</label>
                    <select name="purchase_order_id" class="form-input text-xs">
                        <option value="">— Aucune —</option>
                        @foreach($orders as $o)
                        <option value="{{ $o->id }}" {{ old('purchase_order_id') == $o->id ? 'selected' : '' }}>
                            {{ $o->reference }} — {{ $o->supplier->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Montant *</label>
                    <input type="number" name="amount" required min="0.01" step="0.01" value="{{ old('amount') }}" class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Date de paiement *</label>
                    <input type="date" name="payment_date" required value="{{ old('payment_date', date('Y-m-d')) }}" class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Mode de paiement *</label>
                    <select name="method" required class="form-input text-xs">
                        <option value="especes" {{ old('method') === 'especes' ? 'selected' : '' }}>Espèces</option>
                        <option value="virement" {{ old('method') === 'virement' ? 'selected' : '' }}>Virement</option>
                        <option value="cheque" {{ old('method') === 'cheque' ? 'selected' : '' }}>Chèque</option>
                        <option value="mobile" {{ old('method') === 'mobile' ? 'selected' : '' }}>Mobile money</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Référence</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="form-input text-xs" placeholder="N° virement, chèque...">
                </div>
                <div>
                    <label class="form-label text-xs">Notes</label>
                    <textarea name="notes" rows="2" class="form-input text-xs">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn-primary w-full text-sm">Enregistrer</button>
            </form>
        </div>

        <!-- Liste -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-800 text-sm">Historique des paiements</h3>
                <form method="GET" class="flex gap-2">
                    <select name="supplier_id" class="form-input text-xs w-44">
                        <option value="">Tous les fournisseurs</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary text-xs">Filtrer</button>
                </form>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Fournisseur</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Commande</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mode</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $p->supplier->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $p->purchaseOrder?->reference ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ money($p->amount) }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
                                {{ ['especes' => 'Espèces', 'virement' => 'Virement', 'cheque' => 'Chèque', 'mobile' => 'Mobile'][$p->method] ?? $p->method }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('supplier-payments.destroy', $p) }}" onsubmit="return confirm('Supprimer ce paiement ?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">Aucun paiement</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $payments->links() }}</div>
        </div>
    </div>
</div>
@endsection
