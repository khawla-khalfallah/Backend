<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeanceController extends Controller
{
    public function index()
    {
        return Seance::with('formation')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titreSeance' => 'required|string|max:100',
            'date' => 'required|date',
            'heureDebut' => 'required',
            'heureFin' => 'required',
            'lienRoom' => 'required|string|max:255',
            'formation_id' => 'required|exists:formations,id',
        ]);

        $seance = Seance::create($validated);
        return response()->json($seance, 201);
    }

    public function show($id)
    {
        return Seance::with('formation')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $seance = Seance::findOrFail($id);

        $validated = $request->validate([
            'titreSeance' => 'sometimes|string|max:100',
            'date' => 'sometimes|date',
            'heureDebut' => 'sometimes',
            'heureFin' => 'sometimes',
            'lienRoom' => 'sometimes|string|max:255',
            'formation_id' => 'sometimes|exists:formations,id',
        ]);

        $seance->update($validated);
        return response()->json($seance);
    }

    public function destroy($id)
    {
        $seance = Seance::findOrFail($id);
        $seance->delete();

        return response()->json(['message' => 'Séance supprimée.']);
    }
}
