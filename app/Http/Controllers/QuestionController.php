<?php
// app/Http/Controllers/QuestionController.php
namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Reponse;
use App\Models\Examen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    // Liste des questions d’un examen
    public function indexByExamen(Examen $examen)
    {
        return $examen->load(['questions.reponses']);
    }

    // Créer une question pour un examen
    public function storeByExamen(Request $request, Examen $examen)
    {
        $data = $request->validate([
            'enonce' => 'required|string',
            'type'   => ['required', Rule::in(['qcm','vrai-faux','texte'])],
            'points' => 'nullable|integer|min:1',
            // Pour QCM: tableau de choix + index de la bonne réponse
            'choix'          => 'array',
            'choix.*'        => 'string|nullable',
            'bonne_reponse'  => 'nullable|integer', // index du choix correct (0..n-1)
            // Pour vrai-faux: valeur "vrai" ou "faux"
            'bonne_valeur'   => ['nullable', Rule::in(['vrai','faux'])],
            // Pour texte: réponse attendue facultative
            'reponse_attendue' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($examen, $data) {
            $question = Question::create([
                'examen_id' => $examen->id,
                'enonce'    => $data['enonce'],
                'type'      => $data['type'],
                'points'    => $data['points'] ?? 1,
            ]);

            // Créer les réponses possibles selon le type
            if ($data['type'] === 'qcm' && !empty($data['choix'])) {
                foreach ($data['choix'] as $idx => $texte) {
                    if ($texte === null || $texte === '') continue;
                    Reponse::create([
                        'question_id'  => $question->id,
                        'texte'        => $texte,
                        'est_correcte' => isset($data['bonne_reponse']) && $data['bonne_reponse'] === $idx,
                    ]);
                }
            }

            if ($data['type'] === 'vrai-faux') {
                // On crée 2 réponses: vrai & faux
                Reponse::create([
                    'question_id'  => $question->id,
                    'texte'        => 'vrai',
                    'est_correcte' => ($data['bonne_valeur'] ?? 'vrai') === 'vrai',
                ]);
                Reponse::create([
                    'question_id'  => $question->id,
                    'texte'        => 'faux',
                    'est_correcte' => ($data['bonne_valeur'] ?? 'vrai') === 'faux',
                ]);
            }

            if ($data['type'] === 'texte' && !empty($data['reponse_attendue'])) {
                // On stocke la réponse attendue comme une "réponse" correcte unique
                Reponse::create([
                    'question_id'  => $question->id,
                    'texte'        => $data['reponse_attendue'],
                    'est_correcte' => true,
                ]);
            }

            return response()->json($question->load('reponses'), 201);
        });
    }

    // Détail d’une question
    public function show(Question $question)
    {
        return $question->load('reponses');
    }

    // Modifier une question + ses réponses
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'enonce' => 'sometimes|string',
            'type'   => ['sometimes', Rule::in(['qcm','vrai-faux','texte'])],
            'points' => 'nullable|integer|min:1',

            // Mise à jour des choix (remplacement simple)
            'choix'          => 'array',
            'choix.*'        => 'string|nullable',
            'bonne_reponse'  => 'nullable|integer',
            'bonne_valeur'   => ['nullable', Rule::in(['vrai','faux'])],
            'reponse_attendue' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($question, $data) {
            $question->update([
                'enonce' => $data['enonce'] ?? $question->enonce,
                'type'   => $data['type']   ?? $question->type,
                'points' => $data['points'] ?? $question->points,
            ]);

            // Si on passe un payload de réponses → on régénère
            if (array_key_exists('choix', $data) || array_key_exists('bonne_valeur', $data) || array_key_exists('reponse_attendue', $data)) {
                // purge anciennes réponses
                $question->reponses()->delete();

                if ($question->type === 'qcm' && !empty($data['choix'])) {
                    foreach ($data['choix'] as $idx => $texte) {
                        if ($texte === null || $texte === '') continue;
                        Reponse::create([
                            'question_id'  => $question->id,
                            'texte'        => $texte,
                            'est_correcte' => isset($data['bonne_reponse']) && $data['bonne_reponse'] === $idx,
                        ]);
                    }
                }

                if ($question->type === 'vrai-faux') {
                    Reponse::create([
                        'question_id'  => $question->id,
                        'texte'        => 'vrai',
                        'est_correcte' => ($data['bonne_valeur'] ?? 'vrai') === 'vrai',
                    ]);
                    Reponse::create([
                        'question_id'  => $question->id,
                        'texte'        => 'faux',
                        'est_correcte' => ($data['bonne_valeur'] ?? 'vrai') === 'faux',
                    ]);
                }

                if ($question->type === 'texte' && !empty($data['reponse_attendue'])) {
                    Reponse::create([
                        'question_id'  => $question->id,
                        'texte'        => $data['reponse_attendue'],
                        'est_correcte' => true,
                    ]);
                }
            }

            return response()->json($question->load('reponses'));
        });
    }

    // Supprimer une question
    public function destroy(Question $question)
    {
        $question->reponses()->delete();
        $question->delete();
        return response()->json(['message' => 'Question supprimée']);
    }
}
