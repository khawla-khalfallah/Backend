<?php

namespace App\Http\Controllers;

use App\Models\Examen;
use App\Models\Question;
use App\Models\Reponse;
use App\Models\ReponseApprenant;
use App\Models\Apprenant;
use App\Models\Formation;
use App\Models\Inscrit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExamenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Get exams based on user role
            if ($user->role === 'formateur') {
                $examens = Examen::with(['formation', 'questions'])
                    ->where('formateur_id', $user->id)
                    ->get();
            } elseif ($user->role === 'apprenant') {
                // Get exams for formations the apprenant is enrolled in
                $apprenant = Apprenant::where('user_id', $user->id)->first();
                if (!$apprenant) {
                    return response()->json(['error' => 'Apprenant profile not found'], 404);
                }

                $formationIds = Inscrit::where('apprenant_id', $apprenant->user_id)
                    ->pluck('formation_id');

                $examens = Examen::with(['formation', 'questions'])
                    ->whereIn('formation_id', $formationIds)
                    ->get();
            } else {
                $examens = Examen::with(['formation', 'questions'])->get();
            }

            return response()->json($examens);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch exams: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'formation_id' => 'required|exists:formations,id',
                'duration' => 'required|integer|min:1',
                'total_marks' => 'required|integer|min:1',
                'questions' => 'required|array|min:1',
                'questions.*.question_text' => 'required|string',
                'questions.*.type' => 'required|in:multiple_choice,true_false,text',
                'questions.*.points' => 'required|integer|min:1',
                'questions.*.reponses' => 'required_if:questions.*.type,multiple_choice|array',
                'questions.*.reponses.*.text' => 'required_with:questions.*.reponses|string',
                'questions.*.reponses.*.is_correct' => 'required_with:questions.*.reponses|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Auth::user();
            
            if (!$user || $user->role !== 'formateur') {
                return response()->json(['error' => 'Only formateurs can create exams'], 403);
            }

            DB::beginTransaction();

            // Create the exam
            $examen = Examen::create([
                'title' => $request->title,
                'description' => $request->description,
                'formation_id' => $request->formation_id,
                'formateur_id' => $user->id,
                'duration' => $request->duration,
                'total_marks' => $request->total_marks,
            ]);

            // Create questions and answers
            foreach ($request->questions as $questionData) {
                // Map frontend question types to database enum values
                $typeMapping = [
                    'multiple_choice' => 'qcm',
                    'true_false' => 'vrai-faux',
                    'text' => 'texte'
                ];
                
                $question = Question::create([
                    'examen_id' => $examen->id,
                    'enonce' => $questionData['question_text'],
                    'type' => $typeMapping[$questionData['type']] ?? 'qcm',
                    'points' => $questionData['points'],
                ]);

                if (isset($questionData['reponses']) && is_array($questionData['reponses'])) {
                    foreach ($questionData['reponses'] as $reponseData) {
                        Reponse::create([
                            'question_id' => $question->id,
                            'texte' => $reponseData['text'],
                            'est_correcte' => $reponseData['is_correct'] ?? false,
                        ]);
                    }
                }
            }

            DB::commit();

            $examen->load(['formation', 'questions.reponses']);

            return response()->json($examen, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $examen = Examen::with(['formation', 'questions.reponses'])->find($id);

            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Check access permissions
            if ($user->role === 'formateur') {
                if ($examen->formateur_id !== $user->id) {
                    return response()->json(['error' => 'Access denied'], 403);
                }
            } elseif ($user->role === 'apprenant') {
                $apprenant = Apprenant::where('user_id', $user->id)->first();
                if (!$apprenant) {
                    return response()->json(['error' => 'Apprenant profile not found'], 404);
                }

                $isEnrolled = Inscrit::where('apprenant_id', $apprenant->user_id)
                    ->where('formation_id', $examen->formation_id)
                    ->exists();

                if (!$isEnrolled) {
                    return response()->json(['error' => 'You are not enrolled in this formation'], 403);
                }
            }

            return response()->json($examen);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'formateur') {
                return response()->json(['error' => 'Only formateurs can update exams'], 403);
            }

            $examen = Examen::find($id);
            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            if ($examen->formateur_id !== $user->id) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'sometimes|required|integer|min:1',
                'total_marks' => 'sometimes|required|integer|min:1',
                'questions' => 'sometimes|array|min:1',
                'questions.*.question_text' => 'required_with:questions|string',
                'questions.*.type' => 'required_with:questions|in:multiple_choice,true_false,text',
                'questions.*.points' => 'required_with:questions|integer|min:1',
                'questions.*.reponses' => 'required_if:questions.*.type,multiple_choice|array',
                'questions.*.reponses.*.text' => 'required_with:questions.*.reponses|string',
                'questions.*.reponses.*.is_correct' => 'required_with:questions.*.reponses|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            // Update basic exam info
            $examen->update($request->only(['title', 'description', 'duration', 'total_marks']));

            // If questions are provided, update them
            if ($request->has('questions')) {
                // Delete existing questions and answers
                $examen->questions()->each(function ($question) {
                    $question->reponses()->delete();
                    $question->delete();
                });

                // Create new questions and answers
                foreach ($request->questions as $questionData) {
                    // Map frontend question types to database enum values
                    $typeMapping = [
                        'multiple_choice' => 'qcm',
                        'true_false' => 'vrai-faux',
                        'text' => 'texte'
                    ];
                    
                    $question = Question::create([
                        'examen_id' => $examen->id,
                        'enonce' => $questionData['question_text'],
                        'type' => $typeMapping[$questionData['type']] ?? 'qcm',
                        'points' => $questionData['points'],
                    ]);

                    if (isset($questionData['reponses']) && is_array($questionData['reponses'])) {
                        foreach ($questionData['reponses'] as $reponseData) {
                            Reponse::create([
                                'question_id' => $question->id,
                                'texte' => $reponseData['text'],
                                'est_correcte' => $reponseData['is_correct'] ?? false,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            // Reload the exam with updated relationships
            $examen->load(['formation', 'questions.reponses']);

            return response()->json($examen);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'formateur') {
                return response()->json(['error' => 'Only formateurs can delete exams'], 403);
            }

            $examen = Examen::find($id);
            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            if ($examen->formateur_id !== $user->id) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $examen->delete();

            return response()->json(['message' => 'Exam deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit exam answers
     */
    public function submitExam(Request $request, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'apprenant') {
                return response()->json(['error' => 'Only apprenants can submit exams'], 403);
            }

            $apprenant = Apprenant::where('user_id', $user->id)->first();
            if (!$apprenant) {
                return response()->json(['error' => 'Apprenant profile not found'], 404);
            }

            $examen = Examen::with(['questions.reponses'])->find($id);
            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Check if apprenant is enrolled in the formation
            $isEnrolled = Inscrit::where('apprenant_id', $apprenant->user_id)
                ->where('formation_id', $examen->formation_id)
                ->exists();

            if (!$isEnrolled) {
                return response()->json(['error' => 'You are not enrolled in this formation'], 403);
            }

            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'answers.*' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $totalScore = 0;
            $answers = $request->answers;

            // Delete existing answers for this exam and apprenant
            ReponseApprenant::where('apprenant_id', $apprenant->user_id)
                ->whereHas('question', function ($query) use ($id) {
                    $query->where('examen_id', $id);
                })
                ->delete();

            foreach ($answers as $questionId => $answer) {
                $question = Question::with('reponses')->find($questionId);
                if (!$question || $question->examen_id != $id) {
                    continue;
                }

                $isCorrect = false;
                $points = 0;

                if ($question->type === 'multiple_choice') {
                    $correctReponse = $question->reponses->where('is_correct', true)->first();
                    if ($correctReponse && $correctReponse->id == $answer) {
                        $isCorrect = true;
                        $points = $question->points;
                    }
                } elseif ($question->type === 'true_false') {
                    $correctReponse = $question->reponses->where('is_correct', true)->first();
                    if ($correctReponse && $correctReponse->text === $answer) {
                        $isCorrect = true;
                        $points = $question->points;
                    }
                } elseif ($question->type === 'text') {
                   
                    $points = 0;
                }

                ReponseApprenant::create([
                    'apprenant_id' => $apprenant->user_id,
                    'question_id' => $questionId,
                    'reponse_text' => is_string($answer) ? $answer : null,
                    'reponse_id' => is_numeric($answer) ? $answer : null,
                    'is_correct' => $isCorrect,
                    'points_earned' => $points,
                ]);

                $totalScore += $points;
            }

            DB::commit();

            return response()->json([
                'message' => 'Exam submitted successfully',
                'total_score' => $totalScore,
                'total_possible' => $examen->total_marks,
                'percentage' => $examen->total_marks > 0 ? round(($totalScore / $examen->total_marks) * 100, 2) : 0
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to submit exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get exam results for an apprenant
     */
    public function getResults(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $examen = Examen::with(['questions.reponses'])->find($id);
            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            if ($user->role === 'apprenant') {
                $apprenant = Apprenant::where('user_id', $user->id)->first();
                if (!$apprenant) {
                    return response()->json(['error' => 'Apprenant profile not found'], 404);
                }

                $results = ReponseApprenant::with(['question', 'reponse'])
                    ->where('apprenant_id', $apprenant->user_id)
                    ->whereHas('question', function ($query) use ($id) {
                        $query->where('examen_id', $id);
                    })
                    ->get();

                $totalScore = $results->sum('points_earned');

                return response()->json([
                    'exam' => $examen,
                    'results' => $results,
                    'total_score' => $totalScore,
                    'total_possible' => $examen->total_marks,
                    'percentage' => $examen->total_marks > 0 ? round(($totalScore / $examen->total_marks) * 100, 2) : 0
                ]);
            } elseif ($user->role === 'formateur' && $examen->formateur_id === $user->id) {
                // Get all results for this exam
                $results = ReponseApprenant::with(['question', 'reponse', 'apprenant.user'])
                    ->whereHas('question', function ($query) use ($id) {
                        $query->where('examen_id', $id);
                    })
                    ->get()
                    ->groupBy('apprenant_id');

                return response()->json([
                    'exam' => $examen,
                    'results' => $results
                ]);
            } else {
                return response()->json(['error' => 'Access denied'], 403);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch results: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get exam for taking (passer endpoint)
     */
    public function passer(string $id): JsonResponse
    {
        try {
            \Log::info("Passer endpoint called for exam ID: " . $id);
            
            $user = Auth::user();
            \Log::info("Authenticated user: " . ($user ? $user->email . " (ID: " . $user->id . ")" : "None"));
            
            if (!$user || $user->role !== 'apprenant') {
                \Log::warning("Access denied - user role: " . ($user ? $user->role : "no user"));
                return response()->json(['error' => 'Only apprenants can take exams'], 403);
            }

            $apprenant = Apprenant::where('user_id', $user->id)->first();
            if (!$apprenant) {
                \Log::error("Apprenant profile not found for user ID: " . $user->id);
                return response()->json(['error' => 'Apprenant profile not found'], 404);
            }

            $examen = Examen::with(['formation', 'questions.reponses'])->find($id);
            if (!$examen) {
                \Log::error("Exam not found with ID: " . $id);
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Check if apprenant is enrolled in the formation
            $isEnrolled = Inscrit::where('apprenant_id', $apprenant->user_id)
                ->where('formation_id', $examen->formation_id)
                ->exists();

            if (!$isEnrolled) {
                \Log::warning("User not enrolled in formation. Apprenant ID: " . $apprenant->user_id . ", Formation ID: " . $examen->formation_id);
                return response()->json(['error' => 'You are not enrolled in this formation'], 403);
            }

            \Log::info("Successfully returning exam data for ID: " . $id);
            return response()->json(['examen' => $examen]);
        } catch (\Exception $e) {
            \Log::error("Error in passer method: " . $e->getMessage() . " - " . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Submit exam answers (soumettre endpoint)
     */
    public function soumettre(Request $request, string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'apprenant') {
                return response()->json(['error' => 'Only apprenants can submit exams'], 403);
            }

            $apprenant = Apprenant::where('user_id', $user->id)->first();
            if (!$apprenant) {
                return response()->json(['error' => 'Apprenant profile not found'], 404);
            }

            $examen = Examen::with(['questions.reponses'])->find($id);
            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Check if apprenant is enrolled in the formation
            $isEnrolled = Inscrit::where('apprenant_id', $apprenant->user_id)
                ->where('formation_id', $examen->formation_id)
                ->exists();

            if (!$isEnrolled) {
                return response()->json(['error' => 'You are not enrolled in this formation'], 403);
            }

            $validator = Validator::make($request->all(), [
                'reponses' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $totalScore = 0;
            $reponses = $request->reponses;

            // Create or update ExamenApprenant record
            $examenApprenant = \App\Models\ExamenApprenant::firstOrCreate([
                'examen_id' => $id,
                'apprenant_id' => $apprenant->user_id,
            ], [
                'date_passage' => now(),
                'statut' => 'en_cours'
            ]);

            // Delete existing answers for this exam and apprenant
            \App\Models\ReponseApprenant::where('examen_apprenant_id', $examenApprenant->id)->delete();

            foreach ($reponses as $questionId => $answer) {
                $question = Question::with('reponses')->find($questionId);
                if (!$question || $question->examen_id != $id) {
                    continue;
                }

                $isCorrect = false;
                $points = 0;

                if ($question->type === 'qcm') {
                    $correctReponse = $question->reponses->where('est_correcte', true)->first();
                    if ($correctReponse && $correctReponse->id == $answer) {
                        $isCorrect = true;
                        $points = $question->points;
                    }
                } elseif ($question->type === 'vrai-faux') {
                    $correctReponse = $question->reponses->where('est_correcte', true)->first();
                    if ($correctReponse && $correctReponse->id == $answer) {
                        $isCorrect = true;
                        $points = $question->points;
                    }
                } elseif ($question->type === 'texte') {
                    // For text questions, manual grading might be needed
                    // For now, we'll store the answer but not calculate points
                    $points = 0;
                }

                \App\Models\ReponseApprenant::create([
                    'examen_apprenant_id' => $examenApprenant->id,
                    'question_id' => $questionId,
                    'reponse_donnee' => is_string($answer) ? $answer : null,
                    'reponse_id' => is_numeric($answer) ? $answer : null,
                    'est_correct' => $isCorrect,
                ]);

                $totalScore += $points;
            }

            // Update the ExamenApprenant with final score and status
            $percentage = $examen->total_marks > 0 ? round(($totalScore / $examen->total_marks) * 100, 2) : 0;
            $note = $examen->total_marks > 0 ? round(($totalScore / $examen->total_marks) * 20, 2) : 0;
            
            $examenApprenant->update([
                'note' => $note,
                'statut' => 'termine'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Exam submitted successfully',
                'total_score' => $totalScore,
                'total_possible' => $examen->total_marks,
                'percentage' => $percentage,
                'note' => $note
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to submit exam: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if apprenant has taken an exam
     */
    public function hasUserTakenExam(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'apprenant') {
                return response()->json(['error' => 'Only apprenants can check exam status'], 403);
            }

            $apprenant = Apprenant::where('user_id', $user->id)->first();
            if (!$apprenant) {
                return response()->json(['error' => 'Apprenant profile not found'], 404);
            }

            $examen = Examen::find($id);
            if (!$examen) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Check if apprenant has taken this exam
            $examenApprenant = \App\Models\ExamenApprenant::where('examen_id', $id)
                ->where('apprenant_id', $apprenant->user_id)
                ->where('statut', 'termine')
                ->first();

            $hasTaken = $examenApprenant !== null;
            $result = null;

            if ($hasTaken) {
                $note = $examenApprenant->note;
                $percentage = $examen->total_marks > 0 ? round(($note / 20) * 100, 2) : 0;

                $result = [
                    'total_score' => round(($note / 20) * $examen->total_marks, 2),
                    'total_possible' => $examen->total_marks,
                    'percentage' => $percentage,
                    'note' => $note
                ];
            }

            return response()->json([
                'has_taken' => $hasTaken,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to check exam status: ' . $e->getMessage()], 500);
        }
    }
}
