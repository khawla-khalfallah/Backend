<?php
namespace App\Http\Controllers;

use App\Models\Inscrit;
use Illuminate\Http\Request;

class InscritController extends Controller
{
    
    public function index()
    {
        return Inscrit::with(['apprenant.user', 'formation'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apprenant_id' => 'required|exists:apprenants,user_id',
            'formation_id' => 'required|exists:formations,id',
        ]);

        // Vérifier si l'inscription existe déjà
        $exists = Inscrit::where('apprenant_id', $validated['apprenant_id'])
                         ->where('formation_id', $validated['formation_id'])
                         ->exists();

        if ($exists) {
            return response()->json(['message' => 'L\'apprenant est déjà inscrit à cette formation.'], 409);
        }

        $inscrit = Inscrit::create($validated);
        return response()->json($inscrit, 201);
    }

    public function show($id)
    {
        return Inscrit::with(['apprenant.user', 'formation'])->findOrFail($id);
    }

    public function destroy($id)
    {
        $inscrit = Inscrit::findOrFail($id);
        $inscrit->delete();

        return response()->json(['message' => 'Inscription supprimée.']);
    }
}
