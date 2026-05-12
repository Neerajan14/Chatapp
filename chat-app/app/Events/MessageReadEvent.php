<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageReadEvent implements ShouldBroadcast
{
    public $message_id;
    public $room_id;
    public $user;

    public function __construct(Message $message, User $user)
    {
        $this->message_id = $message->id;
        $this->room_id = $message->chat_room_id;

        $this->user = [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->room_id);
    }

    public function broadcastAs()
    {
        return 'message.read';
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->message_id,
            'room_id' => $this->room_id,
            'user' => $this->user,
        ];
    }
}