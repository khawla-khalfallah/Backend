<?php

namespace App\Http\Controllers;

use App\Models\Administrateur;
use Illuminate\Http\Request;

class AdministrateurController extends Controller
{
    // Lister tous les administrateurs
    public function index()
    {
        return Administrateur::with('user')->get();
    }

    // Créer un administrateur
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        return Administrateur::create([
            'user_id' => $request->user_id,
        ]);
    }

    // Afficher un administrateur par ID
    public function show($id)
    {
        return Administrateur::with('user')->findOrFail($id);
    }

    // Supprimer un administrateur
    public function destroy($id)
    {
        $admin = Administrateur::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Administrateur supprimé']);
    }
}
