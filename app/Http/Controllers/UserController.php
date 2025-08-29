<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Apprenant;
use App\Models\Formateur;
use App\Models\Recruteur;

class UserController extends Controller
{
    // Liste de tous les utilisateurs avec leurs profils
    public function index()
    {
        try {
            $users = User::with(['apprenant', 'formateur', 'recruteur'])->get();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Ajouter un utilisateur avec transaction
   public function store(Request $request)
{
    $validated = $request->validate([
        'nom' => 'required|string|max:50',
        'prenom' => 'required|string|max:50',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6|confirmed',
        'role' => 'required|in:apprenant,formateur,recruteur',
        'niveau_etude' => 'nullable|required_if:role,apprenant|string|max:100',
        'specialite' => 'nullable|required_if:role,formateur|string|max:100',
        'bio' => 'nullable|required_if:role,formateur|string|max:500',
        'entreprise' => 'nullable|required_if:role,recruteur|string|max:100',
        'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048'

    ]);

    DB::beginTransaction();
    try {
        // 1. Création de l'utilisateur principal
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // 2. Création du profil spécifique selon le rôle
        switch ($validated['role']) {
            case 'apprenant':
                $user->apprenant()->create([
                    'user_id' => $user->id, // Explicitement défini
                    'niveau_etude' => $validated['niveau_etude']
                ]);
                break;

            case 'formateur':
                    $formateurData = [
                        'user_id' => $user->id,
                        'specialite' => $validated['specialite'],
                        'bio' => $validated['bio']
                    ];

                    if ($request->hasFile('cv')) {
                        $cvPath = $request->file('cv')->store('cvs', 'public');
                        $formateurData['cv'] = $cvPath;
                    }

                    $user->formateur()->create($formateurData);
                    break;

            case 'recruteur':
                $user->recruteur()->create([
                    'user_id' => $user->id, // Explicitement défini
                    'entreprise' => $validated['entreprise']
                ]);
                break;
        }

        DB::commit();
        
        return response()->json([
            'user' => $user->load([$user->role]), // Charge automatiquement la relation
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Erreur lors de la création',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Voir un utilisateur avec son profil
    public function show($id)
    {
        try {
            $user = User::with(['apprenant', 'formateur', 'recruteur'])->findOrFail($id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Utilisateur non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Modifier un utilisateur
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            
            // Validation des champs de base
            $validated = $request->validate([
                'nom' => 'sometimes|string|max:50',
                'prenom' => 'sometimes|string|max:50',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'nullable|min:6|confirmed',
                'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048'

            ]);

            // Hachage du mot de passe si fourni
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Mise à jour du profil spécifique
            $this->updateUserProfile($user, $request);

            DB::commit();
            return response()->json([
                'message' => 'Utilisateur modifié avec succès',
                'user' => $user->fresh([$user->role])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Supprimer un utilisateur et son profil
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::with(['apprenant', 'formateur', 'recruteur'])->findOrFail($id);

            // Suppression du profil
            if ($user->{$user->role}) {
                $user->{$user->role}->delete();
            }

            $user->delete();
            DB::commit();

            return response()->json([
                'message' => 'Utilisateur et profil supprimés avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Récupérer l'utilisateur connecté avec son profil
    public function me(Request $request)
    {
        try {
            $user = $request->user()->load([
                'apprenant', 
                'formateur', 
                'recruteur', 
                'administrateur'
            ]);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Méthode privée pour mettre à jour le profil utilisateur
    private function updateUserProfile(User $user, Request $request)
    {
        switch ($user->role) {
            case 'apprenant':
                $request->validate(['niveau_etude' => 'nullable|string|max:100']);
                if ($user->apprenant) {
                    $user->apprenant->update(['niveau_etude' => $request->niveau_etude]);
                }
                break;

            case 'formateur':
                $request->validate([
                    'specialite' => 'nullable|string|max:100',
                    'bio' => 'nullable|string|max:500',
                    'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
                ]);
                if ($user->formateur) {
                    $updateData = [
                        'specialite' => $request->specialite,
                        'bio' => $request->bio
                    ];
                    if ($request->hasFile('cv')) {
                        $cvPath = $request->file('cv')->store('cvs', 'public');
                        $updateData['cv'] = $cvPath;
                    }
                    $user->formateur->update($updateData);
                }
                break;

            case 'recruteur':
                $request->validate(['entreprise' => 'nullable|string|max:100']);
                if ($user->recruteur) {
                    $user->recruteur->update(['entreprise' => $request->entreprise]);
                }
                break;
        }
    }
}