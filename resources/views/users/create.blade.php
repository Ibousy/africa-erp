@extends('layouts.app')
@section('title', 'Ajouter un membre')
@section('page-title', 'Ajouter un membre')

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <!-- Info -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6 text-sm text-blue-700 flex gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                L'utilisateur pourra se connecter immédiatement avec l'email et le mot de passe que vous définissez.
                Il sera redirigé directement vers le tableau de bord de <strong>{{ $tenant->company_name }}</strong>.
            </div>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Prénom Nom" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Adresse email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="membre@entreprise.sn" class="form-input">
                </div>
                <div>
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" required placeholder="Min. 6 caractères" class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" name="password_confirmation" required class="form-input">
                </div>

                <div>
                    <label class="form-label">Rôle *</label>
                    <select name="role" required class="form-input">
                        <option value="operateur" {{ old('role', 'operateur') === 'operateur' ? 'selected' : '' }}>Opérateur</option>
                        <option value="manager"   {{ old('role') === 'manager'   ? 'selected' : '' }}>Manager</option>
                        <option value="admin"     {{ old('role') === 'admin'     ? 'selected' : '' }}>Administrateur</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+221 77 000 00 00" class="form-input">
                </div>
                <!-- Department — only meaningful for operators -->
                <div class="col-span-2" id="dept-row">
                    <label class="form-label">Département (opérateurs uniquement)</label>
                    <select name="department" class="form-input">
                        <option value="">— Aucun département —</option>
                        @foreach(\App\Models\User::DEPARTMENTS as $key => $label)
                        <option value="{{ $key }}" {{ old('department') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Détermine les modules visibles et la page d'accueil de l'opérateur.</p>
                </div>

                <div class="col-span-2">
                    <label class="form-label">Module de départ (page d'accueil après connexion)</label>
                    <select name="default_module" class="form-input">
                        <option value="">Tableau de bord général</option>
                        @foreach($modules as $key => $label)
                        <option value="{{ $key }}" {{ old('default_module') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Si défini, cet utilisateur atterrit directement sur ce module après connexion.</p>
                </div>
            </div>

            <!-- Description des rôles -->
            <div class="bg-gray-50 rounded-lg p-4 text-xs text-gray-500 space-y-1.5" id="role-help">
                <p id="help-operateur"><span class="font-semibold text-gray-700">Opérateur :</span> Saisie et consultation des données. Pas de suppression ni d'administration.</p>
                <p id="help-manager" class="hidden"><span class="font-semibold text-gray-700">Manager :</span> Accès complet aux données et rapports. Peut créer et modifier tout.</p>
                <p id="help-admin" class="hidden"><span class="font-semibold text-gray-700">Administrateur :</span> Accès total incluant gestion des utilisateurs et paramètres entreprise.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Créer le compte →</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const roleHelp = { operateur: 'help-operateur', manager: 'help-manager', admin: 'help-admin' };
const roleSelect = document.querySelector('select[name="role"]');
const deptRow = document.getElementById('dept-row');

function syncRole(val) {
    Object.values(roleHelp).forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById(roleHelp[val])?.classList.remove('hidden');
    deptRow.style.display = val === 'operateur' ? '' : 'none';
}

roleSelect.addEventListener('change', function() { syncRole(this.value); });
syncRole(roleSelect.value);
</script>
@endpush
@endsection
