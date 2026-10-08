<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\SurveyRequestResource;
use App\Models\Kost;
use App\Models\Review;
use App\Models\SurveyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /**
     * Request a survey date for a kost (Pencari).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kost_id' => ['required', 'exists:kosts,id'],
            'survey_date' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $kost = Kost::findOrFail($validated['kost_id']);

        if ($user->id === $kost->owner_id) {
            return $this->errorResponse('Tidak dapat menjadwalkan survei pada kost milik sendiri.', 422);
        }

        $survey = SurveyRequest::create([
            'kost_id' => $kost->id,
            'pencari_id' => $user->id,
            'owner_id' => $kost->owner_id,
            'survey_date' => $validated['survey_date'],
            'status' => 'PENDING',
            'notes' => $validated['notes'] ?? null,
        ]);

        $survey->load(['kost', 'pencari', 'owner']);

        return $this->successResponse(
            new SurveyRequestResource($survey),
            'Jadwal survei berhasil diajukan dan menunggu konfirmasi pemilik kost',
            201
        );
    }

    /**
     * List user's scheduled surveys (Pencari).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(max($request->integer('per_page', 15), 1), 50);

        $surveys = SurveyRequest::with(['kost', 'owner', 'review'])
            ->where('pencari_id', $user->id)
            ->latest('survey_date')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar jadwal survei berhasil diambil',
            'data' => SurveyRequestResource::collection($surveys),
            'meta' => [
                'current_page' => $surveys->currentPage(),
                'last_page' => $surveys->lastPage(),
                'per_page' => $surveys->perPage(),
                'total' => $surveys->total(),
            ],
        ]);
    }

    /**
     * Cancel a survey request (Pencari).
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $survey = SurveyRequest::where('pencari_id', $request->user()->id)->findOrFail($id);

        if (in_array($survey->status, ['COMPLETED', 'CANCELLED'], true)) {
            return $this->errorResponse('Jadwal survei sudah tidak dapat dibatalkan.', 422);
        }

        $survey->update(['status' => 'CANCELLED']);

        return $this->successResponse(new SurveyRequestResource($survey), 'Survei berhasil dibatalkan');
    }

    /**
     * List incoming surveys for kost owner.
     */
    public function ownerSurveys(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        $perPage = min(max($request->integer('per_page', 15), 1), 50);

        $surveys = SurveyRequest::with(['kost', 'pencari', 'review'])
            ->where('owner_id', $ownerId)
            ->latest('survey_date')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar permintaan survei masuk berhasil diambil',
            'data' => SurveyRequestResource::collection($surveys),
            'meta' => [
                'current_page' => $surveys->currentPage(),
                'last_page' => $surveys->lastPage(),
                'per_page' => $surveys->perPage(),
                'total' => $surveys->total(),
            ],
        ]);
    }

    /**
     * Update survey status by owner (CONFIRMED, REJECTED, COMPLETED).
     */
    public function updateOwnerSurvey(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:CONFIRMED,REJECTED,COMPLETED,CANCELLED'],
            'owner_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $survey = SurveyRequest::where('owner_id', $request->user()->id)->findOrFail($id);

        $survey->update([
            'status' => $validated['status'],
            'owner_notes' => $validated['owner_notes'] ?? $survey->owner_notes,
        ]);

        $survey->load(['kost', 'pencari', 'review']);

        return $this->successResponse(new SurveyRequestResource($survey), 'Status survei berhasil diperbarui');
    }

    /**
     * Submit review and rating after a survey is completed.
     */
    public function storeReview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kost_id' => ['required', 'exists:kosts,id'],
            'survey_request_id' => ['nullable', 'exists:survey_requests,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        // Check if user already reviewed this kost
        $existingReview = Review::where('kost_id', $validated['kost_id'])
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            $existingReview->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]);
            $existingReview->load('user');

            return $this->successResponse(new ReviewResource($existingReview), 'Ulasan Anda berhasil diperbarui');
        }

        $review = Review::create([
            'kost_id' => $validated['kost_id'],
            'user_id' => $user->id,
            'survey_request_id' => $validated['survey_request_id'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        $review->load('user');

        return $this->successResponse(new ReviewResource($review), 'Terima kasih! Ulasan Anda berhasil dikirim', 201);
    }
}
