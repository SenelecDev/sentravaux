<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);
        $user->update(['name' => trim($validated['prenom'] . ' ' . $validated['nom'])]);

        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour avec succès.');
    }

    public function editSignature()
    {
        return view('profile.signature', [
            'user' => auth()->user(),
        ]);
    }

    public function updateSignature(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'stamp'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $data = [];

        if ($request->hasFile('signature')) {
            if ($user->signature) {
                Storage::delete('public/' . $user->signature);
            }

            $path = $request->file('signature')->storeAs(
                'public/signatures/' . $user->id,
                'signature.' . $request->file('signature')->getClientOriginalExtension()
            );

            $data['signature'] = str_replace('public/', '', $path);
        }

        if ($request->hasFile('stamp')) {
            if ($user->stamp) {
                Storage::delete('public/' . $user->stamp);
            }

            $path = $request->file('stamp')->storeAs(
                'public/signatures/' . $user->id,
                'stamp.' . $request->file('stamp')->getClientOriginalExtension()
            );

            $data['stamp'] = str_replace('public/', '', $path);
        }

        if (!empty($data)) {
            $user->update($data);
        }

        return redirect()
            ->route('profile.signature')
            ->with('success', 'Signature mise à jour avec succès.');
    }
}
