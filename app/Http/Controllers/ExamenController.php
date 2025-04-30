<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Examen;
use App\Models\Formation;

class ExamenController extends Controller
{
    public function index()
    {
        return Examen::with(['formation', 'apprenant.user'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_examen' => 'required|date',
            'note' => 'nullable|numeric',
            'formation_id' => 'required|exists:formations,id',
            'apprenant_id' => 'required|exists:apprenants,user_id',
        ]);

        $examen = Examen::create($validated);
        return response()->json($examen, 201);
    }

    public function show($id)
    {
        
        $examen = Examen::with(['formation', 'apprenant.user','questions'])->findOrFail($id);
        return response()->json($examen);

    }

    public function update(Request $request, $id)
    {
        $examen = Examen::findOrFail($id);

        $validated = $request->validate([
            'date_examen' => 'sometimes|date',
            'note' => 'nullable|numeric',
            'formation_id' => 'sometimes|exists:formations,id',
            'apprenant_id' => 'sometimes|exists:apprenants,user_id',
        ]);

        $examen->update($validated);
        return response()->json($examen);
    }



    public function passer($id)
    {
        $examen = Examen::with('formation', 'questions')->findOrFail($id);
        return response()->json($examen);
    }




    public function soumettre(Request $request, $id)
    {
        $examen = Examen::with('questions')->findOrFail($id);
        $reponsesUtilisateur = $request->input('reponses');
        $note = 0;
        $total = count($examen->questions);
    
        foreach ($examen->questions as $question) {
            if (
                isset($reponsesUtilisateur[$question->id]) &&
                $reponsesUtilisateur[$question->id] === $question->reponse_correcte
            ) {
                $note++;
            }
        }
    
        $noteFinale = round(($note / $total) * 20, 2); // sur 20
    
        // TODO : mise à jours la note dans une table `examens`, par exemple
    
    // enregistrement de la note dans la base
            $examen->note = $noteFinale;
            $examen->save();
                return response()->json(['note' => $noteFinale]);
            }
    


    public function destroy($id)
    {
        $examen = Examen::findOrFail($id);
        $examen->delete();

        return response()->json(['message' => 'Examen supprimé.']);
    }
}
 