@extends('layouts.app')
@section('title', $client->name)
@section('page-title', 'Fiche client — ' . $client->name)
@section('header-actions')
    <a href="{{ route('clients.edit', $client) }}" class="btn-secondary text-xs">Modifier</a>
    <a href="{{ route('clients.index') }}" class="btn-secondary text-xs">Retour</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">

    <!-- Info + stats row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Infos -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl font-bold text-white shrink-0" style="background-color:var(--clr-p)">
                    {{ strtoupper(substr($client->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">{{ $client->name }}</h2>
                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold
                        {{ $client->segment === 'vip' ? 'bg-yellow-100 text-yellow-700' :
                           ($client->segment === 'regulier' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ \App\Models\Client::SEGMENTS[$client->segment] ?? $client->segment }}
                    </span>
                </div>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Code</dt><dd class="font-mono text-gray-700">{{ $client->code }}</dd></div>
                @if($client->contact_person)
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Contact</dt><dd class="text-gray-700">{{ $client->contact_person }}</dd></div>
                @endif
                @if($client->phone)
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Téléphone</dt><dd class="text-gray-700">{{ $client->phone }}</dd></div>
                @endif
                @if($client->email)
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Email</dt><dd class="text-gray-700">{{ $client->email }}</dd></div>
                @endif
                @if($client->address)
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Adresse</dt><dd class="text-gray-700">{{ $client->address }}@if($client->city), {{ $client->city }}@endif</dd></div>
                @endif
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Pays</dt><dd class="text-gray-700">{{ $client->country }}</dd></div>
                <div class="flex gap-2"><dt class="text-gray-500 w-28 shrink-0">Client depuis</dt><dd class="text-gray-700">{{ $client->created_at->format('d/m/Y') }}</dd></div>
            </dl>
        </div>

        <!-- Stats financières -->
        <div class="lg:col-span-2 grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Solde dû</p>
                <p class="text-2xl font-bold mt-1 {{ $client->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ money($client->balance_due) }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Points fidélité</p>
                <p class="text-2xl font-bold text-yellow-500 mt-1">{{ money($client->loyalty_points) }} pts</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Limite de crédit</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($client->credit_limit) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 uppercase font-semibold">Commandes</p>
                <p class="text-2xl font-bold mt-1" style="color:var(--clr-p)">{{ $client->customerOrders->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs: devis / commandes / paiements -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Devis récents -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Devis récents</h3>
                <a href="{{ route('quotes.create') }}" class="text-xs btn-primary">+ Devis</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($client->quotes as $q)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-xs text-gray-700">{{ $q->reference }}</p>
                        <p class="text-xs text-gray-400">{{ $q->issue_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-800">{{ money($q->total) }}</p>
                        <span class="px-1.5 py-0.5 text-xs rounded-full
                            {{ $q->status === 'accepte' ? 'bg-green-100 text-green-700' :
                               ($q->status === 'refuse' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ['brouillon' => 'Brouillon','envoye' => 'Envoyé','accepte' => 'Accepté','refuse' => 'Refusé','expire' => 'Expiré'][$q->status] ?? $q->status }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Aucun devis</p>
                @endforelse
            </div>
        </div>

        <!-- Commandes récentes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Commandes récentes</h3>
                <a href="{{ route('orders.create') }}" class="text-xs btn-primary">+ Commande</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($client->customerOrders as $o)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-xs text-gray-700">{{ $o->reference }}</p>
                        <p class="text-xs text-gray-400">{{ $o->order_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-800">{{ money($o->total_amount) }}</p>
                        <span class="px-1.5 py-0.5 text-xs rounded-full
                            {{ $o->status === 'livre' ? 'bg-green-100 text-green-700' :
                               ($o->status === 'annule' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ ['brouillon'=>'Brouillon','confirme'=>'Confirmé','en_preparation'=>'Prép.','livre'=>'Livré','annule'=>'Annulé'][$o->status] ?? $o->status }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Aucune commande</p>
                @endforelse
            </div>
        </div>

        <!-- Paiements récents -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Paiements récents</h3>
                <a href="{{ route('client-payments.index') }}" class="text-xs text-blue-500 hover:underline">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($client->payments as $p)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-700">{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ ['especes'=>'Espèces','virement'=>'Virement','cheque'=>'Chèque','mobile'=>'Mobile'][$p->method] ?? $p->method }}</p>
                    </div>
                    <p class="text-sm font-bold text-green-600">+{{ money($p->amount) }}</p>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-gray-400 text-sm">Aucun paiement</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
