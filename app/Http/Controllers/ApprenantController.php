<?php

namespace App\Http\Controllers;

use App\Models\Apprenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;


class ApprenantController extends Controller
{
    // Tous les apprenants
    public function index()
    {
        return response()->json(Apprenant::with('user')->get());
    }

    // Créer un apprenant
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user_id' => 'required|exists:users,id|unique:apprenants,user_id',
    //         'niveau_etude' => 'nullable|string|max:50',
    //     ]);

    //     $apprenant = Apprenant::create($validated);
    //     return response()->json($apprenant, 201);
    // }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'niveau_etude' => 'required|string',
        ]);
    
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'apprenant'
        ]);
    
        $apprenant = Apprenant::create([
            'user_id' => $user->id,
            'niveau_etude' => $validated['niveau_etude']
        ]);
    
        return response()->json(['message' => 'Apprenant créé', 'apprenant' => $apprenant], 201);
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
    // public function destroy($id)
    // {
    //     Apprenant::destroy($id);
    //     return response()->json(['message' => 'Apprenant supprimé avec succès']);
    // }
    public function destroy($id)
    {
        try {
            $apprenant = Apprenant::with('user')->findOrFail($id);
    
            // Supprimer l'utilisateur associé
            if ($apprenant->user) {
                $apprenant->user->delete();
            }
    
            // Supprimer l'apprenant
            $apprenant->delete();
    
            return response()->json(['message' => 'Apprenant supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    

















    public function chercherParNom(Request $request)
    {
        $nom = $request->query('nom');
    
        $apprenant = Apprenant::with('user')
            ->whereHas('user', function ($query) use ($nom) {
                $query->where('nom', 'like', "%$nom%")
                      ->orWhere('prenom', 'like', "%$nom%");
            })
            ->first();
    
        if (!$apprenant) {
            return response()->json(['message' => 'Aucun apprenant trouvé'], 404);
        }
    
        return response()->json($apprenant);
    }
    
}
