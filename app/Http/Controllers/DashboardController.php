<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $roleRoutes = [
            'admin'       => 'admin.dashboard',
            'dage'        => 'dage.dashboard',
            'sad'         => 'sad.dashboard',
            'seg'         => 'seg.dashboard',
            'approbateur' => 'approbateur.dashboard',
            'umt'         => 'umt.dashboard',
            'ubt'         => 'ubt.dashboard',
            'unsp'        => 'unsp.dashboard',
            'umr'         => 'umr.dashboard',
            'utgc'        => 'utgc.dashboard',
            'equipe'      => 'equipe.dashboard',
            'demandeur'   => 'demandeur.dashboard',
        ];

        foreach ($roleRoutes as $role => $route) {
            if ($user->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        // Fallback : vue générique si aucun rôle reconnu
        return view('dashboard');
    }
}
