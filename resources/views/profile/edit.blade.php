@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Mon profil</h1>
        
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="matricule" class="label">Matricule</label>
                    <input type="text" id="matricule" value="{{ $user->matricule }}" disabled
                           class="input-senelec bg-gray-100 cursor-not-allowed">
                </div>

                <div>
                    <label for="email" class="label">Email</label>
                    <input type="email" id="email" value="{{ $user->email }}" disabled
                           class="input-senelec bg-gray-100 cursor-not-allowed">
                </div>

                <div>
                    <label for="prenom" class="label">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $user->prenom) }}"
                           class="input-senelec">
                    @error('prenom')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nom" class="label">Nom</label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $user->nom) }}"
                           class="input-senelec">
                    @error('nom')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telephone" class="label">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                           class="input-senelec">
                    @error('telephone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="poste" class="label">Poste</label>
                    <input type="text" id="poste" value="{{ $user->poste }}" disabled
                           class="input-senelec bg-gray-100 cursor-not-allowed">
                </div>
            </div>

            <div class="flex justify-end mt-6 gap-3">
                <a href="{{ route('dashboard') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
