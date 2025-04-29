<?php

namespace App\Http\Controllers;

use App\Models\Question;

use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return Question::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'options' => 'required|array',
            'reponse_correcte' => 'required|string',
            'examen_id' => 'required|exists:examens,id',
        ]);

        $question = Question::create($validated);
        return response()->json($question, 201);
    }

    public function show($id)
    {
        return Question::findOrFail($id);
    }
}
