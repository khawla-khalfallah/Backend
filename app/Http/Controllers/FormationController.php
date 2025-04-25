<?php
namespace App\Http\Controllers;

use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FormationController extends Controller
{
    public function index()
    {
        return Formation::with(['formateur', 'apprenants', 'examens', 'seances', 'videos', 'pdfs','formateur.user'])->get();
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:100',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'formateur_id' => 'required|exists:formateurs,user_id',
        ]);

        $formation = Formation::create($validated);
        return response()->json($formation, 201);
    }

    public function show($id)
    {
        $formation = Formation::with(['formateur.user', 'apprenants', 'examens', 'seances', 'videos', 'pdfs'])->findOrFail($id);
        return response()->json($formation);
    }

    public function update(Request $request, $id)
    {
        $formation = Formation::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $formation->update($validated);
        return response()->json($formation);
    }

    public function destroy($id)
    {
        Formation::destroy($id);
        return response()->json(['message' => 'Formation supprimée avec succès.']);
    }    
    




    

    // Renvoie les apprenants inscrits à une formation donnée
    public function getApprenants($id)
    {
        $formation = Formation::with('apprenants.user')->find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }

        return response()->json($formation->apprenants);
    }
    public function chercherParTitre(Request $request)
    {
        $titre = $request->query('titre');

        $formation = Formation::where('titre', 'LIKE', "%$titre%")->first();

        if ($formation) {
            return response()->json($formation);
        } else {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }
    }
}
