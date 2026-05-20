<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $roleRoutes = [
            'admin'       => 'admin.dashboard',
            'dage'        => 'dage.dashboard',
            'sgb'         => 'sgb.dashboard',
            'sad'         => 'sad.dashboard',
            'seg'         => 'seg.dashboard',
            'approbateur' => 'approbateur.dashboard',
            'umt'         => 'umt.dashboard',
            'ubt'         => 'ubt.dashboard',
            'unsp'        => 'unsp.dashboard',
            'umr'         => 'umr.dashboard',
            'utgc'        => 'utgc.dashboard',
            'ual'         => 'ual.dashboard',
            'ucc'         => 'ucc.dashboard',
            'equipe'      => 'equipe.dashboard',
            'demandeur'   => 'demandeur.dashboard',
        ];

        $availableDashboards = [];
        foreach ($roleRoutes as $role => $route) {
            if ($user->hasRole($role) && \Illuminate\Support\Facades\Route::has($route)) {
                $availableDashboards[$role] = $route;
            }
        }

        // Si un rôle est explicitement demandé, on redirige dessus (ex: /dashboard?role=seg)
        $requestedRole = strtolower((string) request('role', ''));
        if ($requestedRole !== '' && isset($availableDashboards[$requestedRole])) {
            return redirect()->route($availableDashboards[$requestedRole]);
        }

        // Un seul rôle: redirection directe
        if (count($availableDashboards) === 1) {
            return redirect()->route(reset($availableDashboards));
        }

        // Plusieurs rôles: afficher le hub de choix
        return view('dashboard', compact('availableDashboards'));
    }
}
