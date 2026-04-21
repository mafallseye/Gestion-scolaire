<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        // Récupère tous les utilisateurs sauf soi-même

          $users = \App\Models\User::where('id', '!=', auth()->id())->get();
    // dd($users); // <-- DECOMENTE CETTE LIGNE ET ACTUALISE
    return Inertia::render('Users/Index', ['users' => $users]);
    }

    // AJOUT DU $ DEVANT USER ICI :
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,teacher,student'
        ]);

        $user->update([
            'role' => $request->role
        ]);

        return back()->with('message', 'Rôle mis à jour avec succès');
    }
}
