@extends('layouts.app')
@section('title', 'Équipe')
@section('page-title', 'Équipe')

@section('header-actions')
    <a href="{{ route('users.create') }}" class="btn-primary">+ Ajouter un membre</a>
@endsection

@section('content')
<div class="mt-2 space-y-4">

    <!-- Info équipe -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-800">{{ $users->total() }} membre(s) dans votre équipe</p>
            <p class="text-xs text-gray-400 mt-0.5">Gérez les accès et les rôles de votre entreprise</p>
        </div>
        <div class="flex -space-x-2">
            @foreach($users->take(5) as $u)
            <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold"
                style="background-color: var(--clr-p)" title="{{ $u->name }}">
                {{ strtoupper(substr($u->name, 0, 1)) }}
            </div>
            @endforeach
            @if($users->total() > 5)
            <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold">
                +{{ $users->total() - 5 }}
            </div>
            @endif
        </div>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Membre</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rôle</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 {{ !$user->is_active ? 'opacity-60' : '' }}">
                    <td class="px-5 py-3 font-medium text-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                style="background-color: var(--clr-p)">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                @if($user->phone)
                                <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                                @endif
                            </div>
                            @if($user->id === auth()->id())
                            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Vous</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' :
                               ($user->role === 'manager' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                            @label($user->role)
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Actif' : 'Désactivé' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('users.edit', $user) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium mr-3">Modifier</a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Supprimer {{ $user->name }} ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium">Retirer</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center">
                        <div class="text-gray-400 text-sm">Aucun autre membre dans votre équipe.</div>
                        <a href="{{ route('users.create') }}" class="btn-primary mt-4 inline-flex">+ Ajouter le premier membre</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $users->links() }}</div>
    </div>

    <!-- Légende des rôles -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Droits par rôle</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600">
            <div class="space-y-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 font-semibold rounded-full text-xs">Administrateur</span>
                </div>
                <p>✓ Accès complet à tous les modules</p>
                <p>✓ Gestion des utilisateurs et paramètres</p>
                <p>✓ Peut modifier l'abonnement</p>
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 font-semibold rounded-full text-xs">Manager</span>
                </div>
                <p>✓ Accès à tous les modules</p>
                <p>✓ Création et modification des données</p>
                <p>✗ Pas de gestion des utilisateurs</p>
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 font-semibold rounded-full text-xs">Opérateur</span>
                </div>
                <p>✓ Lecture et saisie des données</p>
                <p>✓ Mise à jour des statuts</p>
                <p>✗ Pas de suppression ni d'administration</p>
            </div>
        </div>
    </div>
</div>
@endsection
