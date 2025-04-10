<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Liste de tous les utilisateurs
    public function index()
    {
        return response()->json(User::all());
    }

    // Ajouter un utilisateur
    public function store(Request $request)
    {
        // Validation des données, y compris les champs spécifiques pour chaque rôle
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:apprenant,formateur,recruteur,admin',
            // Validation des champs spécifiques pour les rôles
            'niveau_etude' => 'nullable|string',
            'specialite' => 'required_if:role,formateur|string|max:100', // Spécifique au formateur
            'bio' => 'required_if:role,formateur|string|max:500', // Spécifique au formateur
            'entreprise' => 'required_if:role,recruteur|string|max:100', // Spécifique au recruteur
        ]);

        // Hacher le mot de passe
        $validated['password'] = Hash::make($validated['password']);

        // Créer l'utilisateur
        $user = User::create($validated);

        // Ajouter l'utilisateur dans la table correspondante en fonction du rôle
        if ($validated['role'] === 'apprenant') {
            // Ajouter l'utilisateur dans la table 'apprenants'
            \App\Models\Apprenant::create([
                'user_id' => $user->id,
                'niveau_etude' => $validated['niveau_etude'],
            ]);
        } elseif ($validated['role'] === 'formateur') {
            // Ajouter l'utilisateur dans la table 'formateurs' avec les champs 'specialite' et 'bio'
            \App\Models\Formateur::create([
                'user_id' => $user->id,
                'specialite' => $validated['specialite'],
                'bio' => $validated['bio'],
            ]);
        } elseif ($validated['role'] === 'recruteur') {
            // Ajouter l'utilisateur dans la table 'recruteurs' avec le champ 'entreprise'
            \App\Models\Recruteur::create([
                'user_id' => $user->id,
                'entreprise' => $validated['entreprise'],
            ]);
        }

        // Retourner la réponse avec l'utilisateur créé
        return response()->json($user, 201);
    }

    // Voir un utilisateur
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Modifier un utilisateur
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:50',
            'prenom' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role' => 'sometimes|in:apprenant,formateur,recruteur,admin',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    // Supprimer un utilisateur
    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }
}