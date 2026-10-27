<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class ChatController extends Controller
{
    public function show(Request $request, string $roomCode)
    {
        $room = Room::where('room_code', $roomCode)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $membership = $request->session()->get(
            "authorized_rooms.{$roomCode}"
        );

        if (
            !is_array($membership) ||
            empty($membership['uid']) ||
            empty($membership['nickname'])
        ) {
            abort(403, 'Please join this room before opening the chat.');
        }

        return view('rooms.chat', [
            'room' => $room,
            'nickname' => $membership['nickname'],
        ]);
    }

    public function firebaseToken(Request $request, string $roomCode)
    {
        $room = Room::where('room_code', $roomCode)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (!$room) {
            return response()->json([
                'message' => 'Room not found or expired.',
            ], 404);
        }

        $membership = $request->session()->get(
            "authorized_rooms.{$roomCode}"
        );

        if (
            !is_array($membership) ||
            empty($membership['uid']) ||
            empty($membership['nickname'])
        ) {
            return response()->json([
                'message' => 'Room access denied. Please join the room again.',
            ], 403);
        }

        $credentials = config('firebase.credentials');
        $databaseUrl = config('firebase.database_url');

        if (
            !is_string($credentials) ||
            !is_file($credentials) ||
            !is_string($databaseUrl) ||
            $databaseUrl === ''
        ) {
            Log::error('ShadowChat Firebase configuration is incomplete.');

            return response()->json([
                'message' => 'Firebase service is unavailable.',
            ], 503);
        }

        try {
            $auth = (new Factory())
                ->withServiceAccount($credentials)
                ->withDatabaseUri($databaseUrl)
                ->createAuth();

            $token = $auth->createCustomToken(
                (string) $membership['uid'],
                [
                    'roomCode' => (string) $roomCode,
                ]
            );

            return response()->json([
                'token' => $token->toString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ShadowChat Firebase token error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not authorize Firebase access.',
            ], 503);
        }
    }
}
