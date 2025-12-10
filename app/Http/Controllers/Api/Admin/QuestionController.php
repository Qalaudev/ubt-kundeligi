<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $questions = Question::with('answers')->get();
        return response()->json($questions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'question_text' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        $question = Question::create([
            'topic_id' => $validated['topic_id'],
            'question_text' => $validated['question_text'],
        ]);

        foreach ($validated['answers'] as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answerData['answer_text'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        $question->load('answers');
        return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $question = Question::with('answers')->findOrFail($id);
        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'answers' => 'required|array|min:2',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
        ]);

        $question = Question::findOrFail($id);
        $question->update([
            'question_text' => $validated['question_text'],
        ]);

        // Delete existing answers
        $question->answers()->delete();
        
        // Create new answers
        foreach ($validated['answers'] as $answerData) {
            Answer::create([
                'question_id' => $question->id,
                'answer_text' => $answerData['answer_text'],
                'is_correct' => $answerData['is_correct'],
            ]);
        }

        $question->load('answers');
        return response()->json($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $question = Question::findOrFail($id);
        $question->delete();
        return response()->json(['message' => 'Question deleted successfully']);
    }
}
