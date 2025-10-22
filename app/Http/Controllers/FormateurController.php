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
   
    // Créer un formateur (avec création de user)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'prenom' => 'required|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
            'email' => 'required|email|unique:users,email|regex:/^[\w\.-]+@([\w-]+\.)+[a-zA-Z]{2,}$/',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            'specialite' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:500',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:102400',
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
            'status' => 'en_attente' 
        ]);

        return response()->json($formateur->load('user'), 201);
    }
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
            public function update(Request $request, $id)
            {
                $formateur = Formateur::with('user')->findOrFail($id);
            
                $validated = $request->validate([
                    'nom' => 'nullable|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
                    'prenom' => 'nullable|string|max:50|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
                    'email' => 'nullable|email|unique:users,email,' . $formateur->user_id . '|regex:/^[\w\.-]+@([\w-]+\.)+[a-zA-Z]{2,}$/',
                    'password' => 'nullable|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                    'specialite' => 'nullable|string|max:100',
                    'bio' => 'nullable|string|max:500',
                    'cv' => 'nullable|file|mimes:pdf,doc,docx|max:102400',

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
            public function updateStatus(Request $request, $id)
            {
                $data = $request->validate([
                    'status'   => 'required|in:en_attente,accepte,refuse',
                    'remarque' => 'nullable|string|max:1000',
                ]);

                $formateur = Formateur::findOrFail($id);

                // ✅ remarque obligatoire si refus
                 if ($data['status'] === 'refuse' && empty($data['remarque'])) {
                    return response()->json([
                        'message' => 'Une remarque est obligatoire en cas de refus.',
                        'errors'  => ['remarque' => ['Remarque obligatoire si refus']]
                    ], 422);
                }

                $formateur->status   = $data['status'];
                $formateur->remarque = $data['status'] === 'refuse' ? $data['remarque'] : null;
                $formateur->save();

                return response()->json([
                    'message'   => 'Statut mis à jour avec succès.',
                    'formateur' => $formateur->load('user')
                ]);
            }

                    
            
      // Supprimer un formateur
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

    /**
     * Admin: Get CV file for viewing/downloading
     */
    public function downloadCV($id)
    {
        try {
            $formateur = Formateur::with('user')->findOrFail($id);
            
            if (!$formateur->cv) {
                return response()->json(['message' => 'Aucun CV trouvé pour ce formateur'], 404);
            }

            $filePath = storage_path('app/public/' . $formateur->cv);
            
            if (!file_exists($filePath)) {
                return response()->json(['message' => 'Fichier CV introuvable sur le serveur'], 404);
            }

            $fileName = $formateur->user->nom . '_' . $formateur->user->prenom . '_CV.' . pathinfo($filePath, PATHINFO_EXTENSION);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors du téléchargement du CV', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: View CV in browser
     */
    public function viewCV($id)
    {
        try {
            $formateur = Formateur::with('user')->findOrFail($id);
            
            if (!$formateur->cv) {
                return response()->json(['message' => 'Aucun CV trouvé pour ce formateur'], 404);
            }

            $filePath = storage_path('app/public/' . $formateur->cv);
            
            if (!file_exists($filePath)) {
                return response()->json(['message' => 'Fichier CV introuvable sur le serveur'], 404);
            }

            $mimeType = mime_content_type($filePath);

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline'
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la consultation du CV', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Get formateurs with CV status for management
     */
    public function getFormateursWithCVStatus()
    {
        try {
            $formateurs = Formateur::with('user')
                ->select('user_id', 'specialite', 'bio', 'cv', 'status', 'remarque', 'created_at', 'updated_at')
                ->get()
                ->map(function ($formateur) {
                    return [
                        'id' => $formateur->user_id,
                        'nom' => $formateur->user->nom,
                        'prenom' => $formateur->user->prenom,
                        'email' => $formateur->user->email,
                        'specialite' => $formateur->specialite,
                        'bio' => $formateur->bio,
                        'status' => $formateur->status,
                        'remarque' => $formateur->remarque,
                        'has_cv' => !is_null($formateur->cv),
                        'cv_filename' => $formateur->cv ? basename($formateur->cv) : null,
                        'cv_url' => $formateur->cv ? Storage::url($formateur->cv) : null,
                        'cv_size' => $formateur->cv && Storage::disk('public')->exists($formateur->cv) 
                            ? Storage::disk('public')->size($formateur->cv) 
                            : null,
                        'inscription_date' => $formateur->created_at,
                        'last_update' => $formateur->updated_at,
                    ];
                });

            return response()->json([
                'formateurs' => $formateurs,
                'stats' => [
                    'total' => $formateurs->count(),
                    'with_cv' => $formateurs->where('has_cv', true)->count(),
                    'without_cv' => $formateurs->where('has_cv', false)->count(),
                    'en_attente' => $formateurs->where('status', 'en_attente')->count(),
                    'accepte' => $formateurs->where('status', 'accepte')->count(),
                    'refuse' => $formateurs->where('status', 'refuse')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des formateurs', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin: Get detailed CV info
     */
    public function getCVInfo($id)
    {
        try {
            $formateur = Formateur::with('user')->findOrFail($id);
            
            if (!$formateur->cv) {
                return response()->json(['message' => 'Aucun CV trouvé pour ce formateur'], 404);
            }

            $filePath = storage_path('app/public/' . $formateur->cv);
            $fileExists = file_exists($filePath);
            
            return response()->json([
                'formateur_info' => [
                    'id' => $formateur->user_id,
                    'nom' => $formateur->user->nom,
                    'prenom' => $formateur->user->prenom,
                    'email' => $formateur->user->email,
                    'specialite' => $formateur->specialite,
                    'status' => $formateur->status,
                ],
                'cv_info' => [
                    'filename' => basename($formateur->cv),
                    'path' => $formateur->cv,
                    'url' => Storage::url($formateur->cv),
                    'size_bytes' => $fileExists ? filesize($filePath) : null,
                    'size_human' => $fileExists ? $this->formatBytes(filesize($filePath)) : null,
                    'mime_type' => $fileExists ? mime_content_type($filePath) : null,
                    'file_exists' => $fileExists,
                    'last_modified' => $fileExists ? date('Y-m-d H:i:s', filemtime($filePath)) : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des informations du CV', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper function to format file size
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
}
