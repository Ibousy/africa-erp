@extends('layouts.app')
@section('title', 'Nouveau client')
@section('page-title', 'Nouveau client')

@section('content')
<div class="max-w-xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Code *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="form-input" placeholder="CLI-001">
                </div>
                <div>
                    <label class="form-label">Nom / Raison sociale *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Personne de contact</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+221 77 000 00 00">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Pays</label>
                    <input type="text" name="country" value="{{ old('country', 'Sénégal') }}" required class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Adresse</label>
                    <textarea name="address" rows="2" class="form-input">{{ old('address') }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Créer le client</button>
                <a href="{{ route('clients.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
