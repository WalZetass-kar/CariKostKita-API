<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Kost;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    /**
     * Display rooms for a specified kost.
     */
    public function index(Kost $kost): JsonResponse
    {
        $rooms = $kost->rooms()->latest()->get();

        return $this->successResponse(RoomResource::collection($rooms), 'Daftar kamar berhasil diambil');
    }

    /**
     * Store a newly created room in a kost (Owner only).
     */
    public function store(StoreRoomRequest $request, Kost $kost): JsonResponse
    {
        Gate::authorize('manage', $kost);

        $room = $kost->rooms()->create($request->validated());

        return $this->successResponse(new RoomResource($room), 'Kamar berhasil ditambahkan', 201);
    }

    /**
     * Update the specified room (Owner only).
     */
    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        Gate::authorize('manage', $room->kost);

        $room->update($request->validated());

        return $this->successResponse(new RoomResource($room), 'Data kamar berhasil diperbarui');
    }

    /**
     * Remove the specified room (Owner only).
     */
    public function destroy(Room $room): JsonResponse
    {
        Gate::authorize('manage', $room->kost);

        $room->delete();

        return $this->successResponse(null, 'Kamar berhasil dihapus');
    }
}
