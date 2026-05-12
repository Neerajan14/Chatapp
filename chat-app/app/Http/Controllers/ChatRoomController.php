<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\Message;

class ChatRoomController extends Controller
{
    public function index()
   {
    $userId = auth()->id();
    return ChatRoom::with('users')->get()->map(function($room) use ($userId) {
        $room->unread_count = Message::where('chat_room_id', $room->id)
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
        return $room;
    });
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        return ChatRoom::create([
            'name' => $request->name
        ]);
    }

    public function destroy($id)
    {
        $room = ChatRoom::findOrFail($id);
        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}