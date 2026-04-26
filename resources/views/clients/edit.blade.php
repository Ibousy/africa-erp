@extends('layouts.app')
@section('title', 'Modifier client')
@section('page-title', 'Modifier client')

@section('content')
<div class="max-w-xl mt-2">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Code *</label>
                    <input type="text" name="code" value="{{ old('code', $client->code) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Contact</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $client->contact_person) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $client->city) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Pays</label>
                    <input type="text" name="country" value="{{ old('country', $client->country) }}" required class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Adresse</label>
                    <textarea name="address" rows="2" class="form-input">{{ old('address', $client->address) }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Enregistrer</button>
                <a href="{{ route('clients.index') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
