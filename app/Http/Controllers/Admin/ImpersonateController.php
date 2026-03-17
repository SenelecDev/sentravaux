<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Simuler (se connecter en tant que) un autre utilisateur.
     */
    public function start(User $user)
    {
        // Empêcher de se simuler soi-même
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas simuler votre propre compte.');
        }

        // Sauvegarder l'ID admin dans la session
        session()->put('impersonator_id', auth()->id());

        // Se connecter en tant que l'utilisateur cible
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Vous simulez maintenant le compte de ' . $user->full_name);
    }

    /**
     * Arrêter la simulation et revenir au compte admin.
     */
    public function stop()
    {
        $impersonatorId = session()->get('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('dashboard');
        }

        // Restaurer le compte admin
        $admin = User::find($impersonatorId);
        
        if ($admin) {
            Auth::login($admin);
        }

        session()->forget('impersonator_id');

        return redirect()->route('admin.users.index')->with('success', 'Simulation terminée. Vous êtes reconnecté en tant qu\'admin.');
    }
}
