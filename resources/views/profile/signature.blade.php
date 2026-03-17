@extends('layouts.app')

@section('title', 'Ma signature')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    previewSignature: '{{ $user->signature_url }}',
    previewStamp: '{{ $user->stamp_url }}',
    updatePreview(event, target) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => { this[target] = e.target.result };
        reader.readAsDataURL(file);
    }
}">
    <div class="card-senelec">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ma signature</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Téléversez votre signature manuscrite et, si nécessaire, votre cachet (tampon). Elles seront utilisées sur les PDF de demandes.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.signature.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 gap-8">
                <div class="space-y-4">
                    <label for="signature" class="label">Signature manuscrite</label>

                    <label for="signature"
                           class="group flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 cursor-pointer hover:border-senelec-purple/60 hover:bg-senelec-purple/5 transition-colors">
                        <div class="flex flex-col items-center justify-center text-center px-4">
                            <svg class="w-8 h-8 text-senelec-purple mb-2 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l-4-4m4 4l4-4" />
                            </svg>
                            <p class="text-sm font-medium text-gray-700">
                                Glissez-déposez une image ici<br>
                                <span class="text-xs text-gray-500">ou cliquez pour sélectionner un fichier</span>
                            </p>
                            <p class="mt-2 text-[11px] text-gray-400">
                                JPG, PNG, GIF, WebP · Max 4 Mo
                            </p>
                        </div>
                        <input type="file"
                               id="signature"
                               name="signature"
                               accept="image/*"
                               class="hidden"
                               @change="updatePreview($event, 'previewSignature')">
                    </label>

                    @error('signature')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-gray-600">Aperçu</p>
                        <div class="border rounded-xl bg-white px-4 py-3 flex items-center justify-center min-h-[96px]">
                            <template x-if="previewSignature">
                                <img :src="previewSignature" alt="Signature" class="max-h-20 object-contain">
                            </template>
                            <template x-if="!previewSignature">
                                <p class="text-xs text-gray-400">Aucune signature sélectionnée pour le moment.</p>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Cachet / tampon masqué pour le moment --}}
                {{-- 
                <div class="space-y-4">
                    <label for="stamp" class="label">Cachet / Tampon (optionnel)</label>

                    <label for="stamp"
                           class="group flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 cursor-pointer hover:border-senelec-purple/60 hover:bg-senelec-purple/5 transition-colors">
                        <div class="flex flex-col items-center justify-center text-center px-4">
                            <svg class="w-8 h-8 text-senelec-purple mb-2 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M9 11a3 3 0 016 0v1m-7 4h8" />
                            </svg>
                            <p class="text-sm font-medium text-gray-700">
                                Glissez-déposez le cachet ici<br>
                                <span class="text-xs text-gray-500">ou cliquez pour sélectionner un fichier</span>
                            </p>
                            <p class="mt-2 text-[11px] text-gray-400">
                                JPG, PNG, GIF, WebP · Max 4 Mo
                            </p>
                        </div>
                        <input type="file"
                               id="stamp"
                               name="stamp"
                               accept="image/*"
                               class="hidden"
                               @change="updatePreview($event, 'previewStamp')">
                    </label>

                    @error('stamp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-gray-600">Aperçu</p>
                        <div class="border rounded-xl bg-white px-4 py-3 flex items-center justify-center min-h-[96px]">
                            <template x-if="previewStamp">
                                <img :src="previewStamp" alt="Cachet" class="max-h-20 object-contain">
                            </template>
                            <template x-if="!previewStamp">
                                <p class="text-xs text-gray-400">Aucun cachet sélectionné pour le moment.</p>
                            </template>
                        </div>
                    </div>
                </div>
                --}}
            </div>

            <div class="flex justify-end mt-2 gap-3">
                <a href="{{ route('dashboard') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
