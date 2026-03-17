<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\DemandeImage;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Equipe;
use App\Models\Service;
use App\Models\Site;
use App\Models\User;
use App\Helpers\ServiceRedirectionHelper;
use App\Services\NotificationService;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $statutFilter = $request->get('statut'); // ex: brouillon, en_attente, accepte, impute, termine, cloture...

        $baseQuery = $user->demandes();
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
            'sad', 'seg', 'umt', 'umr', 'utgc', 'ubt', 'unsp'
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

        return view('demande.creer', compact('equipes', 'directions', 'departements', 'services', 'sites', 'approbateurs', 'structuredNatures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'date_debut_intervention' => 'nullable|date',
            'date_fin_intervention' => 'nullable|date|after_or_equal:date_debut_intervention',
        ]);

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

        // Traitement des images
        if ($request->hasFile('images')) {
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
                        'size' => $image->getSize()
                    ]);
                }
            }
        }

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

        return view('demande.edit', compact('demande', 'equipes', 'directions', 'departements', 'services', 'sites', 'approbateurs', 'structuredNatures'));
    }

    public function update(Request $request, Demande $demande)
    {
        if (!$this->canBeModified($demande)) {
            return redirect()->route('demande.index')->withErrors('Cette demande ne peut plus être modifiée.');
        }

        $request->validate([
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'date_debut_intervention' => 'nullable|date',
            'date_fin_intervention' => 'nullable|date|after_or_equal:date_debut_intervention',
        ]);

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

        // Nouvelles images
        if ($request->hasFile('images')) {
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
                        'size' => $image->getSize()
                    ]);
                }
            }
        }

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
            'superviseur', 'executant', 'sad', 'seg', 'umt', 'umr', 'utgc', 'ubt', 'unsp'
        ]);

        return view('demande.show', compact('demande'));
    }

    public function pdf(Demande $demande)
    {
        $demande->load([
            'user', 'approbateurN1', 'images', 'service', 'site',
            'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy',
            'terminatedBy', 'cloturedBy', 'equipes', 'chefequipe',
            'superviseur', 'executant', 'sad', 'seg', 'umt', 'umr', 'utgc', 'ubt', 'unsp'
        ]);

        // N1 : Approbateur
        $n1 = $demande->approvedBy ?: $demande->approbateurN1;

        // N2 : Chef de service (SAD ou SEG selon le cas)
        $n2 = $demande->sad ?: $demande->seg;

        // N3 : Chef d'unité (UMT / UBT / UNSP / UMR / UTGC)
        $n3 = null;
        $n3Name = null;

        foreach (['umt', 'ubt', 'unsp', 'umr', 'utgc'] as $unite) {
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
                $updateData = [
                    'statut' => 'impute',
                    // on mémorise l'unité cible (UAG, UPNS, UGBT, UMR, UTGC, ...)
                    'unite_code' => $uniteInfo['code'],
                ];

                $targetServiceRole = $targetServiceCode === 'SA' ? 'sad' : 'seg';

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
            $rejectServiceRole = $targetServiceCode === 'SA' ? 'sad' : 'seg';
            if ($rejectServiceRole === 'sad') {
                $updateData['sad_id'] = Auth::id();
            } elseif ($rejectServiceRole === 'seg') {
                $updateData['seg_id'] = Auth::id();
            }
            $demande->update($updateData);
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
}
