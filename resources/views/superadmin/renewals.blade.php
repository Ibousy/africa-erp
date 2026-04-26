@extends('layouts.superadmin')
@section('title', 'Demandes de renouvellement')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Demandes de renouvellement</h1>
            <p class="text-sm text-gray-500 mt-0.5">Traitez les demandes comme un renouvellement de passeport</p>
        </div>
        <a href="{{ route('superadmin.index') }}" class="btn-secondary text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Tableau de bord
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-800 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold">En attente</p>
            <p class="text-3xl font-bold text-amber-500 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold">Approuvées (30j)</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold">CA renouvellements</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ money($stats['revenue']) }}</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex gap-3 items-center">
            <select name="status" class="form-input text-sm w-40">
                <option value="">Tous statuts</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvées</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusées</option>
            </select>
            <button type="submit" class="btn-primary text-sm">Filtrer</button>
            <a href="{{ route('superadmin.renewals') }}" class="btn-secondary text-sm">Reset</a>
        </form>
    </div>

    {{-- Table des demandes --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Entreprise</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Plan · Durée</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Paiement</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Soumis le</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($renewals as $r)
                <tr class="hover:bg-gray-50 {{ $r->isPending() ? 'bg-amber-50/40' : '' }}">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white shrink-0" style="background:#f97316">
                                {{ strtoupper(substr($r->tenant->company_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $r->tenant->company_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">
                                    <span class="px-1.5 py-0.5 rounded text-xs
                                        {{ $r->tenant->status === 'expired' ? 'bg-red-100 text-red-600' :
                                           ($r->tenant->status === 'suspended' ? 'bg-orange-100 text-orange-600' : 'bg-green-100 text-green-700') }}">
                                        {{ ['active' => 'Actif', 'trial' => 'Essai', 'expired' => 'Expiré', 'suspended' => 'Suspendu'][$r->tenant->status ?? ''] ?? '—' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-semibold text-gray-800">{{ strtoupper($r->plan) }}</p>
                        <p class="text-xs text-gray-400">{{ $r->duration_months }} mois</p>
                    </td>
                    <td class="px-5 py-3 text-right font-bold text-gray-800">{{ money($r->amount) }}</td>
                    <td class="px-5 py-3">
                        <p class="text-gray-700">{{ \App\Models\RenewalRequest::METHOD_LABELS[$r->payment_method] ?? $r->payment_method }}</p>
                        @if($r->payment_reference)
                        <p class="text-xs font-mono text-gray-400">{{ $r->payment_reference }}</p>
                        @endif
                        @if($r->notes)
                        <p class="text-xs text-gray-400 italic mt-0.5 max-w-xs truncate" title="{{ $r->notes }}">{{ $r->notes }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3">
                        @if($r->isPending())
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">En attente</span>
                        @elseif($r->isApproved())
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700">Approuvée</span>
                        <p class="text-xs text-gray-400 mt-0.5">par {{ $r->processor->name ?? '—' }}</p>
                        @else
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-600">Refusée</span>
                        @if($r->rejection_reason)
                        <p class="text-xs text-gray-400 mt-0.5 max-w-xs truncate" title="{{ $r->rejection_reason }}">{{ $r->rejection_reason }}</p>
                        @endif
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if($r->isPending())
                        <div class="flex items-center justify-end gap-2">
                            {{-- Approuver --}}
                            <form method="POST" action="{{ route('superadmin.renewals.approve', $r) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center gap-1"
                                    onclick="return confirm('Approuver et activer l\'abonnement de {{ $r->tenant->company_name }} ?')">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Approuver
                                </button>
                            </form>
                            {{-- Refuser --}}
                            <button onclick="openRejectModal({{ $r->id }})"
                                class="px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 rounded-lg transition-colors">
                                Refuser
                            </button>
                        </div>
                        @else
                        <a href="{{ route('superadmin.show', $r->tenant_id) }}" class="text-xs text-blue-500 hover:text-blue-700">Voir compte</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">Aucune demande de renouvellement</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $renewals->links() }}</div>
    </div>
</div>

{{-- Modal refus --}}
<div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="font-semibold text-gray-900 text-lg mb-4">Refuser la demande</h3>
        <form id="rejectForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="form-label text-sm">Motif du refus *</label>
                <textarea name="rejection_reason" rows="3" required class="form-input text-sm"
                    placeholder="Ex: Paiement non reçu, référence incorrecte..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    Confirmer le refus
                </button>
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-2.5 text-sm font-semibold text-gray-700 border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = '/superadmin/renewals/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>
@endsection
