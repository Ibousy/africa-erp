@extends('layouts.app')
@section('title', 'Paiements clients')
@section('page-title', 'Paiements clients')

@section('content')
<div class="mt-2 space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Total encaissé (tout)</p>
            <p class="text-xl font-bold mt-1" style="color:var(--clr-p)">{{ money($stats['total_recu']) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500 uppercase font-semibold">Ce mois</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ money($stats['ce_mois']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Nouveau paiement -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Enregistrer un paiement</h3>
            <form method="POST" action="{{ route('client-payments.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label text-xs">Client *</label>
                    <select name="client_id" required class="form-input text-xs">
                        <option value="">Sélectionner...</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Montant (FCFA) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required min="0.01" step="1" class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Date *</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Mode de paiement *</label>
                    <select name="method" required class="form-input text-xs">
                        @foreach(\App\Models\ClientPayment::METHODS as $k => $v)
                        <option value="{{ $k }}" {{ old('method', 'especes') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Référence</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="N° chèque, reçu..." class="form-input text-xs">
                </div>
                <div>
                    <label class="form-label text-xs">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="form-input text-xs">
                </div>
                <button type="submit" class="btn-primary w-full text-sm">Enregistrer le paiement</button>
            </form>
        </div>

        <!-- Historique -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold text-gray-800 text-sm">Historique</h3>
                <form method="GET" class="flex gap-2">
                    <select name="client_id" class="form-input text-xs w-44">
                        <option value="">Tous clients</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <select name="method" class="form-input text-xs w-36">
                        <option value="">Tous modes</option>
                        @foreach(\App\Models\ClientPayment::METHODS as $k => $v)
                        <option value="{{ $k }}" {{ request('method') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary text-xs">Filtrer</button>
                </form>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Mode</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $p->client->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $p->payment_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-right font-bold text-green-600">+{{ money($p->amount) }}</td>
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ \App\Models\ClientPayment::METHODS[$p->method] ?? $p->method }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">{{ $p->reference ?: '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('client-payments.destroy', $p) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 text-xs">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Aucun paiement</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $payments->links() }}</div>
        </div>
    </div>
</div>
@endsection
