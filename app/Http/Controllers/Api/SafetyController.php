<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Kost;
use App\Models\Report;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafetyController extends Controller
{
    /**
     * Block another user in chat.
     */
    public function blockUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blocked_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $currentUser = $request->user();

        if ($currentUser->id === (int) $validated['blocked_id']) {
            return $this->errorResponse('Tidak dapat memblokir akun Anda sendiri.', 422);
        }

        $block = UserBlock::firstOrCreate(
            [
                'blocker_id' => $currentUser->id,
                'blocked_id' => $validated['blocked_id'],
            ],
            [
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return $this->successResponse([
            'id' => $block->id,
            'blocked_id' => $block->blocked_id,
        ], 'Pengguna berhasil diblokir.');
    }

    /**
     * Report another user for violation or harassment.
     */
    public function reportUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reported_user_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $currentUser = $request->user();

        if ($currentUser->id === (int) $validated['reported_user_id']) {
            return $this->errorResponse('Tidak dapat melaporkan akun Anda sendiri.', 422);
        }

        $report = UserReport::create([
            'reporter_id' => $currentUser->id,
            'reported_user_id' => $validated['reported_user_id'],
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'Baru',
        ]);

        return $this->successResponse([
            'id' => $report->id,
            'status' => $report->status,
        ], 'Laporan pengguna berhasil dikirim dan akan segera ditindaklanjuti', 201);
    }

    /**
     * Report an invalid kost listing (fake price/photos/location).
     */
    public function reportKost(StoreReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['reporter_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $report = Report::create($validated);
        $report->load(['reporter', 'kost']);

        return $this->successResponse(
            new ReportResource($report),
            'Laporan properti berhasil dikirim dan akan ditinjau oleh tim moderasi',
            201
        );
    }
}
