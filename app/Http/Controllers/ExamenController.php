<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Examen;

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
        return Examen::with(['formation', 'apprenant.user'])->findOrFail($id);
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

    public function destroy($id)
    {
        $examen = Examen::findOrFail($id);
        $examen->delete();

        return response()->json(['message' => 'Examen supprimé.']);
    }
}
 