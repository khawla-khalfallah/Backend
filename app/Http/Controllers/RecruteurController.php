<?php

namespace App\Http\Controllers;

use App\Models\Recruteur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;


class RecruteurController extends Controller
{
    // Afficher tous les recruteurs
    public function index()
    {
        return response()->json(Recruteur::with('user')->get());
    }

    // Créer un recruteur
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user_id' => 'required|exists:users,id|unique:recruteurs,user_id',
    //         'entreprise' => 'nullable|string|max:100',
    //     ]);

    //     $recruteur = Recruteur::create($validated);
    //     return response()->json($recruteur, 201);
    // }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'entreprise' => 'nullable|string|max:100',
        ]);
    
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'recruteur',
        ]);
    
        $recruteur = Recruteur::create([
            'user_id' => $user->id,
            'entreprise' => $validated['entreprise'] ?? null
        ]);
    
        return response()->json($recruteur->load('user'), 201);
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
    // public function destroy($id)
    // {
    //     Recruteur::destroy($id);
    //     return response()->json(['message' => 'Recruteur supprimé avec succès']);
    // }
    public function destroy($id)
    {
        $recruteur = Recruteur::findOrFail($id);
    
        // Supprimer le user lié
        if ($recruteur->user) {
            $recruteur->user->delete();
        }
    
        $recruteur->delete();
    
        return response()->json(['message' => 'Recruteur et utilisateur supprimés']);
    }
    
}
