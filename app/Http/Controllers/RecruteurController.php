<?php

namespace App\Http\Controllers;

use App\Models\Recruteur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'nom' => 'required|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'prenom' => 'required|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => 'required|email|unique:users,email|regex:/^[\w\.-]+@([\w-]+\.)+[a-zA-Z]{2,}$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            'entreprise' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
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

            DB::commit();
            return response()->json([
                'message' => 'Recruteur créé',
                'data' => $recruteur->load('user')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Afficher un recruteur spécifique
    public function show($id)
    {
        $recruteur = Recruteur::where('user_id', $id)->firstOrFail();
        return response()->json($recruteur);
    }

    // Mettre à jour un recruteur (via user_id)
   
    public function update(Request $request, $user_id)
    {
        $validated = $request->validate([
            'nom' => 'nullable|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'prenom' => 'nullable|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => 'nullable|email|unique:users,email,' . $user_id . '|regex:/^[\w\.-]+@([\w-]+\.)+[a-zA-Z]{2,}$/',
            'password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            'entreprise' => 'nullable|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Récupération correcte du recruteur via user_id
            $recruteur = Recruteur::where('user_id', $user_id)->firstOrFail();
            $user = $recruteur->user;

            // Mise à jour user
            if (isset($validated['nom'])) $user->nom = $validated['nom'];
            if (isset($validated['prenom'])) $user->prenom = $validated['prenom'];
            if (isset($validated['email'])) $user->email = $validated['email'];
            if (isset($validated['password'])) $user->password = Hash::make($validated['password']);
            $user->save();

            // Mise à jour recruteur
            if (isset($validated['entreprise'])) {
                $recruteur->entreprise = $validated['entreprise'];
                $recruteur->save();
            }

            DB::commit();
            return response()->json([
                'message' => 'Profil mis à jour',
                'data' => $recruteur->fresh('user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur de mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Supprimer un recruteur
    public function destroy($user_id)
    {
        DB::beginTransaction();
        try {
            $recruteur = Recruteur::where('user_id', $user_id)->firstOrFail();
            $user = $recruteur->user;

            if ($user) $user->delete();
            $recruteur->delete();

            DB::commit();
            return response()->json(['message' => 'Recruteur et utilisateur supprimés']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur de suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}