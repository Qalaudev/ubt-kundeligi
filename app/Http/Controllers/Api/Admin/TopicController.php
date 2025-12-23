<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TopicController extends Controller
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $topics = Topic::with('questions.answers')->get();
        return response()->json($topics);
    }

    /**
     * Public listing of topics (for home page).
     */
    public function publicIndex(): JsonResponse
    {
        $topics = Topic::select('id', 'title', 'qr_code_path')
            ->withCount('questions')
            ->get();
        return response()->json($topics);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'time_limit' => 'nullable|integer|min:1|max:600',
        ]);

        $topic = Topic::create($validated);
        return response()->json($topic, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $topic = Topic::with('questions.answers')->findOrFail($id);
        return response()->json($topic);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'time_limit' => 'nullable|integer|min:1|max:600',
        ]);

        $topic = Topic::findOrFail($id);
        $topic->update($validated);
        return response()->json($topic);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $topic = Topic::findOrFail($id);
        $topic->delete();
        return response()->json(['message' => 'Topic deleted successfully']);
    }

    /**
     * Generate QR code for a topic.
     */
    public function generateQr(Request $request, string $id): JsonResponse
    {
        $topic = Topic::findOrFail($id);
        // Use the actual domain from the request instead of config
        $baseUrl = $request->getSchemeAndHttpHost();
        $qrPath = $this->qrCodeService->generateForTopic($topic->id, $baseUrl);

        $topic->update(['qr_code_path' => $qrPath]);

        return response()->json([
            'message' => 'QR code generated successfully',
            'qr_code_path' => $qrPath,
            'qr_code_url' => asset($qrPath),
        ]);
    }
}
