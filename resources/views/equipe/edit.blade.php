@extends('layouts.app')

@section('title', 'Modifier l\'équipe')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier l'équipe</h1>
            <p class="mt-1 text-gray-500">Mettez à jour le nom et la description de l'équipe.</p>
        </div>
        <a href="{{ route('equipe.index') }}" class="btn-secondary text-sm">
            Retour à la liste
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-4">
            <p class="font-semibold mb-1">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-senelec">
        <form action="{{ route('equipe.update', $equipe) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom de l'équipe <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nom" name="nom"
                       value="{{ old('nom', $equipe->nom) }}"
                       class="input-senelec w-full"
                       required>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea id="description" name="description" rows="3"
                          class="input-senelec w-full">{{ old('description', $equipe->description) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('equipe.index') }}" class="btn-secondary text-sm">
                    Annuler
                </a>
                <button type="submit" class="btn-senelec text-sm">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

