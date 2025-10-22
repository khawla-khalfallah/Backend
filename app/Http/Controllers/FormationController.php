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
 // Vérification que date_fin >= aujourd'hui
    if (!empty($validated['date_fin']) && $validated['date_fin'] < now()->toDateString()) {
        return response()->json(['message' => 'La date de fin doit être supérieure ou égale à aujourd’hui.'], 422);
    }
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
         // Vérification que date_fin >= aujourd'hui
    if (!empty($validated['date_fin']) && $validated['date_fin'] < now()->toDateString()) {
        return response()->json(['message' => 'La date de fin doit être supérieure ou égale à aujourd’hui.'], 422);
    }


        $formation->update($validated);
        return response()->json($formation);
    }

    
    public function destroy($id)
    {
        $formation = Formation::findOrFail($id);

        // Supprimer aussi les inscrits liés (au cas où cascade DB ne marche pas)
        $formation->inscrits()->delete();

        $formation->delete();

        return response()->json(['message' => 'Formation supprimée avec succès.']);
    }

    public function getApprenants($id)
{
    $formation = Formation::with([
        'apprenants.user',
        'apprenants.examens' => function ($query) use ($id) {
            $query->where('formation_id', $id); // récupérer examens liés à cette formation
        }
    ])->findOrFail($id);

    // Transformer la réponse pour inclure directement la note
    $apprenants = $formation->apprenants->map(function ($apprenant) {
        $note = null;

        // Vérifier la note dans la pivot "examens_apprenants"
        if ($apprenant->examens->isNotEmpty()) {
            $note = $apprenant->examens->first()->pivot->note;
        }

        return [
            'id' => $apprenant->id,
            'nom' => $apprenant->user->nom,
            'prenom' => $apprenant->user->prenom,
            'email' => $apprenant->user->email,
            'note' => $note
        ];
    });
 // ✅ Trier par note (les nulls passent en bas)
    $apprenants = $apprenants->sortByDesc(function ($a) {
        return $a['note'] ?? -1; // si note null → traité comme -1
    })->values(); // values() pour réindexer proprement
    return response()->json($apprenants);
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