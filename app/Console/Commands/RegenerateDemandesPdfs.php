<?php

namespace App\Console\Commands;

use App\Models\Demande;
use App\Models\User;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RegenerateDemandesPdfs extends Command
{
    protected $signature = 'app:regenerate-pdfs
        {--statut=cloture : Statut à régénérer (ex: cloture, termine, en_cours, tous)}
        {--only-missing : Génère uniquement les PDFs manquants}
        {--limit=0 : Limite le nombre de demandes traitées}
        {--from-id=0 : Démarrer à partir d’un id}
        {--to-id=0 : S’arrêter à un id}';

    protected $description = 'Régénère les PDFs des demandes (storage/app/public/demandes)';

    public function handle(): int
    {
        $statut = strtolower((string) $this->option('statut'));
        $onlyMissing = (bool) $this->option('only-missing');
        $limit = (int) $this->option('limit');
        $fromId = (int) $this->option('from-id');
        $toId = (int) $this->option('to-id');

        $query = Demande::query();

        if ($statut !== 'tous' && $statut !== 'all') {
            $query->where('statut', $statut);
        }

        if ($fromId > 0) $query->where('id', '>=', $fromId);
        if ($toId > 0) $query->where('id', '<=', $toId);

        $query->orderBy('id');

        $countTotal = (clone $query)->count();
        if ($countTotal === 0) {
            $this->info('Aucune demande trouvée avec ces critères.');
            return self::SUCCESS;
        }

        $this->info("Demandes trouvées: {$countTotal}");
        if ($onlyMissing) $this->info('Mode: uniquement PDFs manquants');
        if ($limit > 0) $this->info("Limite: {$limit}");

        $bar = $this->output->createProgressBar($countTotal);
        $bar->start();

        $processed = 0;
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        $query->chunkById(50, function ($demandes) use (
            &$processed, &$generated, &$skipped, &$failed,
            $onlyMissing, $limit, $bar
        ) {
            foreach ($demandes as $demande) {
                if ($limit > 0 && $processed >= $limit) {
                    // stop chunking
                    return false;
                }

                $processed++;

                try {
                    $pdfPath = 'demandes/' . $demande->numero_demande . '.pdf';
                    if ($onlyMissing && Storage::exists('public/' . $pdfPath)) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    $output = $this->renderPdfForDemande($demande->id);
                    Storage::put('public/' . $pdfPath, $output);
                    $generated++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Regenerate PDFs: erreur', [
                        'demande_id' => $demande->id,
                        'numero' => $demande->numero_demande,
                        'message' => $e->getMessage(),
                    ]);
                } finally {
                    $bar->advance();
                }
            }

            return true;
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Traitement terminé.");
        $this->line("- traitées: {$processed}");
        $this->line("- générées: {$generated}");
        if ($onlyMissing) $this->line("- ignorées (déjà présentes): {$skipped}");
        $this->line("- échecs: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function renderPdfForDemande(int $demandeId): string
    {
        $demande = Demande::with([
            'user', 'approbateurN1', 'service', 'departement', 'direction', 'site',
            'approvedBy', 'rejectedBy', 'rejectedByN2', 'validatedBy',
            'terminatedBy', 'cloturedBy', 'equipes', 'chefequipe',
            'superviseur', 'executant', 'sad', 'seg', 'umt', 'umr', 'utgc', 'ubt', 'unsp',
        ])->findOrFail($demandeId);

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

        return $pdf->output();
    }

    private function convertImageToBase64(?string $path): ?string
    {
        if (!$path) return null;
        if (Storage::exists('public/' . $path)) {
            $fileContent = Storage::get('public/' . $path);
            $mimeType = Storage::mimeType('public/' . $path);
            return 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
        }
        return null;
    }
}

