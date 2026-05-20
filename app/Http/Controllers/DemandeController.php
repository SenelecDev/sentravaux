<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\DemandeImage;
use App\Models\DemandeRejection;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Equipe;
use App\Models\Service;
use App\Models\Site;
use App\Models\User;
use App\Helpers\ServiceRedirectionHelper;
use App\Services\NotificationService;
use Dompdf\Dompdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    private const TEMP_IMAGES_SESSION_KEY = 'demande_temp_images';

    public function index(Request $request)
    {
        $user = Auth::user();
        $statutFilter = $request->get('statut'); // ex: brouillon, en_attente, accepte, impute, termine, cloture...
        $teamTypeFilter = $request->get('team_type'); // interne | externe
        if (!in_array($teamTypeFilter, ['interne', 'externe'], true)) {
            $teamTypeFilter = null;
        }

        $baseQuery = $user->demandes();
        if ($teamTypeFilter) {
            $baseQuery->where('team_type', $teamTypeFilter);
        }
        $totalDemandes = $baseQuery->count();

        // Compteurs par statut pour les cards
        $statuts = ['brouillon','en_attente','accepte','impute','valide','en_cours','rejete','termine','cloture'];
        $statsParStatut = [];
        foreach ($statuts as $s) {
            $statsParStatut[$s] = (clone $baseQuery)->where('statut', $s)->count();
        }

        $query = $baseQuery->with([
            'approbateurN1', 'images', 'service', 'site', 'approvedBy',
            'rejectedBy', 'rejectedByN2', 'validatedBy', 'terminatedBy',
            'cloturedBy', 'equipes', 'chefequipe', 'superviseur', 'executant',
            'sad', 'seg', 'sgb', 'umt', 'umr', 'utgc', 'ubt', 'unsp', 'ual', 'ucc',
            'rejectionHistory'
        ]);

        if ($statutFilter) {
            $query->where('statut', $statutFilter);
        }

        $demandes = $query->orderBy('id', 'desc')->get();

        foreach ($demandes as $demande) {
            $demande->statut_label = ucfirst(str_replace('_', ' ', $demande->statut));
        }

        return view('demande.index', [
            'demandes'        => $demandes,
            'statutFilter'    => $statutFilter,
            'teamTypeFilter'  => $teamTypeFilter,
            'statsParStatut'  => $statsParStatut,
            'totalDemandes'   => $totalDemandes,
        ]);
    }

    public function create()
    {
        $equipes = Equipe::all();
        $directions = Direction::active()->orderBy('libelle')->get();
        $departements = Departement::active()->orderBy('libelle')->get();
        $services = Service::active()->orderBy('libelle')->get();
        $sites = Site::all();
        $approbateurs = User::role('approbateur')->orderBy('name')->get();

        $structuredNatures = ServiceRedirectionHelper::getAccessibleNatures(Auth::user());
        if (empty($structuredNatures)) {
            $structuredNatures = ServiceRedirectionHelper::getStructuredNatures();
        }

        $tempImages = $this->getTempImagesFromSession();

        return view('demande.creer', compact('equipes', 'directions', 'departements', 'services', 'sites', 'approbateurs', 'structuredNatures', 'tempImages'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            array_merge(
                [
                    'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                    'temp_images.*' => 'nullable|string',
                ],
                $this->getDateValidationRules($request)
            ),
            $this->getDateValidationMessages()
        );

        if ($validator->fails()) {
            $this->storeUploadedImagesTemporarily($request);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vd = $request->all();
        $vd['user_id'] = Auth::id();

        // Parse unite compound value (direction-1, departement-2, service-3)
        $this->parseUniteSelection($request, $vd);

        $action = $request->input('action', 'save_draft');
        $vd['statut'] = ($action === 'submit') ? 'en_attente' : 'brouillon';

        $demande = Demande::create($vd);

        if (!$demande) {
            return redirect()->back()->withErrors('Une erreur est survenue lors de la création de la demande.')->withInput();
        }

        $this->persistUploadedImagesToDemande($request, $demande);
        $this->persistTempImagesToDemande($demande, $request->input('temp_images', []));

        if ($action === 'submit') {
            $message = "La demande a été soumise avec succès et est en attente d'approbation.";
            NotificationService::demandeCreee($demande);
        } else {
            $message = "La demande a été sauvegardée en brouillon.";
        }

        return redirect()->route('demande.index')->with('success', $message);
    }

    public function submit(Demande $demande)
    {
        if ($demande->user_id !== Auth::id()) {
            return redirect()->back()->withErrors('Vous n\'avez pas l\'autorisation de soumettre cette demande.');
        }

        if (!in_array($demande->statut, ['brouillon', 'rejete'])) {
            return redirect()->back()->withErrors('Cette demande ne peut plus être soumise.');
        }

        if ($demande->statut === 'rejete') {
            $demande->motif = null;
            $demande->rejected_by = null;
            $demande->motif2 = null;
            $demande->rejected_by_n2 = null;
        }

        $demande->update(['statut' => 'en_attente']);
        NotificationService::demandeCreee($demande);

        return redirect()->route('demande.index')->with('success', 'Demande soumise avec succès.');
    }

    public function canBeModified(Demande $demande)
    {
        return in_array($demande->statut, ['brouillon', 'rejete']) && $demande->user_id === Auth::id();
    }

    public function edit(Demande $demande)
    {
        if (!$this->canBeModified($demande)) {
            return redirect()->route('demande.index')->withErrors('Cette demande ne peut plus être modifiée.');
        }

        $equipes = Equipe::all();
        $directions = Direction::active()->orderBy('libelle')->get();
        $departements = Departement::active()->orderBy('libelle')->get();
        $services = Service::active()->orderBy('libelle')->get();
        $sites = Site::all();
        $approbateurs = User::role('approbateur')->orderBy('name')->get();

        $structuredNatures = ServiceRedirectionHelper::getAccessibleNatures(Auth::user());
        if (empty($structuredNatures) || !is_array($structuredNatures)) {
            $structuredNatures = ServiceRedirectionHelper::getStructuredNatures();
        }

        $tempImages = $this->getTempImagesFromSession();

        return view('demande.edit', compact('demande', 'equipes', 'directions', 'departements', 'services', 'sites', 'approbateurs', 'structuredNatures', 'tempImages'));
    }

    public function update(Request $request, Demande $demande)
    {
        if (!$this->canBeModified($demande)) {
            return redirect()->route('demande.index')->withErrors('Cette demande ne peut plus être modifiée.');
        }

        $validator = Validator::make(
            array_merge(
                [
                    'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                    'temp_images.*' => 'nullable|string',
                ],
                $this->getDateValidationRules($request)
            ),
            $this->getDateValidationMessages()
        );

        if ($validator->fails()) {
            $this->storeUploadedImagesTemporarily($request);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $vd = $request->only(['objet', 'observation', 'nature', 'unite_code', 'site_id', 'approbateur_n1_id', 'date_debut_intervention', 'date_fin_intervention']);

        // Parse unite compound value (direction-1, departement-2, service-3)
        $this->parseUniteSelection($request, $vd);

        if ($demande->statut === 'rejete') {
            $vd['motif'] = null;
            $vd['rejected_by'] = null;
        }

        $action = $request->input('action', 'save_draft');
        $vd['statut'] = ($action === 'submit') ? 'en_attente' : 'brouillon';

        $demande->update($vd);

        // Suppression d'images
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images', []) as $imageId) {
                $image = $demande->images()->find($imageId);
                if ($image) {
                    if (file_exists($image->full_path)) {
                        unlink($image->full_path);
                    }
                    $image->delete();
                }
            }
        }

        $this->persistUploadedImagesToDemande($request, $demande);
        $this->persistTempImagesToDemande($demande, $request->input('temp_images', []));

        $message = ($action === 'submit') ? 'La demande a été mise à jour et soumise.' : 'La demande a été mise à jour en brouillon.';
        return redirect()->route('demande.index')->with('success', $message);
    }

    public function destroy(Demande $demande)
    {
        if (!$this->canBeModified($demande)) {
            return redirect()->route('demande.index')->withErrors('Cette demande ne peut plus être supprimée.');
        }
        $demande->delete();
        return redirect()->route('demande.index')->with('success', 'Demande supprimée avec succès.');
    }

    public function show(Demande $demande)
    {
        $demande->load([
            'user', 'approbateurN1', 'images', 'service', 'site',
            'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy',
            'terminatedBy', 'cloturedBy', 'equipes', 'chefequipe',
            'superviseur', 'executant', 'sad', 'seg', 'sgb', 'umt', 'umr', 'utgc', 'ubt', 'unsp', 'ual', 'ucc',
            'rejectionHistory.rejectedByUser'
        ]);

        return view('demande.show', compact('demande'));
    }

    public function pdf(Demande $demande)
    {
        $demande->load([
            'user', 'approbateurN1', 'images', 'service', 'departement', 'direction', 'site',
            'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy',
            'terminatedBy', 'cloturedBy', 'equipes', 'chefequipe',
            'superviseur', 'executant', 'sad', 'seg', 'sgb', 'umt', 'umr', 'utgc', 'ubt', 'unsp', 'ual', 'ucc'
        ]);

        // N1 : Approbateur
        $n1 = $demande->approvedBy ?: $demande->approbateurN1;

        // N2 : Chef de service (SAD ou SEG selon le cas)
        $n2 = $demande->sad ?: ($demande->seg ?: $demande->sgb);

        // N3 : Chef d'unité (UMT / UBT / UNSP / UMR / UTGC)
        $n3 = null;
        $n3Name = null;

        foreach (['umt', 'ubt', 'unsp', 'umr', 'utgc', 'ual', 'ucc'] as $unite) {
            $field = $unite . '_id';
            if ($demande->$field) {
                $n3 = User::find($demande->$field);
                $n3Name = $demande->$unite ? $demande->$unite->name : null;
                break;
            }
        }

        $signatureN1 = $n1 && $n1->signature ? $this->convertImageToBase64($n1->signature) : null;
        $stampN1 = $n1 && $n1->stamp ? $this->convertImageToBase64($n1->stamp) : null;
        $signatureN2 = $n2 && $n2->signature ? $this->convertImageToBase64($n2->signature) : null;
        $stampN2 = $n2 && $n2->stamp ? $this->convertImageToBase64($n2->stamp) : null;
        $signatureN3 = $n3 && $n3->signature ? $this->convertImageToBase64($n3->signature) : null;
        $stampN3 = $n3 && $n3->stamp ? $this->convertImageToBase64($n3->stamp) : null;

        $pdf = new Dompdf();
        $pdf->loadHtml(view('pdf.demande', compact(
            'demande',
            'signatureN1',
            'stampN1',
            'signatureN2',
            'stampN2',
            'signatureN3',
            'stampN3',
            'n3Name'
        ))->render());
        $pdf->setPaper('A4', 'paysage');
        $pdf->render();

        $output = $pdf->output();
        $pdfPath = 'demandes/' . $demande->numero_demande . '.pdf';
        Storage::put('public/' . $pdfPath, $output);

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $demande->numero_demande . '.pdf"',
        ]);
    }

    private function convertImageToBase64($path)
    {
        if (Storage::exists('public/' . $path)) {
            $fileContent = Storage::get('public/' . $path);
            $mimeType = Storage::mimeType('public/' . $path);
            return 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
        }
        return null;
    }

    /**
     * Dispatcher une demande approuvée vers une unité ou la rejeter (par un manager de service)
     */
    public function validateDemande(Request $request, Demande $demande)
    {
        // Rôle qui a le droit de traiter la demande dans son état actuel
        $originalServiceRole = ServiceRedirectionHelper::getServiceRoleForNature($demande->nature, $demande->unite_code);
        if (!Auth::user()->hasRole($originalServiceRole)) {
            return redirect()->back()->withErrors('Vous n\'avez pas l\'autorisation de traiter cette demande.');
        }

        $action = $request->input('action');

        // Surcharge éventuelle du service + unité cible (corrigés par SA/SEG)
        $overrideFull = $request->input('unite_full_override');
        $targetServiceCode = null;
        $targetUniteCode = null;

        if ($overrideFull) {
            [$targetServiceCode, $targetUniteCode] = array_pad(explode(':', $overrideFull, 2), 2, null);
        }

        if (!$targetServiceCode || !$targetUniteCode) {
            $originalUniteInfo = ServiceRedirectionHelper::getUniteFromNature($demande->nature, $demande->unite_code);
            if ($originalUniteInfo) {
                $targetServiceCode = $originalUniteInfo['service'];
                $targetUniteCode = $originalUniteInfo['code'];
            }
        }

        if ($action === 'dispatch') {
            $uniteInfo = ServiceRedirectionHelper::getUniteFromNature($demande->nature, $targetUniteCode);
            $uniteUser = ServiceRedirectionHelper::getUniteUserForNature($demande->nature, $targetUniteCode);

            if ($uniteInfo) {
                // Permettre le choix explicite du chef d'unité au dispatch
                if ($request->filled('unite_user_id')) {
                    $selectedUser = User::find($request->input('unite_user_id'));
                    $requiredRole = ServiceRedirectionHelper::getRoleFromUnite($uniteInfo['code']);
                    if (!$selectedUser || !$requiredRole || !$selectedUser->hasRole($requiredRole)) {
                        return redirect()->back()->withErrors('Le chef d\'unité sélectionné ne correspond pas à l\'unité cible.');
                    }
                    $uniteUser = $selectedUser;
                }

                $updateData = [
                    'statut' => 'impute',
                    // on mémorise l'unité cible (UAG, UPNS, UGBT, UMR, UTGC, ...)
                    'unite_code' => $uniteInfo['code'],
                ];

                $targetServiceRole = match ($targetServiceCode) {
                    'SA' => 'sad',
                    'SEG' => 'seg',
                    'SGB' => 'sgb',
                    default => null,
                };

                if ($targetServiceRole === 'sad') {
                    $updateData['sad_id'] = Auth::id();
                    if ($uniteUser) {
                        switch ($uniteInfo['code']) {
                            case 'UAG': $updateData['umt_id'] = $uniteUser->id; break;
                            case 'UGBT': $updateData['ubt_id'] = $uniteUser->id; break;
                            case 'UPNS': $updateData['unsp_id'] = $uniteUser->id; break;
                        }
                    }
                } elseif ($targetServiceRole === 'seg') {
                    $updateData['seg_id'] = Auth::id();
                    if ($uniteUser) {
                        switch ($uniteInfo['code']) {
                            case 'UTGC': $updateData['utgc_id'] = $uniteUser->id; break;
                            case 'UMR':  $updateData['umr_id']  = $uniteUser->id; break;
                        }
                    }

                    // Si SAD renseigne une période lors de l'imputation vers SEG (UMR/UTGC),
                    // on considère désormais que cette période est déjà validée par SEG.
                    if ($request->has('date_debut_intervention') && $request->has('date_fin_intervention')) {
                        $updateData['date_debut_intervention'] = $request->input('date_debut_intervention');
                        $updateData['date_fin_intervention'] = $request->input('date_fin_intervention');
                        $updateData['periode_validee_seg'] = true;
                    }
                } elseif ($targetServiceRole === 'sgb') {
                    $updateData['sgb_id'] = Auth::id();
                    if ($uniteUser) {
                        switch ($uniteInfo['code']) {
                            case 'UAL': $updateData['ual_id'] = $uniteUser->id; break;
                            case 'UCC': $updateData['ucc_id'] = $uniteUser->id; break;
                        }
                    }
                }

                $demande->update($updateData);
                NotificationService::demandeImputee($demande, Auth::user(), $uniteInfo['name']);
                return redirect()->back()->with('success', 'Demande imputée vers l\'unité ' . $uniteInfo['name']);
            } else {
                return redirect()->back()->withErrors('Aucun utilisateur trouvé pour cette unité.');
            }
        } elseif ($action === 'reject') {
            $updateData = [
                'statut' => 'rejete',
                'motif2' => $request->input('rejection_reason'),
                'rejected_by_n2' => Auth::id(),
            ];
            $rejectServiceRole = match ($targetServiceCode) {
                'SA' => 'sad',
                'SEG' => 'seg',
                'SGB' => 'sgb',
                default => null,
            };
            if ($rejectServiceRole === 'sad') {
                $updateData['sad_id'] = Auth::id();
            } elseif ($rejectServiceRole === 'seg') {
                $updateData['seg_id'] = Auth::id();
            } elseif ($rejectServiceRole === 'sgb') {
                $updateData['sgb_id'] = Auth::id();
            }
            $demande->update($updateData);
            DemandeRejection::create([
                'demande_id' => $demande->id,
                'rejected_by' => Auth::id(),
                'rejection_level' => 'n2',
                'reason' => (string) $request->input('rejection_reason'),
                'rejected_at' => now(),
            ]);
            NotificationService::demandeRejeteeN2($demande->fresh(), Auth::user(), $request->input('rejection_reason'));
            return redirect()->back()->with('success', 'Demande rejetée et renvoyée vers le demandeur.');
        }

        return redirect()->back()->withErrors('Action non reconnue.');
    }

    public function pendingValidation()
    {
        $userRoles = Auth::user()->roles->pluck('name')->toArray();
        $demandes = collect();

        if (in_array('sad', $userRoles)) {
            $saNatures = $this->getSANatures();
            $saDemandes = Demande::whereIn('statut', ['accepte', 'approuve'])
                ->where(function ($query) use ($saNatures) {
                    $query->whereIn('nature', $saNatures)
                        ->where(function ($q) {
                            $q->where('nature', '!=', 'Autres demandes')
                                ->orWhereIn('unite_code', ['UAG', 'UPNS', 'UGBT']);
                        });
                })
                ->whereNull('sad_id')
                ->with(['user', 'site'])
                ->orderBy('created_at', 'desc')
                ->get();
            $demandes = $demandes->merge($saDemandes);
        }

        if (in_array('seg', $userRoles)) {
            $segNatures = $this->getSEGNatures();
            $segDemandes = Demande::whereIn('statut', ['accepte', 'approuve'])
                ->where(function ($query) use ($segNatures) {
                    $query->whereIn('nature', $segNatures)
                        ->where(function ($q) {
                            $q->where('nature', '!=', 'Autres demandes')
                                ->orWhereIn('unite_code', ['UTGC', 'UMR']);
                        });
                })
                ->whereNull('seg_id')
                ->with(['user', 'site'])
                ->orderBy('created_at', 'desc')
                ->get();
            $demandes = $demandes->merge($segDemandes);
        }

        if (in_array('sgb', $userRoles)) {
            $sgbNatures = $this->getSGBNatures();
            $sgbDemandes = Demande::whereIn('statut', ['accepte', 'approuve'])
                ->whereIn('nature', $sgbNatures)
                ->where(function ($query) {
                    $query->where('nature', '!=', 'Autres demandes')
                        ->orWhereIn('unite_code', ['UAL', 'UCC']);
                })
                ->whereNull('sgb_id')
                ->with(['user', 'site'])
                ->orderBy('created_at', 'desc')
                ->get();
            $demandes = $demandes->merge($sgbDemandes);
        }

        return view('demande.pending_dispatch', compact('demandes'));
    }

    /**
     * Parse the compound unite selection value (e.g. "direction-3", "departement-5", "service-12")
     * and set the appropriate ID columns, clearing the others.
     */
    private function parseUniteSelection(Request $request, array &$vd): void
    {
        $unite = $request->input('unite');
        $vd['service_id'] = null;
        $vd['direction_id'] = null;
        $vd['departement_id'] = null;

        if ($unite && str_contains($unite, '-')) {
            [$type, $id] = explode('-', $unite, 2);
            match ($type) {
                'direction' => $vd['direction_id'] = (int) $id,
                'departement' => $vd['departement_id'] = (int) $id,
                'service' => $vd['service_id'] = (int) $id,
                default => null,
            };
        }
    }

    private function getDateValidationRules(Request $request): array
    {
        $isUmr = strtoupper((string) $request->input('unite_code')) === 'UMR';
        $isSubmitAction = $request->input('action') === 'submit';
        $requireUmrPeriod = $isUmr && $isSubmitAction;

        $dateDebutRules = [
            'nullable',
            'date_format:Y-m-d H:i',
            'required_with:date_fin_intervention',
        ];
        $dateFinRules = [
            'nullable',
            'date_format:Y-m-d H:i',
            'required_with:date_debut_intervention',
            'after:date_debut_intervention',
        ];

        if ($requireUmrPeriod) {
            $dateDebutRules[] = 'required';
            $dateDebutRules[] = 'after_or_equal:now';
            $dateFinRules[] = 'required';
        }

        return [
            'date_debut_intervention' => $dateDebutRules,
            'date_fin_intervention' => $dateFinRules,
        ];
    }

    private function getDateValidationMessages(): array
    {
        return [
            'date_debut_intervention.required' => 'La date de début est obligatoire pour une demande UMR soumise.',
            'date_fin_intervention.required' => 'La date de fin est obligatoire pour une demande UMR soumise.',
            'date_debut_intervention.required_with' => 'Veuillez renseigner la date de début si une date de fin est saisie.',
            'date_fin_intervention.required_with' => 'Veuillez renseigner la date de fin si une date de début est saisie.',
            'date_debut_intervention.date_format' => 'La date de début doit respecter le format YYYY-MM-DD HH:mm.',
            'date_debut_intervention.after_or_equal' => 'La date de début doit être supérieure ou égale à la date/heure actuelle.',
            'date_fin_intervention.date_format' => 'La date de fin doit respecter le format YYYY-MM-DD HH:mm.',
            'date_fin_intervention.after' => 'La date de fin doit être strictement postérieure à la date de début.',
        ];
    }

    private function getSANatures()
    {
        $structure = config('services_structure.services_structure');
        $natures = [];
        foreach ($structure['SA']['unites'] as $unite) {
            $natures = array_merge($natures, array_keys($unite['natures']));
        }
        return $natures;
    }

    private function getSEGNatures()
    {
        $structure = config('services_structure.services_structure');
        $natures = [];
        foreach ($structure['SEG']['unites'] as $unite) {
            $natures = array_merge($natures, array_keys($unite['natures']));
        }
        return $natures;
    }

    private function getSGBNatures()
    {
        $structure = config('services_structure.services_structure');
        $natures = [];
        foreach (($structure['SGB']['unites'] ?? []) as $unite) {
            $natures = array_merge($natures, array_keys($unite['natures']));
        }
        return $natures;
    }

    private function getTempImagesFromSession(): array
    {
        return array_values(session(self::TEMP_IMAGES_SESSION_KEY, []));
    }

    private function storeUploadedImagesTemporarily(Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $existing = session(self::TEMP_IMAGES_SESSION_KEY, []);

        foreach ($request->file('images', []) as $image) {
            if (!$image || !$image->isValid()) {
                continue;
            }

            $id = (string) Str::uuid();
            $extension = $image->getClientOriginalExtension() ?: 'jpg';
            $filename = $id . '.' . $extension;
            $path = 'temp/demandes/' . Auth::id();
            $storedPath = $image->storeAs('public/' . $path, $filename);

            if (!$storedPath) {
                continue;
            }

            $existing[$id] = [
                'id' => $id,
                'filename' => $filename,
                'original_name' => $image->getClientOriginalName(),
                'path' => $path . '/' . $filename,
                'mime_type' => $image->getClientMimeType(),
                'size' => $image->getSize(),
            ];
        }

        session([self::TEMP_IMAGES_SESSION_KEY => $existing]);
    }

    private function persistUploadedImagesToDemande(Request $request, Demande $demande): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $image) {
            if ($image->isValid()) {
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = 'demandes/' . $demande->id;
                $image->storeAs('public/' . $path, $filename);
                $demande->images()->create([
                    'filename' => $filename,
                    'original_name' => $image->getClientOriginalName(),
                    'path' => $path . '/' . $filename,
                    'mime_type' => $image->getClientMimeType(),
                    'size' => $image->getSize(),
                ]);
            }
        }
    }

    private function persistTempImagesToDemande(Demande $demande, array $tempImageIds): void
    {
        if (empty($tempImageIds)) {
            return;
        }

        $allTempImages = session(self::TEMP_IMAGES_SESSION_KEY, []);
        $selectedTempImages = Arr::only($allTempImages, array_unique($tempImageIds));

        $movedIds = [];

        foreach ($selectedTempImages as $tempId => $tempImage) {
            $sourcePath = 'public/' . $tempImage['path'];
            if (!Storage::exists($sourcePath)) {
                continue;
            }

            $filename = time() . '_' . uniqid() . '.' . pathinfo((string) $tempImage['filename'], PATHINFO_EXTENSION);
            $targetPath = 'demandes/' . $demande->id . '/' . $filename;

            Storage::move($sourcePath, 'public/' . $targetPath);

            $demande->images()->create([
                'filename' => $filename,
                'original_name' => $tempImage['original_name'],
                'path' => $targetPath,
                'mime_type' => $tempImage['mime_type'] ?? null,
                'size' => $tempImage['size'] ?? null,
            ]);

            $movedIds[$tempId] = true;
        }

        $remaining = array_diff_key($allTempImages, $movedIds);
        session([self::TEMP_IMAGES_SESSION_KEY => $remaining]);
    }
}
