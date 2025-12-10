<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    /**
     * Get quiz data for a topic.
     */
    public function show(string $topicId): JsonResponse
    {
        $topic = Topic::with(['questions.answers' => function ($query) {
            $query->orderBy('id');
        }])->findOrFail($topicId);

        // Remove is_correct from answers to prevent cheating
        $questions = $topic->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'answers' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'answer_text' => $answer->answer_text,
                    ];
                }),
            ];
        });

        return response()->json([
            'topic' => [
                'id' => $topic->id,
                'title' => $topic->title,
                'time_limit' => $topic->time_limit,
            ],
            'questions' => $questions,
        ]);
    }

    /**
     * Submit quiz answers.
     */
    public function submit(Request $request, string $topicId): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_ids' => 'required|array',
            'answers.*.answer_ids.*' => 'required|exists:answers,id',
        ]);

        $topic = Topic::with('questions.answers')->findOrFail($topicId);
        
        $correctCount = 0;
        $wrongCount = 0;
        $totalPoints = 0;
        $maxPossiblePoints = 0;
        $questionResults = [];

        // Sort questions by ID to maintain order
        $questions = $topic->questions->sortBy('id')->values();

        foreach ($validated['answers'] as $answerData) {
            $question = $topic->questions->find($answerData['question_id']);
            
            if (!$question) {
                continue;
            }

            // Get all answers sorted by ID to assign letters (A, B, C, etc.)
            $allAnswers = $question->answers->sortBy('id')->values();
            
            // Get all correct answers for this question
            $correctAnswers = $question->answers->where('is_correct', true);
            $totalCorrectAnswers = $correctAnswers->count();
            // Max points: 1 correct = 1 point max, 2+ correct = 2 points max
            $maxPossiblePoints += ($totalCorrectAnswers == 1) ? 1 : 2;

            // Get selected answer IDs
            $selectedAnswerIds = $answerData['answer_ids'];
            $selectedAnswers = $question->answers->whereIn('id', $selectedAnswerIds);

            // Count how many correct answers were selected
            $correctSelected = $selectedAnswers->where('is_correct', true)->count();
            $incorrectSelected = $selectedAnswers->where('is_correct', false)->count();

            // Calculate points for this question based on the rules
            $questionPoints = $this->calculateQuestionPoints($totalCorrectAnswers, $correctSelected, $incorrectSelected);
            $totalPoints += $questionPoints;

            // Get correct answer letters (A, B, C, etc.)
            $correctAnswerLetters = [];
            foreach ($allAnswers as $index => $answer) {
                if ($answer->is_correct) {
                    $correctAnswerLetters[] = chr(65 + $index); // A=65, B=66, etc.
                }
            }

            // Get student's selected answer letters
            $selectedAnswerLetters = [];
            foreach ($allAnswers as $index => $answer) {
                if (in_array($answer->id, $selectedAnswerIds)) {
                    $selectedAnswerLetters[] = chr(65 + $index);
                }
            }

            // Check if all correct answers were found
            $allCorrectFound = ($correctSelected == $totalCorrectAnswers) && ($incorrectSelected == 0);

            // Find question index for ordering
            $questionIndex = $questions->search(function ($q) use ($question) {
                return $q->id === $question->id;
            });

            $questionResults[] = [
                'question_id' => $question->id,
                'question_number' => $questionIndex + 1,
                'correct_answers' => $correctAnswerLetters,
                'correct_answers_text' => implode(', ', $correctAnswerLetters),
                'selected_answers' => $selectedAnswerLetters,
                'selected_answers_text' => implode(', ', $selectedAnswerLetters),
                'points' => $questionPoints,
                'all_correct' => $allCorrectFound,
            ];

            // Count questions as correct/wrong for display
            if ($questionPoints == 2) {
                // Full points = fully correct
                $correctCount++;
            } elseif ($questionPoints > 0) {
                // Partial points = partially correct
                $correctCount++;
            } else {
                // No points = wrong
                $wrongCount++;
            }
        }

        // Sort question results by question number
        usort($questionResults, function ($a, $b) {
            return $a['question_number'] <=> $b['question_number'];
        });

        // Save result
        Result::create([
            'topic_id' => $topic->id,
            'user_id' => auth()->id(),
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
        ]);

        $total = $correctCount + $wrongCount;

        return response()->json([
            'correct' => $correctCount,
            'wrong' => $wrongCount,
            'total' => $total,
            'points' => $totalPoints,
            'max_points' => $maxPossiblePoints,
            'question_results' => $questionResults,
        ]);
    }

    /**
     * Calculate points for a question based on the scoring rules.
     * 
     * Rules:
     * - 1 correct answer: find 1 → 1 point, find 0 → 0 points
     * - 2 correct answers: find 1 → 1 point, find 2 → 2 points
     * - 3+ correct answers: find 1 → 0 points, find 2+ → 1 point, find all → 2 points
     * - If any incorrect answer is selected → 0 points
     * 
     * @param int $totalCorrect Total number of correct answers for the question
     * @param int $correctSelected Number of correct answers selected by student
     * @param int $incorrectSelected Number of incorrect answers selected by student
     * @return int Points earned (0, 1, or 2)
     */
    private function calculateQuestionPoints(int $totalCorrect, int $correctSelected, int $incorrectSelected): int
    {
        // If student selected any incorrect answers, they get 0 points
        if ($incorrectSelected > 0) {
            return 0;
        }

        // If no correct answers selected
        if ($correctSelected == 0) {
            return 0;
        }

        // Single correct answer: find 1 → 1 point
        if ($totalCorrect == 1) {
            return 1;
        }

        // If all correct answers found → full points (2)
        if ($correctSelected == $totalCorrect) {
            return 2;
        }

        // Partial credit cases
        if ($totalCorrect == 2) {
            // 2 correct total: find 1 → 1 point
            if ($correctSelected == 1) {
                return 1;
            }
        } elseif ($totalCorrect >= 3) {
            // 3+ correct total: 
            // - find only 1 → 0 points
            // - find 2 or more (but not all) → 1 point
            if ($correctSelected == 1) {
                return 0;
            } elseif ($correctSelected >= 2) {
                return 1;
            }
        }

        return 0;
    }
}
