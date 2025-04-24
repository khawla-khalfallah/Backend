<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Apprenant;
use App\Models\Formateur;
use App\Models\Recruteur;


class UserController extends Controller
{
    // Liste de tous les utilisateurs
    // public function index()
    // {
    //     return response()->json(User::all());
    // }
    public function index()
    {
        return response()->json(
            User::with(['apprenant', 'formateur', 'recruteur'])->get()
        );
    }
    
    // Ajouter un utilisateur
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:apprenant,formateur,recruteur',
            'niveau_etude' => 'nullable|string|max:100',
            'specialite' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:500',
            'entreprise' => 'nullable|string|max:100',
        ]);

        // Création de l'utilisateur avec le mot de passe hashé
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Création du profil spécifique selon le rôle
        switch ($validated['role']) {
            case 'apprenant':
                $user->apprenant()->create([
                    'niveau_etude' => $validated['niveau_etude'] ?? null,
                ]);
                break;

            case 'formateur':
                $user->formateur()->create([
                    'specialite' => $validated['specialite'] ?? null,
                    'bio' => $validated['bio'] ?? null,
                ]);
                break;

            case 'recruteur':
                $user->recruteur()->create([
                    'entreprise' => $validated['entreprise'] ?? null,
                ]);
                break;
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    // Voir un utilisateur
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Modifier un utilisateur
    // public function update(Request $request, $id)
    // {
    //     $user = User::findOrFail($id);

    //     $validated = $request->validate([
    //         'nom' => 'sometimes|string|max:50',
    //         'prenom' => 'sometimes|string|max:50',
    //         'email' => 'sometimes|email|unique:users,email,' . $id,
    //         'password' => 'nullable|min:6|confirmed',
    //         'role' => 'sometimes|in:apprenant,formateur,recruteur,admin',
    //     ]);

    //     // Si un mot de passe est fourni, le hacher
    //     if (!empty($validated['password'])) {
    //         $validated['password'] = Hash::make($validated['password']);
    //     } else {
    //         unset($validated['password']);
    //     }

    //     $user->update($validated);

    //     return response()->json($user);
    // }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // ✅ Valider les champs de base
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:50',
            'prenom' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
        ]);
    
        // ✅ Hacher le mot de passe s’il est fourni
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
    
        // ✅ Mise à jour des infos générales
        $user->update($validated);
    
        // ✅ Mise à jour des données spécifiques selon le rôle
        if ($user->role === 'apprenant') {
            $request->validate([
                'niveau_etude' => 'nullable|string|max:100',
            ]);
    
            $apprenant = $user->apprenant;
            if ($apprenant) {
                $apprenant->update([
                    'niveau_etude' => $request->niveau_etude,
                ]);
            }
        }
    
        if ($user->role === 'formateur') {
            $request->validate([
                'specialite' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:500',
            ]);
    
            $formateur = $user->formateur;
            if ($formateur) {
                $formateur->update([
                    'specialite' => $request->specialite,
                    'bio' => $request->bio,
                ]);
            }
        }
    
        if ($user->role === 'recruteur') {
            $request->validate([
                'entreprise' => 'nullable|string|max:100',
            ]);
    
            $recruteur = $user->recruteur;
            if ($recruteur) {
                $recruteur->update([
                    'entreprise' => $request->entreprise,
                ]);
            }
        }
    
        return response()->json(['message' => 'Utilisateur modifié avec succès', 'user' => $user]);
    }
    // Supprimer un utilisateur
    // public function destroy($id)
    // {
    //     $user = User::findOrFail($id);
    //     $user->delete();

    //     return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    // }
    public function destroy($id)
    {
        $user = User::with(['apprenant', 'formateur', 'recruteur'])->findOrFail($id);
    
        // 🔥 Supprimer les données associées
        if ($user->apprenant) {
            $user->apprenant->delete();
        }
    
        if ($user->formateur) {
            $user->formateur->delete();
        }
    
        if ($user->recruteur) {
            $user->recruteur->delete();
        }
    
        // 🔥 Supprimer le user lui-même
        $user->delete();
    
        return response()->json(['message' => 'Utilisateur et profil associé supprimés avec succès']);
    }
    
   public function me(Request $request)
{
    $user = $request->user()->load(['apprenant', 'formateur', 'recruteur', 'administrateur']);
    return response()->json($user);
}


}
