<?php

namespace App\Http\Controllers;

use App\Models\Formateur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


class FormateurController extends Controller
{
      // Afficher tous les formateurs
      public function index()
    //   {
    //       return response()->json(Formateur::with('user')->get());
    //   }
  {
    $formateurs = Formateur::with('user')->get();

    // 🔥 Ajouter cv_url automatiquement
    $formateurs->map(function ($f) {
        if ($f->cv) { $f->cv_url = Storage::url($f->cv); 
            // ça va générer: /storage/cvs/xxxx.pdf
        } else {
            $f->cv_url = null;
        }
        return $f;
    });

    return response()->json($formateurs);
}
      // Créer un formateur
    //   public function store(Request $request)
    //   {
    //       $validated = $request->validate([
    //           'user_id' => 'required|exists:users,id|unique:formateurs,user_id',
    //           'specialite' => 'nullable|string|max:100',
    //           'bio' => 'nullable|string',
    //       ]);
  
    //       $formateur = Formateur::create($validated);
    //       return response()->json($formateur, 201);
    //   }
            // Créer un formateur (avec création de user)
            public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'specialite' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        // Création du user
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'formateur',
        ]);

        // Upload du fichier CV
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'public'); 
        }

        // Création du formateur
        $formateur = Formateur::create([
            'user_id' => $user->id,
            'specialite' => $validated['specialite'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'cv' => $cvPath,
        ]);

        return response()->json($formateur->load('user'), 201);
    }


  
      // Afficher un formateur spécifique
            // public function show($id)
            // {
            //     $formateur = Formateur::with(['user', 'formations'])->findOrFail($id);
            //     return response()->json($formateur);
            // }

         // Afficher un formateur spécifique
            public function show($id)
            {
                $formateur = Formateur::with(['formations.examens.apprenant.user'])->find($id);
            
                if (!$formateur) {
                    return response()->json(['message' => 'Formateur non trouvé'], 404);
                }
            
                return response()->json($formateur);
            }

  
      // Mettre à jour un formateur
    //   public function update(Request $request, $id)
    //   {
    //       $formateur = Formateur::findOrFail($id);
  
    //       $validated = $request->validate([
    //           'specialite' => 'nullable|string|max:100',
    //           'bio' => 'nullable|string',
    //       ]);
  
    //       $formateur->update($validated);
    //       return response()->json($formateur);
    //   }
            public function update(Request $request, $id)
            {
                $formateur = Formateur::with('user')->findOrFail($id);
            
                $validated = $request->validate([
                    'nom' => 'nullable|string|max:255',
                    'prenom' => 'nullable|string|max:255',
                    'email' => 'nullable|email|unique:users,email,' . $formateur->user_id,
                    'password' => 'nullable|string|min:6',
                    'specialite' => 'nullable|string|max:100',
                    'bio' => 'nullable|string',
                    'cv' => 'nullable|file|mimes:pdf,doc,docx',

                ]);
            
                // Mettre à jour User
                $user = $formateur->user;
                if (isset($validated['nom'])) $user->nom = $validated['nom'];
                if (isset($validated['prenom'])) $user->prenom = $validated['prenom'];
                if (isset($validated['email'])) $user->email = $validated['email'];
                if (isset($validated['password'])) $user->password = bcrypt($validated['password']);
                $user->save();
                 // Upload nouveau CV si fourni
                if ($request->hasFile('cv')) {
                    $cvPath = $request->file('cv')->store('cvs', 'public');
                    $formateur->cv = $cvPath;
                }
            
                // Mettre à jour Formateur
                if (isset($validated['specialite'])) $formateur->specialite = $validated['specialite'];
                if (isset($validated['bio'])) $formateur->bio = $validated['bio'];
                $formateur->save();
            
                return response()->json(['message' => 'Profil formateur mis à jour avec succès']);
            }
            
                    
            
      // Supprimer un formateur
    //   public function destroy($id)
    //   {
    //       Formateur::destroy($id);
    //       return response()->json(['message' => 'Formateur supprimé avec succès']);
    //   }
            public function destroy($id)
            {
                $formateur = Formateur::findOrFail($id);
            
                // Supprimer l'utilisateur associé
                if ($formateur->user) {
                    $formateur->user->delete();
                }
            
                // Supprimer le formateur (si cascade pas active)
                $formateur->delete();
            
                return response()->json(['message' => 'Formateur et utilisateur supprimés avec succès']);
            }
    
}
