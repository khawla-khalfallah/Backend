<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Inscrit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function evaluer(Request $request, $formationId)
    {
        $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        $apprenant = $user->apprenant;

        if (!$apprenant) {
            return response()->json(['message' => 'Seuls les apprenants peuvent évaluer'], 403);
        }

        // Vérifier que l'apprenant est bien inscrit à la formation
        if (!Inscrit::where('formation_id', $formationId)
                  ->where('apprenant_id', $apprenant->user_id)
                  ->exists()) {
            return response()->json(['message' => 'Vous devez être inscrit à cette formation pour évaluer'], 403);
        }

        $evaluation = Evaluation::updateOrCreate(
            [
                'formation_id' => $formationId,
                'apprenant_id' => $apprenant->user_id
            ],
            [
                'note' => $request->note,
                'commentaire' => $request->commentaire
            ]
        );

        return response()->json([
            'message' => 'Évaluation enregistrée avec succès',
            'evaluation' => $evaluation
        ]);
    }

    public function getEvaluation($formationId)
    {
        $user = Auth::user();
        $apprenant = $user->apprenant;

        if (!$apprenant) {
            return response()->json(null);
        }

        $evaluation = Evaluation::where('formation_id', $formationId)
                              ->where('apprenant_id', $apprenant->user_id)
                              ->first();

        return response()->json($evaluation);
    }
}