<?php

namespace App\Http\Controllers;

use App\Models\Recruteur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecruteurController extends Controller
{
    // Afficher tous les recruteurs
    public function index()
    {
        return response()->json(Recruteur::with('user')->get());
    }

    // Créer un recruteur
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:recruteurs,user_id',
            'entreprise' => 'nullable|string|max:100',
        ]);

        $recruteur = Recruteur::create($validated);
        return response()->json($recruteur, 201);
    }

    // Afficher un recruteur spécifique
    public function show($id)
    {
        $recruteur = Recruteur::with('user')->findOrFail($id);
        return response()->json($recruteur);
    }

    // Mettre à jour un recruteur
    public function update(Request $request, $id)
    {
        $recruteur = Recruteur::findOrFail($id);

        $validated = $request->validate([
            'entreprise' => 'nullable|string|max:100',
        ]);

        $recruteur->update($validated);
        return response()->json($recruteur);
    }

    // Supprimer un recruteur
    public function destroy($id)
    {
        Recruteur::destroy($id);
        return response()->json(['message' => 'Recruteur supprimé avec succès']);
    }
}
