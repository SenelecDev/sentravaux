@extends('layouts.app')

@section('title', 'Nouvelle demande')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
<style>
    /* Select2 custom SENELEC theme */
    .select2-container--default .select2-selection--single {
        height: 44px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0 12px;
        background-color: #fff;
        display: flex;
        align-items: center;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
        right: 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px;
        color: #374151;
        padding-left: 0;
        padding-right: 28px;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
        font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        display: none;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2B1444;
        box-shadow: 0 0 0 3px rgba(43, 20, 68, 0.12);
        outline: none;
    }
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        margin-top: 4px;
        overflow: visible;
        background-color: #ffffff !important;
    }
    .select2-results {
        overflow-y: auto;
        max-height: 250px;
    }
    .select2-search--dropdown {
        padding: 8px !important;
        display: block !important;
    }
    .select2-container input[type="search"].select2-search__field,
    .select2-search--dropdown .select2-search__field {
        background-color: #fff !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
        color: #374151 !important;
        outline: none !important;
        box-shadow: none !important;
        -webkit-appearance: none !important;
        appearance: none !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .select2-container input[type="search"].select2-search__field:focus,
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #2B1444 !important;
        box-shadow: 0 0 0 2px rgba(43, 20, 68, 0.1) !important;
        outline: none !important;
    }
    .select2-results__option {
        padding: 8px 12px;
        font-size: 0.875rem;
    }
    .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #2B1444 !important;
        color: #fff;
    }
    .select2-results__option--selected {
        background-color: #f3e8ff !important;
        color: #2B1444;
    }
    .select2-results__group {
        padding: 8px 12px;
        font-weight: 700;
        font-size: 0.8rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .select2-container { width: 100% !important; }

    /* Image upload zone */
    .upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafafa;
    }
    .upload-zone:hover {
        border-color: #2B1444;
        background: #faf5ff;
    }
    /* Flatpickr custom theme */
    .flatpickr-input {
        background-color: #fff !important;
    }
    .flatpickr-day.selected, .flatpickr-day.selected:hover {
        background: #2B1444 !important;
        border-color: #2B1444 !important;
    }
    .flatpickr-day.today {
        border-color: #2B1444 !important;
    }
    .flatpickr-day:hover {
        background: #f3e8ff !important;
    }
    .flatpickr-months .flatpickr-month, .flatpickr-current-month .flatpickr-monthDropdown-months,
    span.flatpickr-weekday, .flatpickr-weekdays {
        background: #2B1444 !important;
        color: #fff !important;
    }
    .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
        color: #fff !important;
        fill: #fff !important;
    }
    .flatpickr-current-month input.cur-year {
        color: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Header --}}
    <div class="flex items-center justify-between bg-white rounded-xl shadow-sm px-6 py-4 border border-gray-100">
        <a href="{{ route('demande.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle demande de travaux</h1>
            <p class="mt-1 text-sm text-gray-500">
                <svg class="w-4 h-4 inline-block mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                Les champs marqués d'un <span class="text-red-500 font-bold">*</span> sont obligatoires.
            </p>
        </div>
        <a href="{{ route('demande.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90" style="background-color: #2B1444;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Mes demandes
        </a>
    </div>

    <form id="demande-form" action="{{ route('demande.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Ligne 1 : Date + Nature --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Travaux demandés le :</label>
                <input type="text" value="{{ now()->format('d/m/Y H:i') }}" class="w-full h-[44px] px-3 text-sm rounded-lg border border-gray-300 bg-gray-100 text-gray-500" readonly>
            </div>
            <div>
                <label for="nature" class="block text-sm font-semibold text-gray-700 mb-1">Nature des travaux <span class="text-red-500">*</span></label>
                <select name="nature" id="nature" class="select2-nature" required>
                    <option value="">Sélectionner la nature des travaux</option>
                    @foreach($structuredNatures as $service => $natures)
                        <optgroup label="{{ $service }}">
                            @foreach($natures as $nature)
                                <option value="{{ $nature }}" {{ old('nature') === $nature ? 'selected' : '' }}>{{ $nature }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <input type="hidden" name="unite_code" id="unite_code" value="{{ old('unite_code') }}">
                @error('nature') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section UMR : Période d'intervention (visible uniquement pour les natures UMR) --}}
        <div id="umr-period-section" class="hidden">
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-semibold text-teal-800">Pour les demandes UMR, veuillez indiquer la période d'intervention souhaitée.</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_debut_intervention" class="block text-sm font-semibold text-gray-700 mb-1">Date début intervention <span class="text-red-500">*</span></label>
                        <input type="text" name="date_debut_intervention" id="date_debut_intervention" value="{{ old('date_debut_intervention') }}" 
                               class="flatpickr-datetime w-full h-[44px] px-3 text-sm rounded-lg border border-gray-300 focus:border-[#2B1444] focus:ring-2 focus:ring-[#2B1444]/10 outline-none transition bg-white"
                               placeholder="Choisir la date et l'heure" readonly>
                        @error('date_debut_intervention') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="date_fin_intervention" class="block text-sm font-semibold text-gray-700 mb-1">Date fin intervention <span class="text-red-500">*</span></label>
                        <input type="text" name="date_fin_intervention" id="date_fin_intervention" value="{{ old('date_fin_intervention') }}" 
                               class="flatpickr-datetime w-full h-[44px] px-3 text-sm rounded-lg border border-gray-300 focus:border-[#2B1444] focus:ring-2 focus:ring-[#2B1444]/10 outline-none transition bg-white"
                               placeholder="Choisir la date et l'heure" readonly>
                        @error('date_fin_intervention') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Ligne 2 : 2 cartes côte à côte --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Carte gauche --}}
            <div class="bg-white rounded-xl border-2 border-blue-200 shadow-sm">
                <div class="p-6 space-y-5">
                    <div>
                        <label for="objet" class="block text-sm font-bold text-gray-800 mb-1">Objet <span class="text-red-500">*</span> :</label>
                        <input type="text" name="objet" id="objet" value="{{ old('objet') }}" 
                               class="w-full h-[44px] px-3 text-sm rounded-lg border border-gray-300 focus:border-[#2B1444] focus:ring-2 focus:ring-[#2B1444]/10 outline-none transition" 
                               required placeholder="Décrivez brièvement l'objet de la demande">
                        @error('objet') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="observation" class="block text-sm font-bold text-gray-800 mb-1">Observation <span class="text-red-500">*</span> :</label>
                        <textarea name="observation" id="observation" rows="4" 
                                  class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 focus:border-[#2B1444] focus:ring-2 focus:ring-[#2B1444]/10 outline-none transition resize-y" 
                                  required placeholder="Décrivez en détail les travaux demandés...">{{ old('observation') }}</textarea>
                        @error('observation') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="approbateur_n1_id" class="block text-sm font-bold text-gray-800 mb-1">Approbateur <span class="text-red-500">*</span> :</label>
                        <select name="approbateur_n1_id" id="approbateur_n1_id" class="select2-field" required>
                            <option value="">Sélectionner l'approbateur</option>
                            @foreach($approbateurs as $approbateur)
                                <option value="{{ $approbateur->id }}" {{ old('approbateur_n1_id') == $approbateur->id ? 'selected' : '' }}>
                                    {{ $approbateur->name }} {{ $approbateur->prenom ? '- ' . $approbateur->prenom : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('approbateur_n1_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Carte droite --}}
            <div class="bg-white rounded-xl border-2 border-blue-200 shadow-sm">
                <div class="p-6 space-y-5">
                    <div>
                        <label for="unite" class="block text-sm font-bold text-gray-800 mb-1">Unité <span class="text-red-500">*</span></label>
                        <select name="unite" id="unite" class="select2-field" required>
                            <option value="">Sélectionner une unité</option>
                            @if($directions->count())
                            <optgroup label="Directions">
                                @foreach($directions as $dir)
                                    <option value="direction-{{ $dir->id }}" {{ old('unite') === 'direction-'.$dir->id ? 'selected' : '' }}>{{ $dir->libelle }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                            @if($departements->count())
                            <optgroup label="Départements">
                                @foreach($departements as $dept)
                                    <option value="departement-{{ $dept->id }}" {{ old('unite') === 'departement-'.$dept->id ? 'selected' : '' }}>{{ $dept->libelle }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                            @if($services->count())
                            <optgroup label="Services">
                                @foreach($services as $service)
                                    <option value="service-{{ $service->id }}" {{ old('unite') === 'service-'.$service->id ? 'selected' : '' }}>{{ $service->libelle }}</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                        @error('unite') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="site_id" class="block text-sm font-bold text-gray-800 mb-1">Sites <span class="text-red-500">*</span></label>
                        <select name="site_id" id="site_id" class="select2-field" required>
                            <option value="">Sélectionner un site</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>{{ $site->libelle }}</option>
                            @endforeach
                        </select>
                        @error('site_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ files: [] }">
                        <label class="block text-sm font-bold text-gray-800 mb-1">Images (optionnel) :</label>
                        <label class="upload-zone block">
                            <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-sm text-gray-600 font-medium">Cliquez pour sélectionner des images</span>
                            <p class="text-xs text-gray-400 mt-1">Formats acceptés : JPG, PNG, GIF</p>
                            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/gif" class="hidden" @change="files = Array.from($event.target.files)">
                        </label>
                        <template x-if="files.length > 0">
                            <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="file in files" :key="file.name">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full" style="background-color: #f3e8ff; color: #2B1444;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span x-text="file.name"></span>
                                    </span>
                                </template>
                            </div>
                        </template>
                        @if(!empty($tempImages))
                            <div class="mt-3">
                                <p class="text-xs font-semibold text-gray-600 mb-2">Images conservees apres erreur :</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tempImages as $tempImage)
                                        <input type="hidden" name="temp_images[]" value="{{ $tempImage['id'] }}">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full border border-teal-200 bg-teal-50 text-teal-700">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ $tempImage['original_name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @error('images.*') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div id="client-date-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

        {{-- Actions --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit" name="action" value="save_draft" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5 shadow-sm" style="background-color: #27ae60;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Sauvegarder en brouillon
            </button>
            <button type="submit" name="action" value="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5 shadow-sm" style="background-color: #e67e22;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Soumettre la demande
            </button>
            <a href="{{ route('demande.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90 shadow-sm" style="background-color: #95a5a6;">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
<script>
$(document).ready(function() {
    // Select2 pour les champs simples
    $('.select2-field').each(function() {
        $(this).select2({
            language: 'fr',
            placeholder: $(this).find('option:first').text(),
            allowClear: false,
            width: '100%',
            minimumResultsForSearch: 0
        });
    });

    // Select2 pour la nature (avec optgroup)
    $('.select2-nature').select2({
        language: 'fr',
        placeholder: 'Sélectionner la nature des travaux',
        allowClear: false,
        width: '100%',
        minimumResultsForSearch: 0
    });

    // Flatpickr pour les dates d'intervention UMR
    var dateStartPicker = flatpickr('#date_debut_intervention', {
        locale: 'fr',
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd/m/Y H:i',
        time_24hr: true,
        defaultHour: 8,
        defaultMinute: 0,
        allowInput: false,
        onChange: function(selectedDates) {
            if (selectedDates.length && dateEndPicker) {
                dateEndPicker.set('minDate', selectedDates[0]);
            }
        }
    });

    var dateEndPicker = flatpickr('#date_fin_intervention', {
        locale: 'fr',
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd/m/Y H:i',
        time_24hr: true,
        defaultHour: 8,
        defaultMinute: 0,
        allowInput: false
    });

    // Mapping optgroup label → unite_code (ex: "SEG - Unité Matériel Roulant" → "UMR")
    var optgroupToUniteCode = @json(\App\Helpers\ServiceRedirectionHelper::getOptgroupToUniteCodeMap());
    var umrOptgroupLabel = @json('SEG - ' . config('services_structure.services_structure.SEG.unites.UMR.name', ''));

    function updateUniteCode() {
        var selected = $('#nature option:selected');
        var optgroupLabel = selected.closest('optgroup').attr('label') || '';
        var code = optgroupToUniteCode[optgroupLabel] || '';
        $('#unite_code').val(code);
        return { optgroupLabel: optgroupLabel, code: code };
    }

    function toggleUmrSection() {
        var info = updateUniteCode();
        if (info.optgroupLabel === umrOptgroupLabel && $('#nature').val()) {
            $('#umr-period-section').removeClass('hidden');
            $('#date_debut_intervention, #date_fin_intervention').prop('required', true);
        } else {
            $('#umr-period-section').addClass('hidden');
            $('#date_debut_intervention, #date_fin_intervention').prop('required', false);
            if (dateStartPicker) dateStartPicker.clear();
            if (dateEndPicker) {
                dateEndPicker.clear();
                dateEndPicker.set('minDate', null);
            }
            $('#client-date-error').addClass('hidden').text('');
        }
    }

    function validateUmrDatesBeforeSubmit() {
        var info = updateUniteCode();
        var isUmr = info.optgroupLabel === umrOptgroupLabel && $('#nature').val();
        if (!isUmr) {
            $('#client-date-error').addClass('hidden').text('');
            return true;
        }

        var dateDebut = $('#date_debut_intervention').val();
        var dateFin = $('#date_fin_intervention').val();
        if (!dateDebut || !dateFin) {
            $('#client-date-error').removeClass('hidden').text('Veuillez renseigner les deux dates d\'intervention pour une demande UMR.');
            return false;
        }

        var start = new Date(dateDebut.replace(' ', 'T'));
        var end = new Date(dateFin.replace(' ', 'T'));
        var now = new Date();

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
            $('#client-date-error').removeClass('hidden').text('Le format des dates d\'intervention est invalide.');
            return false;
        }

        if (start < now) {
            $('#client-date-error').removeClass('hidden').text('La date de début doit être supérieure ou égale à la date/heure actuelle.');
            return false;
        }

        if (end <= start) {
            $('#client-date-error').removeClass('hidden').text('La date de fin doit être strictement postérieure à la date de début.');
            return false;
        }

        $('#client-date-error').addClass('hidden').text('');
        return true;
    }

    $('#nature').on('change', toggleUmrSection);
    $('#demande-form').on('submit', function(e) {
        if (!validateUmrDatesBeforeSubmit()) {
            e.preventDefault();
        }
    });
    toggleUmrSection();
});
</script>
@endpush
