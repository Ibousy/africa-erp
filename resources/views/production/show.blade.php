@extends('layouts.app')
@section('title', $production->reference)
@section('page-title', 'Ordre ' . $production->reference)

@section('header-actions')
    @if(in_array($production->status, ['brouillon', 'en_cours']))
    <a href="{{ route('material-requests.create', ['production_order_id' => $production->id]) }}" class="btn-secondary text-sm">📦 Demander matières</a>
    @endif
    <a href="{{ route('production.edit', $production) }}" class="btn-secondary">Modifier</a>
@endsection

@section('content')
<div class="mt-2 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Infos principales -->
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Informations</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Produit</dt>
                    <dd class="font-medium text-gray-800">{{ $production->product->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $production->status === 'termine' ? 'bg-green-100 text-green-700' :
                               ($production->status === 'en_cours' ? 'bg-blue-100 text-blue-700' :
                               ($production->status === 'annule' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                            @label($production->status)
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Qté planifiée</dt>
                    <dd class="font-medium">{{ number_format($production->quantity_planned, 2) }} {{ $production->product->unit }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Qté produite</dt>
                    <dd class="font-medium text-blue-600">{{ number_format($production->quantity_produced, 2) }} {{ $production->product->unit }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Rendement</dt>
                    <dd class="font-bold text-lg {{ $production->rendement >= 90 ? 'text-green-600' : ($production->rendement >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $production->rendement }}%
                    </dd>
                </div>
                @if($production->start_date)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Début</dt>
                    <dd>{{ $production->start_date->format('d/m/Y') }}</dd>
                </div>
                @endif
                @if($production->end_date)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Fin prévue</dt>
                    <dd>{{ $production->end_date->format('d/m/Y') }}</dd>
                </div>
                @endif
                @if($production->notes)
                <div class="pt-2 border-t border-gray-100">
                    <dt class="text-gray-500 mb-1">Notes</dt>
                    <dd class="text-gray-700">{{ $production->notes }}</dd>
                </div>
                @endif
            </dl>

            <!-- Barre de progression -->
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Progression</span>
                    <span>{{ $production->rendement }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $production->rendement >= 90 ? 'bg-green-500' : ($production->rendement >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ min($production->rendement, 100) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Mise à jour rapide -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Mettre à jour</h3>
            <form method="POST" action="{{ route('production.update', $production) }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="hidden" name="product_id" value="{{ $production->product_id }}">
                <input type="hidden" name="quantity_planned" value="{{ $production->quantity_planned }}">
                <input type="hidden" name="start_date" value="{{ $production->start_date }}">
                <input type="hidden" name="end_date" value="{{ $production->end_date }}">
                <input type="hidden" name="notes" value="{{ $production->notes }}">
                <div>
                    <label class="form-label text-xs">Qté produite</label>
                    <input type="number" name="quantity_produced" value="{{ $production->quantity_produced }}" min="0" step="0.01" class="form-input">
                </div>
                <div>
                    <label class="form-label text-xs">Statut</label>
                    <select name="status" class="form-input">
                        <option value="brouillon" {{ $production->status === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                        <option value="en_cours" {{ $production->status === 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="termine" {{ $production->status === 'termine' ? 'selected' : '' }}>Terminé</option>
                        <option value="annule" {{ $production->status === 'annule' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary w-full">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- Tâches -->
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Tâches ({{ $production->tasks->count() }})</h3>
                <button onclick="document.getElementById('add-task').classList.toggle('hidden')" class="btn-primary text-xs">+ Ajouter tâche</button>
            </div>

            <!-- Formulaire ajout tâche -->
            <div id="add-task" class="hidden p-5 bg-gray-50 border-b border-gray-100">
                <form method="POST" action="{{ route('production.tasks.store', $production) }}" class="flex flex-wrap gap-3">
                    @csrf
                    <input type="text" name="name" required placeholder="Nom de la tâche *" class="form-input flex-1 min-w-40">
                    <select name="assigned_to" class="form-input w-44">
                        <option value="">Assigner à...</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="description" placeholder="Description" class="form-input flex-1 min-w-40">
                    <button type="submit" class="btn-primary">Ajouter</button>
                </form>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse($production->tasks as $task)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full 
                            {{ $task->status === 'fait' ? 'bg-green-500' : ($task->status === 'en_cours' ? 'bg-blue-500' : 'bg-gray-300') }}"></span>
                        <div>
                            <p class="text-sm font-medium text-gray-800 {{ $task->status === 'fait' ? 'line-through text-gray-400' : '' }}">{{ $task->name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $task->assignedUser ? $task->assignedUser->name : 'Non assigné' }}
                                @if($task->description) · {{ $task->description }} @endif
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('production.tasks.update', $task) }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded px-2 py-1">
                            <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>À faire</option>
                            <option value="en_cours" {{ $task->status === 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="fait" {{ $task->status === 'fait' ? 'selected' : '' }}>Fait</option>
                        </select>
                    </form>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune tâche. Ajoutez-en une ci-dessus.</div>
                @endforelse
            </div>
        </div>

        <!-- Nomenclature (BOM) -->
        @if($production->product->bomItems->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Matières consommées (BOM)</h3>
                <a href="{{ route('products.bom.show', $production->product) }}" class="text-xs text-purple-600 hover:underline">Gérer BOM →</a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Composant</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500">Par unité</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500">Pour {{ number_format($production->quantity_planned, 2) }} unités</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500">Stock dispo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($production->product->bomItems as $bom)
                    @php $needed = $bom->quantity * $production->quantity_planned; @endphp
                    <tr>
                        <td class="px-5 py-2 text-gray-800">{{ $bom->component->name }}</td>
                        <td class="px-5 py-2 text-right text-gray-500 text-xs">{{ rtrim(rtrim(number_format($bom->quantity, 4), '0'), '.') }} {{ $bom->component->unit }}</td>
                        <td class="px-5 py-2 text-right font-medium {{ $bom->component->quantity_in_stock < $needed ? 'text-red-600' : 'text-gray-800' }}">
                            {{ number_format($needed, 2) }} {{ $bom->component->unit }}
                        </td>
                        <td class="px-5 py-2 text-right text-xs {{ $bom->component->quantity_in_stock < $needed ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                            {{ number_format($bom->component->quantity_in_stock, 2) }}
                            @if($bom->component->quantity_in_stock < $needed) ⚠️ @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Contrôles qualité liés -->
        @if($production->qualityControls->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Contrôles qualité</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($production->qualityControls as $qc)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $qc->check_date->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $qc->quantity_checked }} vérifiés, {{ $qc->quantity_defective }} défectueux</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $qc->status === 'passe' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $qc->status === 'passe' ? 'Passé' : 'Échoué' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
