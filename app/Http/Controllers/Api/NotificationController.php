<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications — List notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        $unreadCount = Notification::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', false)->orWhereNull('is_read');
            })
            ->count();

        $notifications = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dimuat',
            'unread_count' => $unreadCount,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * POST /api/notifications — Create a new notification (Admin or system).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|max:50',
            'data' => 'nullable|array',
        ]);

        $notification = Notification::create([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'general',
            'is_read' => false,
            'data' => isset($validated['data']) ? json_encode($validated['data']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dibuat',
            'data' => $notification,
        ], 201);
    }

    /**
     * PATCH /api/notifications/{id}/read — Mark a notification as read.
     */
    public function markAsRead(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan',
            ], 404);
        }

        $notification->update([
            'is_read' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai telah dibaca',
            'data' => $notification,
        ]);
    }

    /**
     * POST /api/notifications/read-all — Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', false)->orWhereNull('is_read');
            })
            ->update([
                'is_read' => true,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai telah dibaca',
        ]);
    }

    /**
     * POST /api/notifications/fcm-token — Register or update FCM/Push device token.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->input('fcm_token') ?? $request->input('token') ?? $request->input('device_token');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak boleh kosong',
            ], 422);
        }

        $user->update(['fcm_token' => $token]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token berhasil diperbarui',
            'data' => [
                'user_id' => $user->id,
                'fcm_token' => $token,
            ],
        ]);
    }
}
