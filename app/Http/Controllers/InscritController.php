<?php
namespace App\Http\Controllers;

use App\Models\Inscrit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Apprenant;
use App\Models\Formation; 

class InscritController extends Controller
{
    public function index()
    {
        return Inscrit::with(['apprenant.user', 'formation'])->get()
            ->map(function ($inscription) {
                return [
                    'id_inscrit' => $inscription->id_inscrit,
                    'apprenant_id' => $inscription->apprenant_id,
                    'formation_id' => $inscription->formation_id,
                    'apprenant' => $inscription->apprenant,
                    'formation' => $inscription->formation,
                    'date_inscription' => $inscription->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }
    public function myInscriptions()
    {
        $user = Auth::user();

        if (!$user || !$user->apprenant) {
            return response()->json(['message' => 'Accès réservé aux apprenants.'], 403);
        }

        $apprenantId = $user->id;

        $inscriptions = Inscrit::with('formation')
            ->where('apprenant_id', $apprenantId)
            ->get()
            ->map(function ($inscription) {
                return [
                    'formation' => $inscription->formation,
                    'date_inscription' => $inscription->created_at->format('Y-m-d H:i:s')
                ];
            });

        return response()->json($inscriptions);
    }

    public function inscrireMoi(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->apprenant) {
            return response()->json(['message' => 'Accès réservé aux apprenants.'], 403);
        }

        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
        ]);

        $apprenantId = $user->id; // user_id est la PK dans apprenants

         // Vérifier si la formation existe et n’est pas expirée
    $formation = Formation::findOrFail($validated['formation_id']);
    if ($formation->date_fin < now()) {
        return response()->json(['message' => 'Impossible de s\'inscrire, la formation est déjà expirée.'], 400);
    }


        // Vérifier si déjà inscrit
        $exists = Inscrit::where('apprenant_id', $apprenantId)
            ->where('formation_id', $validated['formation_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Vous êtes déjà inscrit à cette formation.'], 409);
        }

        $inscrit = Inscrit::create([
            'apprenant_id' => $apprenantId,
            'formation_id' => $validated['formation_id'],
        ]);

        return response()->json([
            'message' => 'Inscription réussie !',
            'data' => $inscrit->load('formation')
        ], 201);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apprenant_id' => 'required|exists:apprenants,user_id',
            'formation_id' => 'required|exists:formations,id',
        ]);
        // Vérifier si la formation n’est pas expirée
    $formation = Formation::findOrFail($validated['formation_id']);
    if ($formation->date_fin < now()) {
        return response()->json(['message' => 'Impossible d\'inscrire : la formation est expirée.'], 400);
    }
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
    public function update(Request $request, $id)
    {
        $inscrit = Inscrit::findOrFail($id);
    
        $validated = $request->validate([
            'apprenant_id' => 'required|exists:apprenants,user_id',
            'formation_id' => 'required|exists:formations,id',
        ]);
    
        // Vérifie que la nouvelle combinaison apprenant/formation n’existe pas déjà
        $exists = Inscrit::where('apprenant_id', $validated['apprenant_id'])
            ->where('formation_id', $validated['formation_id'])
            ->where('id_inscrit', '!=', $id)
            ->exists();
    
        if ($exists) {
            return response()->json(['message' => 'Cette inscription existe déjà.'], 409);
        }
    
        $inscrit->update($validated);
    
        return response()->json(['message' => 'Inscription modifiée.', 'inscrit' => $inscrit]);
    }
    
    public function destroy($id)
    {
        $inscrit = Inscrit::findOrFail($id);
        $inscrit->delete();

        return response()->json(['message' => 'Inscription supprimée.']);
    }
      // Retourner toutes les formations d'un apprenant donné
    public function getByApprenant($id)
    {
        $inscriptions = Inscrit::with(['formation', 'apprenant.user'])
            ->where('apprenant_id', $id)
            ->get();

        return response()->json($inscriptions);
    }
    
}