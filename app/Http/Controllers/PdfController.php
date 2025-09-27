<?php

// namespace App\Http\Controllers;

// namespace App\Http\Controllers;

// use App\Models\Pdf;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Storage;

// class PdfController extends Controller {
//     public function index()
//     {
//         return Pdf::with('formation')->get();
//     }

//     public function store(Request $request)
//     {
//          // Validation des champs
//     $validated = $request->validate([
//         'titre' => 'required|string|max:255',
//         'fichier' => 'required|file|mimes:pdf|max:102400', 
//         'formation_id' => 'required|exists:formations,id',
//     ]);

//     // Gestion du fichier téléchargé
//     $file = $request->file('fichier');
//     $filePath = $file->storeAs('pdfs', $file->getClientOriginalName(), 'public'); // Enregistre dans 'storage/app/public/pdfs'

//     // Création du PDF dans la base de données
//     $pdf = Pdf::create([
//         'titre' => $validated['titre'],
//         'fichier' => $filePath,
//         'formation_id' => $validated['formation_id'],
//     ]);

//     return response()->json($pdf, 201);
//     }

//     public function show($id)
//     {
//         return Pdf::with('formation')->findOrFail($id);
//     }

//     public function update(Request $request, $id)
//     {
//         $pdf = Pdf::findOrFail($id);
    
//         $validated = $request->validate([
//             'titre' => 'sometimes|string|max:255',
//             'fichier' => 'nullable|file|mimes:pdf|max:102400', // ✅ pas "string", mais bien fichier
//             'formation_id' => 'sometimes|exists:formations,id',
//         ]);
    
//         // 📎 Si un nouveau fichier a été envoyé
//         if ($request->hasFile('fichier')) {
//             // Supprimer l'ancien fichier s'il existe (optionnel)
//             if ($pdf->fichier && Storage::disk('public')->exists($pdf->fichier)) {
//                 Storage::disk('public')->delete($pdf->fichier);
//             }
    
//             $file = $request->file('fichier');
//             $filePath = $file->storeAs('pdfs', $file->getClientOriginalName(), 'public');
//             $validated['fichier'] = $filePath;
//         }
    
//         $pdf->update($validated);
//         return response()->json($pdf);
//     }
//     public function getByFormateur($id)
//     {
//         // Récupère uniquement les PDFs des formations créées par ce formateur
//         return Pdf::whereHas('formation', function ($q) use ($id) {
//             $q->where('formateur_id', $id);
//         })
//         ->with('formation:id,titre,formateur_id') // on limite les colonnes utiles
//         ->get(['id', 'titre', 'fichier', 'formation_id']); // ✅ on force le retour de l'id
//     }



//     public function destroy($id)
//     {
//         $pdf = Pdf::findOrFail($id);
//         $pdf->delete();

//         return response()->json(['message' => 'PDF supprimé.']);
//     }
// }


namespace App\Http\Controllers;

use App\Models\Pdf;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    // 📌 Récupérer tous les PDFs
    public function index()
    {
        return Pdf::all();
    }

    // 📌 Récupérer les PDFs d'un formateur
    public function getByFormateur($formateurId)
    {
        return Pdf::whereHas('formation', function ($query) use ($formateurId) {
            $query->where('formateur_id', $formateurId);
        })->get();
    }

    // 📌 Récupérer les PDFs d'une formation
    public function getByFormation($formationId)
    {
        return Pdf::where('formation_id', $formationId)->get();
    }

    // 📌 Ajouter un PDF
    public function store(Request $request)
    {
        try {
            // Debug logging
            \Log::info('PDF Store Request', [
                'user_id' => $request->user() ? $request->user()->id : null,
                'user_role' => $request->user() ? $request->user()->role : null,
                'request_data' => $request->all(),
                'has_file' => $request->hasFile('fichier'),
            ]);

            $user = $request->user();
            if (!$user) {
                \Log::error('PDF Store: No authenticated user');
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            if ($user->role !== 'formateur') {
                \Log::error('PDF Store: User is not formateur', ['user_role' => $user->role]);
                return response()->json(['error' => 'Seuls les formateurs peuvent ajouter des PDFs'], 403);
            }

            // Check formateur status
            $formateur = \App\Models\Formateur::where('user_id', $user->id)->first();
            if (!$formateur) {
                \Log::error('PDF Store: Formateur profile not found', ['user_id' => $user->id]);
                return response()->json(['error' => 'Profil formateur non trouvé'], 404);
            }

            if ($formateur->status !== 'accepte') {
                \Log::error('PDF Store: Formateur not accepted', ['status' => $formateur->status]);
                return response()->json(['error' => 'Votre compte formateur doit être accepté pour ajouter des PDFs'], 403);
            }

            $validated = $request->validate([
                'titre' => 'required|string|max:255',
                'fichier' => 'required|file|mimes:pdf|max:102400',
                'formation_id' => 'required|exists:formations,id',
            ]);

            $formation = Formation::findOrFail($validated['formation_id']);

            if ($formation->formateur_id !== $user->id) {
                \Log::error('PDF Store: Formation does not belong to user', [
                    'formation_id' => $formation->id,
                    'formation_formateur_id' => $formation->formateur_id,
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Cette formation ne vous appartient pas'], 403);
            }

            $filePath = $request->file('fichier')
                ->storeAs('pdfs', $request->file('fichier')->getClientOriginalName(), 'public');

            $pdf = Pdf::create([
                'titre' => $validated['titre'],
                'fichier' => $filePath,
                'formation_id' => $formation->id,
            ]);

            \Log::info('PDF Store: Success', ['pdf_id' => $pdf->id]);

            return response()->json($pdf, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('PDF Store: Validation failed', ['errors' => $e->errors()]);
            return response()->json(['error' => 'Données invalides', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('PDF Store: Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    // 📌 Modifier un PDF
   public function update(Request $request, $id)
{
    $pdf = Pdf::findOrFail($id);
    $user = $request->user();

    // Vérifier que le formateur possède le PDF
    if ($pdf->formation->formateur_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    // Valider les champs
    $validated = $request->validate([
        'titre' => 'sometimes|string|max:255',
        'fichier' => 'sometimes|file|mimes:pdf|max:102400',
        'formation_id' => 'sometimes|exists:formations,id',
    ]);

    // Vérifier si le formateur est propriétaire de la nouvelle formation
    if (isset($validated['formation_id'])) {
        $nouvelleFormation = Formation::findOrFail($validated['formation_id']);
        if ($nouvelleFormation->formateur_id !== $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas assigner ce PDF à cette formation'], 403);
        }
        $pdf->formation_id = $validated['formation_id'];
    }

    // Mise à jour du fichier si envoyé
    if ($request->hasFile('fichier')) {
        if ($pdf->fichier) {
            Storage::disk('public')->delete($pdf->fichier);
        }
        $filePath = $request->file('fichier')->storeAs(
            'pdfs',
            $request->file('fichier')->getClientOriginalName(),
            'public'
        );
        $pdf->fichier = $filePath;
    }

    if (isset($validated['titre'])) {
        $pdf->titre = $validated['titre'];
    }

    $pdf->save();

    return response()->json($pdf);
}

//     public function update(Request $request, $id)
// {
//     // Vérifier l'utilisateur connecté
//     $user = $request->user();
//     if (!$user) {
//         return response()->json(['error' => 'Utilisateur non authentifié'], 401);
//     }

//     // Récupérer le PDF
//     $pdf = Pdf::find($id);
//     if (!$pdf) {
//         return response()->json(['error' => 'PDF introuvable'], 404);
//     }

//     // Vérifier que le formateur est bien propriétaire du PDF
//     if ($pdf->formation->formateur_id !== $user->id) {
//         return response()->json(['error' => 'Vous n’êtes pas autorisé à modifier ce PDF'], 403);
//     }

//     // Valider les données
//     $request->validate([
//         'titre' => 'required|string|max:255',
//         'formation_id' => 'required|exists:formations,id',
//         'fichier' => 'nullable|file|mimes:pdf|max:102400', // 100MB max
//     ]);

//     // Mise à jour du PDF
//     $pdf->titre = $request->titre;
//     $pdf->formation_id = $request->formation_id;

//     if ($request->hasFile('fichier')) {
//         $file = $request->file('fichier');
//         $filename = time() . '_' . $file->getClientOriginalName();
//         $file->storeAs('pdfs', $filename, 'public'); // stocke dans storage/app/public/pdfs
//         $pdf->fichier = $filename;
//     }

//     $pdf->save();

//     return response()->json(['message' => 'PDF mis à jour avec succès', 'pdf' => $pdf]);
// }


    // 📌 Supprimer un PDF
    public function destroy(Request $request, $id)
    {
        $pdf = Pdf::findOrFail($id);
        $user = $request->user();

        if ($pdf->formation->formateur_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($pdf->fichier) {
            Storage::disk('public')->delete($pdf->fichier);
        }

        $pdf->delete();

        return response()->json(['message' => 'PDF supprimé avec succès']);
    }
}
