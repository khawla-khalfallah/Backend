<?php

namespace App\Http\Controllers;

use App\Models\Apprenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApprenantController extends Controller
{
    // Tous les apprenants
    public function index()
    {
        return response()->json(Apprenant::with('user')->get());
    }

    // Créer un apprenant
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:apprenants,user_id',
            'niveau_etude' => 'nullable|string|max:50',
        ]);

        $apprenant = Apprenant::create($validated);
        return response()->json($apprenant, 201);
    }

    // Voir un apprenant
    public function show($id)
    {
        $apprenant = Apprenant::with(['user', 'formations', 'examens', 'certificats'])->findOrFail($id);
        return response()->json($apprenant);
    }

    // Modifier un apprenant
    public function update(Request $request, $id)
    {
        $apprenant = Apprenant::findOrFail($id);

        $validated = $request->validate([
            'niveau_etude' => 'nullable|string|max:50',
        ]);

        $apprenant->update($validated);
        return response()->json($apprenant);
    }

    // Supprimer un apprenant
    public function destroy($id)
    {
        Apprenant::destroy($id);
        return response()->json(['message' => 'Apprenant supprimé avec succès']);
    }
}
