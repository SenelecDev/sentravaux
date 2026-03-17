<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les permissions
        $permissions = [
            'user.manage',
            'role.manage',
            // Permissions demandes
            'demande.create',
            'demande.view',
            'demande.edit',
            'demande.delete',
            'demande.submit',
            // Permissions approbation
            'demande.approve',
            'demande.reject',
            // Permissions dispatch/imputation
            'demande.dispatch',
            'demande.imputer',
            // Permissions validation unité
            'demande.valider',
            'demande.terminer',
            'demande.cloturer',
            // Permissions équipe
            'equipe.manage',
            'equipe.executer',
            // Permissions stats
            'stats.view',
            'stats.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ==================== RÔLES ====================

        // Admin - Accès total
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Demandeur - Créer et voir ses demandes
        $demandeur = Role::firstOrCreate(['name' => 'demandeur']);
        $demandeur->givePermissionTo(['demande.create', 'demande.view', 'demande.edit', 'demande.delete', 'demande.submit']);

        // Approbateur (N1) - Approuver/rejeter les demandes
        $approbateur = Role::firstOrCreate(['name' => 'approbateur']);
        $approbateur->givePermissionTo(['demande.view', 'demande.approve', 'demande.reject']);

        // DAGE - Consultation statistiques globales
        $dage = Role::firstOrCreate(['name' => 'dage']);
        $dage->givePermissionTo(['demande.view', 'stats.view', 'stats.export']);

        // SAD - Service Administratif, dispatch vers unités SA
        $sad = Role::firstOrCreate(['name' => 'sad']);
        $sad->givePermissionTo(['demande.view', 'demande.dispatch', 'demande.imputer']);

        // SEG - Service Entretien Général, dispatch vers unités SEG + validation périodes
        $seg = Role::firstOrCreate(['name' => 'seg']);
        $seg->givePermissionTo(['demande.view', 'demande.dispatch', 'demande.imputer', 'demande.valider']);

        // UMT - Unité Maintenance Technique (SA/UAG)
        $umt = Role::firstOrCreate(['name' => 'umt']);
        $umt->givePermissionTo(['demande.view', 'demande.valider', 'demande.terminer', 'demande.cloturer']);

        // UBT - Unité Basse Tension (SA/UGBT)
        $ubt = Role::firstOrCreate(['name' => 'ubt']);
        $ubt->givePermissionTo(['demande.view', 'demande.valider', 'demande.terminer', 'demande.cloturer']);

        // UNSP - Unité Nettoyage et Salubrité Publique (SA/UPNS)
        $unsp = Role::firstOrCreate(['name' => 'unsp']);
        $unsp->givePermissionTo(['demande.view', 'demande.valider', 'demande.terminer', 'demande.cloturer']);

        // UMR - Unité Maintenance Réseaux (SEG/UMR)
        $umr = Role::firstOrCreate(['name' => 'umr']);
        $umr->givePermissionTo(['demande.view', 'demande.valider', 'demande.terminer', 'demande.cloturer']);

        // UTGC - Unité Travaux Génie Civil (SEG/UTGC)
        $utgc = Role::firstOrCreate(['name' => 'utgc']);
        $utgc->givePermissionTo(['demande.view', 'demande.valider', 'demande.terminer', 'demande.cloturer']);

        // Chef d'équipe
        $equipe = Role::firstOrCreate(['name' => 'equipe']);
        $equipe->givePermissionTo(['demande.view', 'equipe.manage', 'equipe.executer', 'demande.terminer']);

    }
}
