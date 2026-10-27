<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|max:30',
        ]);

        do {
            $roomCode = (string) random_int(100000, 999999);
        } while (Room::where('room_code', $roomCode)->exists());

        $room = Room::create([
            'room_code' => $roomCode,
            'status' => 'active',
            'expires_at' => now()->addHours(24),
        ]);

        $this->authorizeRoom($request, $roomCode, $request->nickname);
        $this->saveRecentRoom($request, $roomCode);

        return redirect()->route('rooms.created', $room->room_code);
    }

    public function created($roomCode)
    {
        $room = Room::where('room_code', $roomCode)
            ->where('status', 'active')
            ->firstOrFail();

        return view('rooms.created', compact('room'));
    }

    public function join(Request $request)
    {
        $request->validate([
            'room_code' => 'required|digits:6',
            'nickname' => 'required|string|max:30',
        ]);

        $room = Room::where('room_code', $request->room_code)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (! $room) {
            return back()
                ->withErrors([
                    'room_code' => 'Room not found or has already ended.',
                ])
                ->withInput();
        }

        $this->authorizeRoom($request, $room->room_code, $request->nickname);
        $this->saveRecentRoom($request, $room->room_code);

        return redirect()->route('rooms.chat', $room->room_code);
    }

    public function info($roomCode)
    {
        $room = Room::where('room_code', $roomCode)
            ->where('status', 'active')
            ->firstOrFail();

        return view('rooms.info', compact('room'));
    }

    private function authorizeRoom(
        Request $request,
        string $roomCode,
        string $nickname
    ): void {
        $request->session()->regenerate();

        $memberships = $request->session()->get('authorized_rooms', []);
        $memberships[$roomCode] = [
            'nickname' => $nickname,
            'uid' => (string) \Illuminate\Support\Str::uuid(),
        ];

        $request->session()->put('authorized_rooms', $memberships);
        $request->session()->put('nickname', $nickname);
    }

    private function saveRecentRoom(Request $request, string $roomCode): void
    {
        $recentChats = $request->session()->get('recent_chats', []);

        $recentChats = array_values(array_filter(
            $recentChats,
            fn ($chat) => (string) ($chat['roomCode'] ?? '') !== $roomCode
        ));

        array_unshift($recentChats, [
            'roomCode' => $roomCode,
            'lastMessage' => '',
            'updatedAt' => now()->format('h:i A'),
            'onlineCount' => 0,
        ]);

        $request->session()->put(
            'recent_chats',
            array_slice($recentChats, 0, 20)
        );
    }
}