<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageReadEvent;
use App\Models\MessageRead;

class MessageController extends Controller
{
    public function index($roomId)
    {
        return Message::where('chat_room_id', $roomId)
            ->with(['user', 'seenBy'])
            ->orderBy('id', 'asc')
            ->get();
    }

    public function store(Request $request, $roomId)
{
    $request->validate([
        'message' => 'nullable|string',
        'file' => 'nullable|file|max:51200'
    ]);

    $filePath = null;
    $type = 'text';

    if ($request->hasFile('file')) {

        $file = $request->file('file');

        $filePath = $file->store('chat-media', 'public');

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        // IMAGE
        if (str_starts_with($mimeType, 'image/')) {
            $type = 'image';
        }

        // AUDIO
        elseif (
            str_starts_with($mimeType, 'audio/') ||
            in_array($extension, ['webm', 'mp3', 'wav', 'ogg'])
        ) {
            $type = 'audio';
        }

        // VIDEO
        elseif (str_starts_with($mimeType, 'video/')) {
            $type = 'video';
        }

        // FILE
        elseif (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) {
            $type = 'file';
        }
    }

    if (!$request->message && !$filePath) {
        return response()->json(['message' => 'Message or file required'], 422);
    }

    $message = Message::create([
        'chat_room_id' => $roomId,
        'user_id' => $request->user()->id,
        'message' => $request->message ?? '',
        'type' => $type,
        'media_path' => $filePath
    ]);

    $message->load('user');

    broadcast(new MessageSent($message))->toOthers();

    return response()->json($message);
}

    public function markAsRead($roomId)
    {
        $user = auth()->user();

        $messages = Message::where('chat_room_id', $roomId)
            ->where('user_id', '!=', $user->id)
            ->get();

        foreach ($messages as $message) {
            MessageRead::updateOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['read_at' => now()]
            );
            broadcast(new MessageReadEvent($message, $user))->toOthers();
        }

        return response()->json(['success' => true]);
    }

    public function destroy($roomId, $messageId)
    {
        $message = Message::where('id', $messageId)
            ->where('chat_room_id', $roomId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }
}