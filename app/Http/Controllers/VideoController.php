<?php


namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        return Video::with('formation')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:100',
            'url' => 'required|string|max:255',
            'description' => 'nullable|string',
            'formation_id' => 'required|exists:formations,id',
        ]);

        $video = Video::create($validated);
        return response()->json($video, 201);
    }

    public function show($id)
    {
        return Video::with('formation')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:100',
            'url' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'formation_id' => 'sometimes|exists:formations,id',
        ]);

        $video->update($validated);
        return response()->json($video);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        $video->delete();

        return response()->json(['message' => 'Vidéo supprimée.']);
    }
}
