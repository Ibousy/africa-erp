@extends('layouts.public')
@section('title', 'Choisissez votre thème — AfricaERP')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="w-full max-w-xl">
        <!-- Logo -->
        <div class="flex items-center gap-3 justify-center mb-8">
            <div class="w-9 h-9 bg-orange-500 rounded-xl flex items-center justify-center font-bold text-white">A</div>
            <span class="font-bold text-gray-900 text-lg">{{ $tenant->company_name }}</span>
        </div>

        <!-- Progress -->
        <div class="mb-8">
            <div class="flex items-center mb-3">
                @foreach(['Compte', 'Entreprise', 'Modules', 'Thème'] as $i => $label)
                <div class="flex items-center {{ $i < 3 ? 'flex-1' : '' }}">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $i === 3 ? 'bg-orange-500 text-white' : 'bg-green-500 text-white' }}">
                        {{ $i < 3 ? '✓' : '4' }}
                    </div>
                    @if($i < 3)<div class="flex-1 h-px mx-2 {{ $i < 3 ? 'bg-green-400' : 'bg-gray-200' }}"></div>@endif
                </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-green-600 font-semibold">Compte</span>
                <span class="text-green-600 font-semibold">Entreprise</span>
                <span class="text-green-600 font-semibold">Modules</span>
                <span class="text-orange-600 font-semibold">Thème</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Choisissez votre thème</h1>
            <p class="text-gray-500 text-sm mb-8">La couleur principale de votre ERP. Vous pourrez la changer à tout moment dans les paramètres.</p>

            <form method="POST" action="{{ route('onboarding.step4') }}" id="theme-form">
                @csrf
                <input type="hidden" name="theme" id="theme-input" value="orange">

                <div class="grid grid-cols-1 gap-4 mb-8">
                    @foreach([
                        ['key' => 'orange', 'hex' => '#f97316', 'name' => 'Corail',  'desc' => 'Chaud et dynamique — parfait pour les industries créatives'],
                        ['key' => 'blue',   'hex' => '#3b82f6', 'name' => 'Océan',   'desc' => 'Calme et professionnel — idéal pour les industries techniques'],
                        ['key' => 'green',  'hex' => '#10b981', 'name' => 'Forêt',   'desc' => 'Naturel et équilibré — pour les industries agroalimentaires'],
                        ['key' => 'purple', 'hex' => '#8b5cf6', 'name' => 'Violet',  'desc' => 'Élégant et moderne — pour les cosmétiques et parfumeries'],
                        ['key' => 'red',    'hex' => '#ef4444', 'name' => 'Grenat',  'desc' => 'Fort et assertif — pour les industries métallurgiques'],
                    ] as $theme)
                    <button type="button" onclick="selectTheme('{{ $theme['key'] }}')"
                        id="theme-btn-{{ $theme['key'] }}"
                        class="theme-option flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-gray-300 transition-all text-left {{ $theme['key'] === 'orange' ? 'border-gray-800 bg-gray-50' : '' }}">
                        <div class="w-10 h-10 rounded-full shrink-0 shadow-md" style="background-color: {{ $theme['hex'] }}"></div>
                        <div>
                            <div class="font-semibold text-gray-900 text-sm">{{ $theme['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $theme['desc'] }}</div>
                        </div>
                        <div class="ml-auto">
                            <div id="check-{{ $theme['key'] }}" class="w-5 h-5 rounded-full border-2 flex items-center justify-center {{ $theme['key'] === 'orange' ? 'bg-gray-900 border-gray-900' : 'border-gray-300' }}">
                                <svg class="w-3 h-3 text-white {{ $theme['key'] === 'orange' ? '' : 'hidden' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </button>
                    @endforeach
                </div>

                <!-- Aperçu -->
                <div class="bg-gray-900 rounded-xl p-4 mb-8">
                    <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-700">
                        <div id="preview-logo" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-sm" style="background-color: #f97316">
                            {{ strtoupper(substr($tenant->company_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-white text-xs font-semibold">{{ $tenant->company_name }}</div>
                            <div class="text-gray-500 text-xs">Aperçu du menu</div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        @foreach(['Tableau de bord', 'Stock', 'Production'] as $item)
                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg {{ $loop->first ? 'preview-active text-white' : 'text-gray-400' }}" style="{{ $loop->first ? '' : '' }}">
                            <div class="w-3 h-3 rounded-sm bg-gray-600"></div>
                            <span class="text-xs">{{ $item }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('onboarding.step3') }}" class="btn-secondary px-6 py-3">← Retour</a>
                    <button type="submit" class="btn-primary flex-1 py-3">Terminer la configuration →</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const themes = {
    orange: '#f97316',
    blue:   '#3b82f6',
    green:  '#10b981',
    purple: '#8b5cf6',
    red:    '#ef4444',
};
const current = '{{ $tenant->theme }}';
if (current !== 'orange') selectTheme(current);

function selectTheme(key) {
    document.getElementById('theme-input').value = key;
    // Reset all
    document.querySelectorAll('.theme-option').forEach(el => {
        el.classList.remove('border-gray-800', 'bg-gray-50');
        el.classList.add('border-gray-100');
    });
    document.querySelectorAll('[id^="check-"]').forEach(el => {
        el.classList.remove('bg-gray-900', 'border-gray-900');
        el.classList.add('border-gray-300');
        el.querySelector('svg').classList.add('hidden');
    });
    // Activate selected
    const btn = document.getElementById('theme-btn-' + key);
    btn.classList.add('border-gray-800', 'bg-gray-50');
    btn.classList.remove('border-gray-100');
    const check = document.getElementById('check-' + key);
    check.classList.add('bg-gray-900', 'border-gray-900');
    check.classList.remove('border-gray-300');
    check.querySelector('svg').classList.remove('hidden');
    // Update preview
    document.getElementById('preview-logo').style.backgroundColor = themes[key];
    document.querySelectorAll('.preview-active').forEach(el => {
        el.style.backgroundColor = themes[key];
    });
}
</script>
@endpush
@endsection
