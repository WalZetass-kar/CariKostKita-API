<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Kost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    /**
     * List all chat rooms for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = min(max($request->integer('per_page', 20), 1), 50);

        $chats = Chat::with(['user', 'pencari', 'owner', 'kost', 'latestMessage'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('owner_id', $userId);
            })
            ->latest('updated_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar percakapan berhasil diambil',
            'data' => ChatResource::collection($chats),
            'meta' => [
                'current_page' => $chats->currentPage(),
                'last_page' => $chats->lastPage(),
                'per_page' => $chats->perPage(),
                'total' => $chats->total(),
            ],
        ]);
    }

    /**
     * Start a new chat or open existing chat conversation.
     */
    public function store(StartChatRequest $request): JsonResponse
    {
        $user = $request->user();
        $kost = Kost::findOrFail($request->integer('kost_id'));

        if ($user->id === $kost->owner_id) {
            return $this->errorResponse('Tidak dapat memulai chat dengan properti milik sendiri.', 422);
        }

        $chat = Chat::firstOrCreate([
            'user_id' => $user->id,
            'owner_id' => $kost->owner_id,
            'kost_id' => $kost->id,
        ]);

        if ($request->filled('message')) {
            $msgContent = $request->input('message');
            $chat->messages()->create([
                'sender_id' => $user->id,
                'message' => $msgContent,
            ]);

            $chat->update([
                'last_message' => $msgContent,
                'last_message_at' => now(),
                'unread_count_owner' => $chat->unread_count_owner + 1,
            ]);
        }

        $chat->load(['user', 'pencari', 'owner', 'kost', 'latestMessage']);

        return $this->successResponse(new ChatResource($chat), 'Percakapan berhasil dimulai', 201);
    }

    /**
     * Get messages in a chat room.
     */
    public function messages(Request $request, Chat $chat): JsonResponse
    {
        Gate::authorize('view', $chat);

        $user = $request->user();

        // Mark incoming messages as read
        $chat->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Reset unread counter for current user
        if ($user->id === $chat->owner_id) {
            $chat->update(['unread_count_owner' => 0]);
        } else {
            $chat->update(['unread_count_user' => 0]);
        }

        $perPage = min(max($request->integer('per_page', 30), 1), 100);

        $messages = $chat->messages()
            ->with('sender')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Pesan percakapan berhasil diambil',
            'data' => MessageResource::collection($messages),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a new message to a chat room.
     */
    public function sendMessage(SendMessageRequest $request, Chat $chat): JsonResponse
    {
        Gate::authorize('sendMessage', $chat);

        $user = $request->user();
        $messageContent = $request->validated()['message'];

        $message = $chat->messages()->create([
            'sender_id' => $user->id,
            'message' => $messageContent,
        ]);

        $updates = [
            'last_message' => $messageContent,
            'last_message_at' => now(),
        ];

        if ($user->id === $chat->owner_id) {
            $updates['unread_count_user'] = $chat->unread_count_user + 1;
        } else {
            $updates['unread_count_owner'] = $chat->unread_count_owner + 1;
        }

        $chat->update($updates);
        $message->load('sender');

        return $this->successResponse(new MessageResource($message), 'Pesan berhasil dikirim', 201);
    }

    /**
     * Mark all messages in chat as read explicitly.
     */
    public function markAsRead(Request $request, Chat $chat): JsonResponse
    {
        Gate::authorize('view', $chat);

        $user = $request->user();

        $chat->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($user->id === $chat->owner_id) {
            $chat->update(['unread_count_owner' => 0]);
        } else {
            $chat->update(['unread_count_user' => 0]);
        }

        return $this->successResponse(null, 'Pesan berhasil ditandai sudah dibaca');
    }
}
