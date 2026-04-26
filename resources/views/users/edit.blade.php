@extends('layouts.app')
@section('title', 'Modifier — ' . $user->name)
@section('page-title', 'Modifier ' . $user->name)

@section('content')
<div class="mt-2 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <!-- Profil rapide -->
        <div class="flex items-center gap-4 pb-5 mb-5 border-b border-gray-100">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0"
                style="background-color: var(--clr-p)">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }} · Membre depuis {{ $user->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-input" placeholder="Laisser vide pour ne pas changer">
                </div>
                <div>
                    <label class="form-label">Confirmer</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
                <div>
                    <label class="form-label">Rôle *</label>
                    <select name="role" required class="form-input">
                        <option value="operateur" {{ old('role', $user->role) === 'operateur' ? 'selected' : '' }}>Opérateur</option>
                        <option value="manager"   {{ old('role', $user->role) === 'manager'   ? 'selected' : '' }}>Manager</option>
                        <option value="admin"     {{ old('role', $user->role) === 'admin'     ? 'selected' : '' }}>Administrateur</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input">
                </div>
                <!-- Department -->
                <div class="col-span-2" id="dept-row">
                    <label class="form-label">Département (opérateurs uniquement)</label>
                    <select name="department" class="form-input">
                        <option value="">— Aucun département —</option>
                        @foreach(\App\Models\User::DEPARTMENTS as $key => $label)
                        <option value="{{ $key }}" {{ old('department', $user->department) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Détermine les modules visibles et la page d'accueil de l'opérateur.</p>
                </div>

                <div class="col-span-2">
                    <label class="form-label">Module de départ (page d'accueil après connexion)</label>
                    <select name="default_module" class="form-input">
                        <option value="" {{ old('default_module', $user->default_module) === null || old('default_module', $user->default_module) === '' ? 'selected' : '' }}>
                            Tableau de bord général
                        </option>
                        @foreach($modules as $key => $label)
                        <option value="{{ $key }}" {{ old('default_module', $user->default_module) === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Cet utilisateur atterrit directement sur ce module après connexion.</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                    {{ $user->is_active ? 'checked' : '' }}
                    {{ $user->id === auth()->id() ? 'disabled' : '' }}
                    class="w-4 h-4 rounded accent-orange-500">
                <label for="is_active" class="text-sm text-gray-700 cursor-pointer">
                    Compte actif
                    <span class="text-gray-400 text-xs">— désactiver empêche la connexion sans supprimer les données</span>
                </label>
            </div>

            @if($user->id === auth()->id())
            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                Vous ne pouvez pas désactiver votre propre compte.
            </p>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-2.5">Enregistrer les modifications</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
const roleSelect = document.querySelector('select[name="role"]');
const deptRow = document.getElementById('dept-row');
function syncDept(val) { deptRow.style.display = val === 'operateur' ? '' : 'none'; }
roleSelect.addEventListener('change', function() { syncDept(this.value); });
syncDept(roleSelect.value);
</script>
@endpush
@endsection
