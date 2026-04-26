<!DOCTYPE html>
<html lang="fr" data-theme="{{ $tenant?->theme ?? 'orange' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP') — {{ $tenant?->company_name ?? 'AfricaERP' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

@if(session('impersonating'))
<div class="w-full text-center text-xs py-2 text-white font-semibold bg-red-600">
    Mode impersonation — vous êtes connecté en tant que {{ auth()->user()->name }} ({{ auth()->user()->tenant?->company_name }})
    <form method="POST" action="{{ route('superadmin.stop-impersonate') }}" class="inline ml-4">
        @csrf
        <button type="submit" class="underline font-bold">← Retour Super Admin</button>
    </form>
</div>
@elseif($tenant && $tenant->status === 'trial')
<div class="w-full text-center text-xs py-2 text-white font-medium" style="background-color: var(--clr-pd)">
    Essai gratuit — {{ $tenant->trialDaysLeft() }} jour(s) restant(s).
    <a href="{{ route('settings.company') }}" class="underline ml-2">Gérer l'abonnement →</a>
</div>
@endif

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
        <!-- Logo / Entreprise -->
        <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700">
            @if($tenant?->logoUrl())
                <img src="{{ $tenant->logoUrl() }}" alt="Logo" class="w-9 h-9 rounded-lg object-cover">
            @else
                <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-lg text-white shrink-0" style="background-color: var(--clr-p)">
                    {{ strtoupper(substr($tenant?->company_name ?? 'A', 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <div class="font-bold text-white text-sm truncate">{{ $tenant?->company_name ?? 'AfricaERP' }}</div>
                <div class="text-xs text-gray-400">{{ $tenant?->industry ?? 'PME Industrielle' }}</div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Tableau de bord</span>
            </a>

            @php $u = auth()->user(); @endphp

            @if((!$tenant || $tenant->hasModule('stock')) && $u->canAccessModule('stock'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Stock</p>
            </div>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Produits</span>
            </a>
            <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Mouvements</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('production')) && $u->canAccessModule('production'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Production</p>
            </div>
            <a href="{{ route('production.index') }}" class="nav-link {{ request()->routeIs('production.index') || request()->routeIs('production.create') || request()->routeIs('production.show') || request()->routeIs('production.edit') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Fabrication</span>
            </a>
            <a href="{{ route('production.costs') }}" class="nav-link {{ request()->routeIs('production.costs') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Coûts & Marges</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('mrp')) && $u->canAccessModule('mrp'))
            <a href="{{ route('mrp.index') }}" class="nav-link {{ request()->routeIs('mrp.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span>MRP</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('quality')) && $u->canAccessModule('quality'))
            <a href="{{ route('quality.index') }}" class="nav-link {{ request()->routeIs('quality.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                <span>Qualité</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('maintenance')) && $u->canAccessModule('maintenance'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Maintenance</p>
            </div>
            <a href="{{ route('machines.index') }}" class="nav-link {{ request()->routeIs('machines.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Machines</span>
            </a>
            <a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 11-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                <span>Planification</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('logistics')) && $u->canAccessModule('logistics'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Logistique</p>
            </div>
            <a href="{{ route('logistics.index') }}" class="nav-link {{ request()->routeIs('logistics.index') || request()->routeIs('logistics.create') || request()->routeIs('logistics.edit') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Expéditions</span>
            </a>
            <a href="{{ route('logistics.stock') }}" class="nav-link {{ request()->routeIs('logistics.stock') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Stock produits</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="nav-link {{ false ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                <span>Factures</span>
            </a>
            <a href="{{ route('carriers.index') }}" class="nav-link {{ request()->routeIs('carriers.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                <span>Transporteurs</span>
            </a>
            <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 2 2 2-2 2 2 2-2 4 2z"/></svg>
                <span>Retours</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('purchases')) && $u->canAccessModule('purchases'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Achats</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Commandes fournisseurs</span>
            </a>
            <a href="{{ route('supplier-payments.index') }}" class="nav-link {{ request()->routeIs('supplier-payments.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Paiements fournisseurs</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('sales')) && $u->canAccessModule('sales'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Commercial</p>
            </div>
            <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Clients</span>
            </a>
            <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Devis</span>
            </a>
            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Commandes clients</span>
            </a>
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                <span>Factures</span>
            </a>
            <a href="{{ route('client-payments.index') }}" class="nav-link {{ request()->routeIs('client-payments.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Encaissements</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('crm')) && $u->canAccessModule('crm'))
            <a href="{{ route('crm.index') }}" class="nav-link {{ request()->routeIs('crm.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
                <span>CRM Prospects</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('accounting')) && $u->canAccessModule('accounting'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Finance</p>
            </div>
            <a href="{{ route('accounting.index') }}" class="nav-link {{ request()->routeIs('accounting.index') || request()->routeIs('accounting.create') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Transactions</span>
            </a>
            <a href="{{ route('accounting.reports') }}" class="nav-link {{ request()->routeIs('accounting.reports') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rapports financiers</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('hr')) && $u->canAccessModule('hr'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">RH</p>
            </div>
            <a href="{{ route('hr.index') }}" class="nav-link {{ request()->routeIs('hr.index') || request()->routeIs('hr.create') || request()->routeIs('hr.edit') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Employés</span>
            </a>
            <a href="{{ route('hr.salaries.index') }}" class="nav-link {{ request()->routeIs('hr.salaries.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Paie</span>
            </a>
            <a href="{{ route('hr.timelogs.index') }}" class="nav-link {{ request()->routeIs('hr.timelogs.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pointage</span>
            </a>
            <a href="{{ route('hr.leaves.index') }}" class="nav-link {{ request()->routeIs('hr.leaves.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Congés</span>
            </a>
            <a href="{{ route('hr.recruitment.index') }}" class="nav-link {{ request()->routeIs('hr.recruitment.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Recrutement</span>
            </a>
            <a href="{{ route('hr.performance.index') }}" class="nav-link {{ request()->routeIs('hr.performance.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <span>Évaluations</span>
            </a>
            @endif

            @if((!$tenant || $tenant->hasModule('energy')) && $u->canAccessModule('energy'))
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Énergie</p>
            </div>
            <a href="{{ route('energy.index') }}" class="nav-link {{ request()->routeIs('energy.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Consommation</span>
            </a>
            @endif

            {{-- Chat & Demandes --}}
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Communication</p>
            </div>
            <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Chat départements</span>
            </a>
            <a href="{{ route('meetings.index') }}" class="nav-link {{ request()->routeIs('meetings.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Réunions</span>
            </a>
            @php $mrRoute = (auth()->user()->department === 'logistique' || auth()->user()->isAdmin()) ? 'material-requests.logistics' : 'material-requests.index'; @endphp
            <a href="{{ route($mrRoute) }}" class="nav-link {{ request()->routeIs('material-requests.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Demandes matières</span>
            </a>

            @if(auth()->user()->isAdmin())
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase px-3">Administration</p>
            </div>
            @if(!$tenant || $tenant->hasModule('users'))
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Utilisateurs</span>
            </a>
            @endif
            <a href="{{ route('settings.company') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'nav-active' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Mon Entreprise</span>
            </a>
            @endif
        </nav>

        <!-- User footer -->
        <div class="border-t border-gray-700 px-4 py-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0" style="background-color: var(--clr-p)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <a href="{{ route('profile.show') }}" class="flex-1 min-w-0 group">
                    <p class="text-sm font-medium text-white truncate group-hover:underline">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">@label(auth()->user()->role)</p>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white" title="Déconnexion">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Tableau de bord')</h1>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')

                {{-- Chat --}}
                <a href="{{ route('chat.index') }}" id="chat-btn" class="relative p-2 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors" title="Chat inter-départements">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span id="chat-badge" class="absolute -top-0.5 -right-0.5 w-4 h-4 text-[10px] font-bold text-white rounded-full hidden items-center justify-center" style="background:var(--clr-p)"></span>
                </a>

                {{-- Notifications --}}
                <div class="relative" id="notif-wrapper">
                    <button onclick="toggleNotifPanel()" class="relative p-2 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors" title="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span id="notif-badge" class="absolute -top-0.5 -right-0.5 w-4 h-4 text-[10px] font-bold text-white rounded-full hidden items-center justify-center" style="background:var(--clr-p)"></span>
                    </button>
                    {{-- Dropdown panel --}}
                    <div id="notif-panel" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <span class="font-semibold text-sm text-gray-800">Notifications</span>
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button class="text-xs text-gray-400 hover:text-gray-600">Tout marquer lu</button>
                            </form>
                        </div>
                        <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                            <div class="px-4 py-6 text-center text-xs text-gray-400">Chargement...</div>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs font-medium hover:underline" style="color:var(--clr-p)">Voir tout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="flex-1 overflow-y-auto px-6 pb-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')

<script>
// ── Notification panel ─────────────────────────────────────
function toggleNotifPanel() {
    const panel = document.getElementById('notif-panel');
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) loadNotifPanel();
}
document.addEventListener('click', e => {
    const w = document.getElementById('notif-wrapper');
    if (w && !w.contains(e.target)) document.getElementById('notif-panel')?.classList.add('hidden');
});

const _icons = { bell:'🔔', warning:'⚠️', message:'💬', truck:'🚛', check:'✅', x:'❌', low_stock:'📦', meeting:'📅' };

function loadNotifPanel() {
    const list = document.getElementById('notif-list');
    list.innerHTML = '<div class="px-4 py-6 text-center text-xs text-gray-400">Chargement…</div>';
    fetch('{{ route("notifications.panel") }}')
        .then(r => r.json())
        .then(data => {
            // Badge to 0 immediately — notifications deleted server-side after this fetch
            _updateBadge('notif-badge', 0);
            if (!data.length) { list.innerHTML = '<div class="px-4 py-6 text-center text-xs text-gray-400">Aucune notification</div>'; return; }
            list.innerHTML = data.map(n => `
                <a href="${n.link||'#'}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 transition-colors ${n.read?'':'bg-blue-50/40'}">
                    <span class="text-xl shrink-0">${_icons[n.icon]||'🔔'}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-gray-800 leading-tight">${n.title}</p>
                        ${n.body ? `<p class="text-xs text-gray-500 mt-0.5 truncate">${n.body}</p>` : ''}
                        <p class="text-xs text-gray-400 mt-0.5">${n.time}</p>
                    </div>
                </a>`).join('');
        }).catch(() => { list.innerHTML = '<div class="px-4 py-4 text-center text-xs text-red-400">Erreur</div>'; });
}

function _updateBadge(id, count) {
    const el = document.getElementById(id);
    if (!el) return;
    if (count > 0) { el.textContent = count > 9 ? '9+' : count; el.classList.remove('hidden'); el.classList.add('flex'); }
    else { el.classList.add('hidden'); el.classList.remove('flex'); }
}

function pollCounts() {
    fetch('{{ route("notifications.count") }}').then(r => r.json()).then(d => _updateBadge('notif-badge', d.count)).catch(()=>{});
}
pollCounts();
setInterval(pollCounts, 30000);
</script>
</body>
</html>
