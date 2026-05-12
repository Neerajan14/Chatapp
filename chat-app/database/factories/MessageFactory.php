<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Message;
use App\Models\User;
use App\Models\ChatRoom;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'chat_room_id' => ChatRoom::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->sentence,
            'type' => 'text',
            'media_path' => null,
        ];
    }
}