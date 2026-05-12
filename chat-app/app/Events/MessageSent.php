<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public Message $message;

   public function __construct(Message $message)
{
    $this->message = $message->load('user'); 
}

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->message->chat_room_id);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

   
    public function broadcastWith(): array
{
    return [
        'id' => $this->message->id,
        'chat_room_id' => $this->message->chat_room_id,
        'user_id' => $this->message->user_id,
        'message' => $this->message->message,
        'type' => $this->message->type,
        'media_path' => $this->message->media_path,

       
        'media_url' => $this->message->media_path
            ? asset('storage/' . $this->message->media_path)
            : null,

        'created_at' => $this->message->created_at,
        'user' => $this->message->user,
    ];
}
}