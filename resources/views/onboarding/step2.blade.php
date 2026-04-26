@extends('layouts.public')
@section('title', 'Votre entreprise — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-xl">
        <!-- Logo -->
        <div class="flex items-center gap-3 justify-center mb-8">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white">A</div>
            <span class="font-bold text-gray-900 text-lg">AfricaERP</span>
        </div>

        <!-- Progress -->
        <div class="mb-8">
            <div class="flex items-center mb-3">
                @foreach(['Compte', 'Entreprise', 'Modules', 'Thème'] as $i => $label)
                <div class="flex items-center {{ $i < 3 ? 'flex-1' : '' }}">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $i === 1 ? 'bg-orange-500 text-white' : ($i < 1 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400') }}">
                        {{ $i < 1 ? '✓' : $i + 1 }}
                    </div>
                    @if($i < 3)<div class="flex-1 h-px mx-2 {{ $i < 1 ? 'bg-green-400' : 'bg-gray-200' }}"></div>@endif
                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-gray-400">
                <span class="text-green-600 font-semibold">Compte</span>
                <span class="text-orange-600 font-semibold">Entreprise</span>
                <span>Modules</span>
                <span>Thème</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Votre entreprise</h1>
            <p class="text-gray-500 text-sm mb-7">Ces informations apparaîtront sur vos documents et dans votre ERP.</p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('onboarding.step2') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Logo -->
                <div>
                    <label class="form-label">Logo de l'entreprise</label>
                    <div class="mt-1 flex items-center gap-4">
                        <div id="logo-preview" class="w-16 h-16 bg-orange-100 rounded-xl flex items-center justify-center text-orange-500 font-bold text-2xl border-2 border-dashed border-orange-300">A</div>
                        <div>
                            <label for="logo-input" class="cursor-pointer btn-secondary text-xs">Choisir un fichier</label>
                            <input id="logo-input" type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                            <p class="text-xs text-gray-400 mt-1">PNG, JPG — max 2 Mo</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label">Nom de l'entreprise *</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="Parfumerie Élite SARL" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Secteur d'activité</label>
                        <input type="text" name="industry" value="{{ old('industry') }}" placeholder="Ex : Parfumerie, Agroalimentaire, Textile..." class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Pays</label>
                        <input type="text" name="country" value="{{ old('country') }}" placeholder="Ex : Sénégal, Côte d'Ivoire..." class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Ville</label>
                        <input type="text" name="city" value="{{ old('city') }}" placeholder="Dakar" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Téléphone entreprise</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+221 33 000 00 00" class="form-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Zone industrielle de Mbao, Dakar" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email entreprise</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="contact@entreprise.sn" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Site web</label>
                        <input type="text" name="website" value="{{ old('website') }}" placeholder="www.entreprise.sn" class="form-input">
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full py-3 mt-2">Continuer →</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const el = document.getElementById('logo-preview');
            el.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-xl">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
