<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SadController;
use App\Http\Controllers\SegController;
use App\Http\Controllers\UbtController;
use App\Http\Controllers\UmrController;
use App\Http\Controllers\UmtController;
use App\Http\Controllers\DageController;
use App\Http\Controllers\DageDashboardController;
use App\Http\Controllers\UnspController;
use App\Http\Controllers\UtgcController;
use App\Http\Controllers\SgbController;
use App\Http\Controllers\UalController;
use App\Http\Controllers\UccController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\DemandeurController;
use App\Http\Controllers\ApprobateurController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SiteController as AdminSiteController;
use App\Http\Controllers\Admin\DemandeController as AdminDemandeController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserSyncController as AdminUserSyncController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\ImpersonateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== AUTH ====================
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ==================== AUTHENTICATED ====================
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Documentation
    Route::view('/documentation', 'documentation')->name('documentation');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/signature', [ProfileController::class, 'editSignature'])->name('profile.signature');
    Route::post('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/data', [NotificationController::class, 'getNotifications'])->name('data');
        Route::get('/count', [NotificationController::class, 'getUnreadCount'])->name('count');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ==================== ARRÊTER SIMULATION ====================
    Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])->name('admin.impersonate.stop');

    // ==================== ADMIN ====================
    Route::prefix('admin')->middleware('role:admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', AdminUserController::class);
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{service}', [AdminServiceController::class, 'show'])->name('services.show');

        // ==================== DEMANDES ====================
        Route::get('/demandes', [AdminDemandeController::class, 'index'])->name('demandes.index');
        Route::get('/demandes/{demande}', [AdminDemandeController::class, 'show'])->name('demandes.show');

        // ==================== SITES ====================
        Route::get('/sites', [AdminSiteController::class, 'index'])->name('sites.index');
        Route::get('/sites/{site}', [AdminSiteController::class, 'show'])->name('sites.show');

        // ==================== RÔLES & PERMISSIONS ====================
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->name('roles.show');
        Route::put('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');

        // ==================== SIMULATION UTILISATEUR ====================
        Route::post('/impersonate/{user}', [ImpersonateController::class, 'start'])->name('impersonate.start');

        // ==================== JOURNAL D'ACTIVITÉS ====================
        Route::get('/activity-log', [AdminActivityLogController::class, 'index'])->name('activity-log.index');

        // ==================== SYNC ORACLE/LDAP ====================
        Route::get('/users-sync', [AdminUserSyncController::class, 'index'])->name('users.sync.index');
        Route::post('/users/{user}/sync', [AdminUserSyncController::class, 'syncUser'])->name('users.sync');
        Route::post('/users-sync-all', [AdminUserSyncController::class, 'syncAll'])->name('users.sync-all');
        Route::post('/users-sync-ldap', [AdminUserSyncController::class, 'syncLdap'])->name('users.sync-ldap');
        Route::post('/users-sync-photos', [AdminUserSyncController::class, 'syncPhotos'])->name('users.sync-photos');
        Route::get('/users-search-oracle', [AdminUserSyncController::class, 'searchOracle'])->name('users.search-oracle');
        Route::post('/users-import', [AdminUserSyncController::class, 'importUser'])->name('users.import');
        Route::post('/users-import-all', [AdminUserSyncController::class, 'importAll'])->name('users.import-all');
        Route::get('/users-sync-logs', [AdminUserSyncController::class, 'getLogs'])->name('users.sync-logs');
        Route::post('/users-sync-logs/clear', [AdminUserSyncController::class, 'clearLogs'])->name('users.sync-logs.clear');
    });

    // ==================== DEMANDEUR ====================
    Route::middleware('role:demandeur')->group(function () {
        Route::get('/demandeur/dashboard', [DemandeurController::class, 'dashboard'])->name('demandeur.dashboard');
    });

    // ==================== APPROBATEUR ====================
    Route::middleware('role:approbateur')->group(function () {
        Route::get('/approbateur/dashboard', [ApprobateurController::class, 'dashboard'])->name('approbateur.dashboard');
        Route::get('demandes/aapprouver', [ApprobateurController::class, 'demandes'])->name('demandes.aapprouver');
        Route::post('/demande/{id}/status', [ApprobateurController::class, 'updateStatus'])->name('demande.updateStatus');
        Route::get('demandes/approuvees', [ApprobateurController::class, 'demandesApprouvees'])->name('demande.demandes_approuvees');
        Route::get('demandes/rejetes', [ApprobateurController::class, 'demandesRejetees'])->name('demande.demandes_rejetes');
    });

    // ==================== DAGE ====================
    Route::middleware('role:dage')->group(function () {
        Route::get('/dage/dashboard', [DageDashboardController::class, 'dashboard'])->name('dage.dashboard');
        Route::get('/dage/export', [DageDashboardController::class, 'exportExcel'])->name('dage.export');
        Route::get('demandes/avalider', fn() => redirect()->route('dage.dashboard'))->name('demandes.avalider');
        Route::get('demandes/validees', fn() => redirect()->route('dage.dashboard'))->name('demande.demandes_validees');
        Route::get('demandes/rejetees', fn() => redirect()->route('dage.dashboard'))->name('demande.demandes_rejetees');
    });

    // ==================== SAD ====================
    Route::middleware('role:sad')->group(function () {
        Route::get('/sad/dashboard', [SadController::class, 'dashboard'])->name('sad.dashboard');
        Route::get('/sad/dashboard/export', [SadController::class, 'exportDashboard'])->name('sad.dashboard.export');
        Route::get('sad/demandes/approuvees', [SadController::class, 'demandes_approuvees'])->name('sad.demandes.approuvees');
        Route::get('sad/demandes/imputees', [SadController::class, 'demandes_imputees'])->name('sad.demandes.imputees');
        Route::get('sad/demandes/rejetees', [SadController::class, 'demandes_rejetees'])->name('sad.demandes.rejetees');
        Route::get('sad/demandes', [SadController::class, 'demandes'])->name('sad.demandes');
    });

    // ==================== SEG ====================
    Route::middleware('role:seg')->group(function () {
        Route::get('/seg/dashboard', [SegController::class, 'dashboard'])->name('seg.dashboard');
        Route::get('/seg/dashboard/export', [SegController::class, 'exportDashboard'])->name('seg.dashboard.export');
        Route::get('seg/demandes/approuvees', [SegController::class, 'demandes_approuvees'])->name('seg.demandes.approuvees');
        Route::get('seg/demandes/imputees', [SegController::class, 'demandes_imputees'])->name('seg.demandes.imputees');
        Route::get('seg/demandes/rejetees', [SegController::class, 'demandes_rejetees'])->name('seg.demandes.rejetees');
        Route::get('seg/demandes', [SegController::class, 'demandes'])->name('seg.demandes');
    });

    // ==================== SGB ====================
    Route::middleware('role:sgb')->group(function () {
        Route::get('/sgb/dashboard', [SgbController::class, 'dashboard'])->name('sgb.dashboard');
        Route::get('/sgb/dashboard/export', [SgbController::class, 'exportDashboard'])->name('sgb.dashboard.export');
        Route::get('sgb/demandes', [SgbController::class, 'demandes'])->name('sgb.demandes');
    });


    // ==================== DEMANDE CRUD (tous les rôles métiers qui manipulent les demandes) ====================
    Route::middleware('role:admin|demandeur|approbateur|sad|seg|sgb|umt|ubt|unsp|umr|utgc|ual|ucc|equipe')->group(function () {
        Route::resource('demande', DemandeController::class);
        Route::post('/demande/{demande}/submit', [DemandeController::class, 'submit'])->name('demande.submit');
    });

    // ==================== DISPATCH (sad/seg) ====================
    Route::middleware('role:admin|sad|seg|sgb')->group(function () {
        Route::get('/demandes/pending-dispatch', [DemandeController::class, 'pendingValidation'])->name('demandes.pending_dispatch');
        Route::post('/demande/{demande}/dispatch', [DemandeController::class, 'validateDemande'])->name('demande.dispatch');
        Route::post('/demande/{demande}/validate', [DemandeController::class, 'validateDemande'])->name('demande.validate');
    });

    // ==================== PDF DEMANDE (tous rôles métiers) ====================
    Route::middleware('role:admin|demandeur|approbateur|sad|seg|sgb|umt|ubt|unsp|umr|utgc|ual|ucc|equipe')->group(function () {
        Route::get('demande/{demande}/pdf', [DemandeController::class, 'pdf'])
            ->name('demande.pdf');
    });

    // ==================== DAGE STATS ====================
    Route::middleware('role:admin|dage')->group(function () {
        Route::get('/dage/statistics', [DageDashboardController::class, 'dashboard'])->name('dage.statistics');
        Route::resource('dage', DageController::class);
    });

    // ==================== APPROBATEUR + ADMIN ====================
    Route::middleware('role:admin|approbateur')->group(function () {
        Route::resource('approbateur', ApprobateurController::class);
        Route::resource('demandeur', DemandeurController::class);
    });

    // ==================== SAD + ADMIN ====================
    Route::middleware('role:admin|sad')->group(function () {
        Route::resource('sad', SadController::class);
    });

    // ==================== SEG + ADMIN ====================
    Route::middleware('role:admin|seg')->group(function () {
        Route::resource('seg', SegController::class);
        Route::post('seg/demandes/{demande}/valider-periode', [SegController::class, 'validerPeriode'])->name('seg.valider_periode');
        Route::post('seg/demandes/{demande}/modifier-periode', [SegController::class, 'modifierPeriode'])->name('seg.modifier_periode');
        Route::post('seg/demandes/{demande}/rejeter-periode-imputee', [SegController::class, 'rejeterPeriodeImputee'])->name('seg.rejeter_periode_imputee');
        Route::get('seg/demandes/periodes-attente', [SegController::class, 'periodesEnAttente'])->name('seg.periodes_attente');
    });

    // ==================== UMT ====================
    Route::middleware('role:admin|umt|equipe')->group(function () {
        Route::get('/umt/dashboard', [UmtController::class, 'dashboard'])->name('umt.dashboard');
        Route::get('umt/demandes', [UmtController::class, 'demandes'])->name('umt.demandes');
        Route::get('umt/demandes/recues', [UmtController::class, 'demandes'])->name('umt.demandes.recues');
        Route::get('umt/demandes/validees', [UmtController::class, 'demandesValidees'])->name('umt.demandes.validees');
        Route::get('umt/demandes/rejetees', [UmtController::class, 'demandesRejetees'])->name('umt.demandes.rejetees');
        Route::get('umt/demandes/terminees', [UmtController::class, 'demandesTerminees'])->name('umt.demandes.terminees');
        Route::get('umt/demandes/cloturees', [UmtController::class, 'demandesCloturees'])->name('umt.demandes.cloturees');
        Route::get('umt/demandes/debutees', [UmtController::class, 'demandesDebutees'])->name('umt.demandes.debutees');
        Route::resource('umt', UmtController::class);
    });

    // ==================== UBT ====================
    Route::middleware('role:admin|ubt')->group(function () {
        Route::get('ubt/dashboard', [UbtController::class, 'dashboard'])->name('ubt.dashboard');
        Route::get('ubt/demandes', [UbtController::class, 'demandes'])->name('ubt.demandes');
        Route::get('ubt/demandes/recues', [UbtController::class, 'demandes'])->name('ubt.demandes.recues');
        Route::get('ubt/demandes/validees', [UbtController::class, 'demandesValidees'])->name('ubt.demandes.validees');
        Route::get('ubt/demandes/terminees', [UbtController::class, 'demandesTerminees'])->name('ubt.demandes.terminees');
        Route::get('ubt/demandes/cloturees', [UbtController::class, 'demandesCloturees'])->name('ubt.demandes.cloturees');
        Route::get('ubt/demandes/debutees', [UbtController::class, 'demandesDebutees'])->name('ubt.demandes.debutees');
        Route::resource('ubt', UbtController::class);
    });

    // ==================== UNSP ====================
    Route::middleware('role:admin|unsp')->group(function () {
        Route::get('/unsp/dashboard', [UnspController::class, 'dashboard'])->name('unsp.dashboard');
        Route::get('unsp/demandes', [UnspController::class, 'demandes'])->name('unsp.demandes');
        Route::get('unsp/demandes/recues', [UnspController::class, 'demandes'])->name('unsp.demandes.recues');
        Route::get('unsp/demandes/validees', [UnspController::class, 'demandesValidees'])->name('unsp.demandes.validees');
        Route::get('unsp/demandes/terminees', [UnspController::class, 'demandesTerminees'])->name('unsp.demandes.terminees');
        Route::get('unsp/demandes/cloturees', [UnspController::class, 'demandesCloturees'])->name('unsp.demandes.cloturees');
        Route::get('unsp/demandes/debutees', [UnspController::class, 'demandesDebutees'])->name('unsp.demandes.debutees');
        Route::resource('unsp', UnspController::class);
    });

    // ==================== UMR ====================
    Route::middleware('role:admin|umr')->group(function () {
        Route::get('/umr/dashboard', [UmrController::class, 'dashboard'])->name('umr.dashboard');
        Route::get('umr/demandes', [UmrController::class, 'demandes'])->name('umr.demandes');
        Route::get('umr/demandes/recues', [UmrController::class, 'demandes'])->name('umr.demandes.recues');
        Route::get('umr/demandes/validees', [UmrController::class, 'demandesValidees'])->name('umr.demandes.validees');
        Route::get('umr/demandes/terminees', [UmrController::class, 'demandesTerminees'])->name('umr.demandes.terminees');
        Route::get('umr/demandes/cloturees', [UmrController::class, 'demandesCloturees'])->name('umr.demandes.cloturees');
        Route::get('umr/demandes/debutees', [UmrController::class, 'demandesDebutees'])->name('umr.demandes.debutees');
        Route::resource('umr', UmrController::class);
    });

    // ==================== UTGC ====================
    Route::middleware('role:admin|utgc')->group(function () {
        Route::get('/utgc/dashboard', [UtgcController::class, 'dashboard'])->name('utgc.dashboard');
        Route::get('utgc/demandes', [UtgcController::class, 'demandes'])->name('utgc.demandes');
        Route::get('utgc/demandes/recues', [UtgcController::class, 'demandes'])->name('utgc.demandes.recues');
        Route::get('utgc/demandes/validees', [UtgcController::class, 'demandesValidees'])->name('utgc.demandes.validees');
        Route::get('utgc/demandes/terminees', [UtgcController::class, 'demandesTerminees'])->name('utgc.demandes.terminees');
        Route::get('utgc/demandes/cloturees', [UtgcController::class, 'demandesCloturees'])->name('utgc.demandes.cloturees');
        Route::get('utgc/demandes/debutees', [UtgcController::class, 'demandesDebutees'])->name('utgc.demandes.debutees');
        Route::resource('utgc', UtgcController::class);
    });

    // ==================== UAL ====================
    Route::middleware('role:admin|ual')->group(function () {
        Route::get('/ual/dashboard', [UalController::class, 'dashboard'])->name('ual.dashboard');
        Route::get('ual/demandes', [UalController::class, 'demandes'])->name('ual.demandes');
        Route::get('ual/demandes/recues', [UalController::class, 'demandes'])->name('ual.demandes.recues');
        Route::get('ual/demandes/validees', [UalController::class, 'demandesValidees'])->name('ual.demandes.validees');
        Route::get('ual/demandes/terminees', [UalController::class, 'demandesTerminees'])->name('ual.demandes.terminees');
        Route::get('ual/demandes/cloturees', [UalController::class, 'demandesCloturees'])->name('ual.demandes.cloturees');
        Route::get('ual/demandes/debutees', [UalController::class, 'demandesDebutees'])->name('ual.demandes.debutees');
        Route::resource('ual', UalController::class);
    });

    // ==================== UCC ====================
    Route::middleware('role:admin|ucc')->group(function () {
        Route::get('/ucc/dashboard', [UccController::class, 'dashboard'])->name('ucc.dashboard');
        Route::get('ucc/demandes', [UccController::class, 'demandes'])->name('ucc.demandes');
        Route::get('ucc/demandes/recues', [UccController::class, 'demandes'])->name('ucc.demandes.recues');
        Route::get('ucc/demandes/validees', [UccController::class, 'demandesValidees'])->name('ucc.demandes.validees');
        Route::get('ucc/demandes/terminees', [UccController::class, 'demandesTerminees'])->name('ucc.demandes.terminees');
        Route::get('ucc/demandes/cloturees', [UccController::class, 'demandesCloturees'])->name('ucc.demandes.cloturees');
        Route::get('ucc/demandes/debutees', [UccController::class, 'demandesDebutees'])->name('ucc.demandes.debutees');
        Route::resource('ucc', UccController::class);
    });

    // ==================== CHEF D'EQUIPE ====================
    Route::middleware('role:equipe')->group(function () {
        Route::get('/equipe/dashboard', [EquipeController::class, 'dashboard'])->name('equipe.dashboard');
        Route::get('equipe/demandes', [EquipeController::class, 'demandes'])->name('equipe.demandes');
        Route::get('equipe/demandes/recues', [EquipeController::class, 'demandes'])->name('equipe.demandes.recues');
        Route::get('equipe/demandes/a-traiter', [EquipeController::class, 'demandes_a_traiter'])->name('equipe.demandes.a_traiter');
        Route::get('equipe/demandes/terminees', [EquipeController::class, 'demandes_terminees'])->name('equipe.demandes.terminees');
        Route::get('equipe/demandes/debutees', [EquipeController::class, 'demandes_debutees'])->name('equipe.demandes.debutees');
        Route::get('equipe/demandes/cloturees', [EquipeController::class, 'demandes_cloturees'])->name('equipe.demandes.cloturees');
    });

    Route::middleware('role:admin|equipe')->group(function () {
        Route::resource('equipe', EquipeController::class);
    });
});
