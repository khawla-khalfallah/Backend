<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Examen;
// use App\Models\Formation;

// class ExamenController extends Controller
// {
//     public function index()
//     {
//         return Examen::with(['formation', 'apprenant.user'])->get();
//     }

//     public function store(Request $request)
//     {
//         $validated = $request->validate([
//             'date_examen' => 'required|date',
//             'note' => 'nullable|numeric',
//             'formation_id' => 'required|exists:formations,id',
//             'apprenant_id' => 'required|exists:apprenants,user_id',
//         ]);

//         $examen = Examen::create($validated);
//         return response()->json($examen, 201);
//     }

//     public function show($id)
//     {
        
//         $examen = Examen::with(['formation', 'apprenant.user','questions'])->findOrFail($id);
//         return response()->json($examen);

//     }

//     public function update(Request $request, $id)
//     {
//         $examen = Examen::findOrFail($id);

//         $validated = $request->validate([
//             'date_examen' => 'sometimes|date',
//             'note' => 'nullable|numeric',
//             'formation_id' => 'sometimes|exists:formations,id',
//             'apprenant_id' => 'sometimes|exists:apprenants,user_id',
//         ]);

//         $examen->update($validated);
//         return response()->json($examen);
//     }



//     public function passer($id)
//     {
//         $examen = Examen::with('formation', 'questions')->findOrFail($id);
//         return response()->json($examen);
//     }




//     public function soumettre(Request $request, $id)
//     {
//         $examen = Examen::with('questions')->findOrFail($id);
//         $reponsesUtilisateur = $request->input('reponses');
//         $note = 0;
//         $total = count($examen->questions);
    
//         foreach ($examen->questions as $question) {
//             if (
//                 isset($reponsesUtilisateur[$question->id]) &&
//                 $reponsesUtilisateur[$question->id] === $question->reponse_correcte
//             ) {
//                 $note++;
//             }
//         }
    
//         $noteFinale = round(($note / $total) * 20, 2); // sur 20
    
//         // TODO : mise à jours la note dans une table `examens`, par exemple
    
//     // enregistrement de la note dans la base
//             $examen->note = $noteFinale;
//             $examen->save();
//                 return response()->json(['note' => $noteFinale]);
//             }
    


//     public function destroy($id)
//     {
//         $examen = Examen::findOrFail($id);
//         $examen->delete();

//         return response()->json(['message' => 'Examen supprimé.']);
//     }
// }


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Question;
use App\Models\ReponseApprenant;
use App\Models\ExamenApprenant;
use Illuminate\Support\Facades\Auth;

class ExamenController extends Controller
{
    // 📌 Liste tous les examens avec formation et questions
    public function index()
    {
        return Examen::with(['formation', 'questions'])->get();
    }

    // 📌 Création d’un examen (lié à une formation)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'formation_id' => 'required|exists:formations,id',
        ]);

        $examen = Examen::create($validated);

        return response()->json($examen, 201);
    }

    // 📌 Afficher un examen avec ses questions et réponses possibles
    public function show($id)
    {
        $examen = Examen::with(['formation', 'questions.reponses'])->findOrFail($id);
        return response()->json($examen);
    }

    // 📌 Mise à jour d’un examen (par le formateur)
    public function update(Request $request, $id)
    {
        $examen = Examen::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'formation_id' => 'sometimes|exists:formations,id',
        ]);

        $examen->update($validated);

        return response()->json($examen);
    }

    // 📌 Lancer un examen pour un apprenant (associer dans la table pivot)
    public function passer($id)
    {
        $user = Auth::user();
        $apprenant = $user->apprenant;

        // Vérifier si l'examen existe
        $examen = Examen::with('questions.reponses')->findOrFail($id);

        // Vérifier si déjà inscrit dans examens_apprenants
        $examenApprenant = ExamenApprenant::firstOrCreate([
            'examen_id' => $examen->id,
            'apprenant_id' => $apprenant->user_id,
        ], [
            'statut' => 'en_cours',
            'date_passage' => now(),
        ]);

        return response()->json([
            'examen' => $examen,
            'examen_apprenant_id' => $examenApprenant->id
        ]);
    }

    // 📌 Soumettre les réponses d’un examen
    public function soumettre(Request $request, $id)
    {
        $user = Auth::user();
        $apprenant = $user->apprenant;

        $examen = Examen::with('questions.reponses')->findOrFail($id);
        $reponsesUtilisateur = $request->input('reponses'); // tableau [question_id => reponse_id]

        $note = 0;
        $total = $examen->questions->count();

        // Récupérer ou créer la ligne examen_apprenant
        $examenApprenant = ExamenApprenant::firstOrCreate([
            'examen_id' => $examen->id,
            'apprenant_id' => $apprenant->user_id,
        ]);

        foreach ($examen->questions as $question) {
            $reponseId = $reponsesUtilisateur[$question->id] ?? null;

            if ($reponseId) {
                $estCorrect = $question->reponses()->where('id', $reponseId)->where('est_correcte', true)->exists();

                if ($estCorrect) {
                    $note++;
                }

                // Sauvegarder la réponse de l’apprenant
                ReponseApprenant::create([
                    'examen_apprenant_id' => $examenApprenant->id,
                    'question_id' => $question->id,
                    'reponse_id' => $reponseId,
                    'reponse_donnee' => null, // si QCM
                ]);
            }
        }

        $noteFinale = round(($note / $total) * 20, 2);

        // Mise à jour du pivot
        $examenApprenant->update([
            'note' => $noteFinale,
            'statut' => 'termine',
            'date_passage' => now(),
        ]);

        return response()->json([
            'message' => 'Examen soumis avec succès',
            'note' => $noteFinale
        ]);
    }

    // 📌 Supprimer un examen
    public function destroy($id)
    {
        $examen = Examen::findOrFail($id);
        $examen->delete();

        return response()->json(['message' => 'Examen supprimé.']);
    }
}
