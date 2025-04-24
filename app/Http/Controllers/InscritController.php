<?php
namespace App\Http\Controllers;

use App\Models\Inscrit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Apprenant;

class InscritController extends Controller
{
    
    // public function index()
    // {
    //     return Inscrit::with(['apprenant.user', 'formation'])->get();
    // }
    // public function index()
    // {
        // $user = 1;//Auth::user();
        
        // // Vérifie si l'utilisateur est un apprenant
        // if (!$user->apprenant) {
        //     return response()->json(['message' => 'Accès réservé aux apprenants.'], 403);
        // }
        // $aprenantConnected = 1; // twalli fonction kifeh tejbed connecté
        // Récupère les formations de l'apprenant connecté
    //     return Inscrit::with('formation')
    //         ->where('apprenant_id', $aprenantConnected)//$user->apprenant->id)
    //         ->get()
    //         ->map(function($inscription) {
    //             return [
    //                 'formation' => $inscription->formation,
    //                 'date_inscription' => $inscription->created_at->format('Y-m-d H:i:s')
    //             ];
    //         });
    // }
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
