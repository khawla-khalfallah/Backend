<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Formation;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    // 📌 Récupérer toutes les vidéos
    public function index()
    {
        return Video::with('formation')->get();
    }

    // 📌 Récupérer les vidéos d'un formateur
    public function getByFormateur($formateurId)
    {
        return Video::whereHas('formation', function ($query) use ($formateurId) {
            $query->where('formateur_id', $formateurId);
        })
        ->with('formation:id,titre,formateur_id')
        ->get(['id', 'titre', 'url', 'description', 'formation_id']);
    }

    // 📌 Récupérer les vidéos d'une formation
    public function getByFormation($formationId)
    {
        return Video::where('formation_id', $formationId)->get();
    }

    // 📌 Ajouter une vidéo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'url' => 'required|string',
            'description' => 'nullable|string', // ✅ ajouté
            'formation_id' => 'required|exists:formations,id',
        ]);

        $user = $request->user();
        $formation = Formation::findOrFail($validated['formation_id']);

        if ($formation->formateur_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $video = Video::create([
            'titre' => $validated['titre'],
            'url' => $validated['url'],
            'description' => $validated['description'] ?? null, // ✅ pris en compte
            'formation_id' => $formation->id,
        ]);

        return response()->json($video, 201);
    }

    // 📌 Modifier une vidéo
    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $user = $request->user();

        if ($video->formation->formateur_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'url' => 'sometimes|string',
            'description' => 'nullable|string', // ✅ ajouté
            'formation_id' => 'sometimes|exists:formations,id',

        ]);
        if (isset($validated['formation_id'])) {
                $newFormation = Formation::findOrFail($validated['formation_id']);
                if ($newFormation->formateur_id !== $user->id) {
                    return response()->json(['error' => 'Vous ne pouvez pas assigner une vidéo à cette formation'], 403);
                }
                $video->formation_id = $validated['formation_id'];
        }
        if (isset($validated['titre'])) {
            $video->titre = $validated['titre'];
        }
        if (isset($validated['url'])) {
            $video->url = $validated['url'];
        }
        if (array_key_exists('description', $validated)) {
            $video->description = $validated['description']; // ✅ pris en compte
        }

        $video->save();

        return response()->json($video);
    }

    // 📌 Supprimer une vidéo
    public function destroy(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $user = $request->user();

        if ($video->formation->formateur_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $video->delete();

        return response()->json(['message' => 'Vidéo supprimée avec succès']);
    }
}
