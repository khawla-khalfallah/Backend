<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormationController extends Controller
{
    // Constante par défaut pour le seuil de confiance
    const DEFAULT_M = 10;

    public function index(Request $request)
    {
        $m = $request->input('m', self::DEFAULT_M);
        $C = $request->input('C', Evaluation::globalAverage());
        
        $formations = Formation::with(['formateur.user', 'evaluations'])
            ->withCount('evaluations')
            ->withAvg('evaluations', 'note')
            ->get()
            ->map(function ($formation) use ($m, $C) {
                return $this->calculateBayesianStats($formation, $m, $C);
            });

        return response()->json($formations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:100',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'formateur_id' => 'required|exists:formateurs,user_id',
        ]);

        $formation = Formation::create($validated);
        return response()->json($formation, 201);
    }

    public function show($id)
    {
        $formation = Formation::with([
            'formateur.user', 
            'apprenants.user', 
            'examens', 
            'seances', 
            'videos', 
            'pdfs',
            'evaluations.apprenant.user'
        ])->findOrFail($id);
        
        // Ajout des statistiques
        $formation->moyenne = $formation->evaluations->avg('note') ?? 0;
        $formation->evaluations_count = $formation->evaluations->count();
        
        return response()->json($formation);
    }

    public function update(Request $request, $id)
    {
        $formation = Formation::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'prix' => 'nullable|numeric',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        $formation->update($validated);
        return response()->json($formation);
    }

    public function destroy($id)
    {
        Formation::destroy($id);
        return response()->json(['message' => 'Formation supprimée avec succès.']);
    }

    public function getApprenants($id)
    {
        $formation = Formation::with('apprenants.user')->findOrFail($id);
        return response()->json($formation->apprenants);
    }

    public function chercherParTitre(Request $request)
    {
        $request->validate(['titre' => 'required|string']);
        
        $formations = Formation::where('titre', 'LIKE', "%{$request->titre}%")
            ->with(['formateur.user', 'evaluations'])
            ->get()
            ->map(function ($formation) {
                return $this->calculateBayesianStats($formation);
            });

        return response()->json($formations);
    }

    /**
     * Endpoint spécifique pour le dashboard bayésien
     */
    
    public function getBayesianRanking(Request $request)
{
    $m = $request->input('m', 10);
    $C = $request->input('C', Evaluation::avg('note') ?? 3);

    $formations = Formation::with(['formateur.user', 'evaluations'])
        ->withCount('evaluations')
        ->get()
        ->map(function ($formation) use ($m, $C) {
            $v = $formation->evaluations_count;
            $R = $formation->evaluations->avg('note') ?? 0;
            
            return [
                'id' => $formation->id,
                'titre' => $formation->titre,
                'description' => $formation->description,
                'formateur' => $formation->formateur,
                'evaluations_count' => $v,
                'average_rating' => $R,
                'bayesian_score' => $v > 0 
                    ? ($v / ($v + $m)) * $R + ($m / ($v + $m)) * $C
                    : $C
            ];
        })
        ->sortByDesc('bayesian_score')
        ->values();

    return response()->json([
        'formations' => $formations,
        'meta' => [
            'm' => $m,
            'C' => $C,
            'global_avg' => Evaluation::avg('note') ?? 3
        ]
    ]);
}
    /**
     * Calcule les statistiques bayésiennes pour une formation
     */
    private function calculateBayesianStats($formation, $m = self::DEFAULT_M, $C = null)
    {
        $C = $C ?? Evaluation::globalAverage();
        $v = $formation->evaluations_count ?? $formation->evaluations->count();
        $R = $formation->evaluations_avg_note ?? $formation->evaluations->avg('note') ?? 0;

        $formation->bayesian_score = $this->calculateBayesianScore($v, $R, $m, $C);
        $formation->bayesian_calculation = "($v/($v+$m))×$R + ($m/($v+$m))×$C";
        
        return $formation;
    }
    public static function globalAverage()
    {
        return (float) Evaluation::avg('note') ?? 3.0;
    }
    /**
     * Formule bayésienne de base
     */
    private function calculateBayesianScore($v, $R, $m, $C)
    {
        if ($v == 0) return $C;
        return ($v / ($v + $m)) * $R + ($m / ($v + $m)) * $C;
    }
    public function getByFormateur($id)
    {
        return Formation::where('formateur_id', $id)->get();
    }

}