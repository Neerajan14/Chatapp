<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\ChatRoom;

class MessageTest extends TestCase
{
    public function test_user_can_send_audio_message()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $room = ChatRoom::factory()->create();

        $file = UploadedFile::fake()->create(
            'voice.webm',
            100,
            'audio/webm'
        );

        $response = $this->actingAs($user)->post(
            "/api/chat-rooms/{$room->id}/messages",
            [
                'file' => $file
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('messages', [
            'user_id' => $user->id,
            'chat_room_id' => $room->id,
            'type' => 'audio'
        ]);

        Storage::disk('public')->assertExists(
            'chat-media/' . $file->hashName()
        );
    }



    public function test_user_can_send_text_message()
{
    $user = \App\Models\User::factory()->create();
    $room = \App\Models\ChatRoom::factory()->create();

    $response = $this->actingAs($user)->postJson(
        "/api/chat-rooms/{$room->id}/messages",
        ['message' => 'Hello world']
    );

    $response->assertStatus(200);

    $this->assertDatabaseHas('messages', [
        'message' => 'Hello world',
        'type' => 'text'
    ]);
}



public function test_user_can_send_image()
{
    Storage::fake('public');

    $user = User::factory()->create();
    $room = ChatRoom::factory()->create();

    
    $file = UploadedFile::fake()->create(
        'photo.jpg',
        100,
        'image/jpeg'
    );

    $response = $this->actingAs($user)->post(
        "/api/chat-rooms/{$room->id}/messages",
        ['file' => $file]
    );

    $response->assertStatus(200);

    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'chat_room_id' => $room->id,
        'type' => 'image'
    ]);
}




public function test_fetch_messages()
{
    $user = \App\Models\User::factory()->create();
    $room = \App\Models\ChatRoom::factory()->create();

    \App\Models\Message::factory()->create([
        'chat_room_id' => $room->id,
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user)->getJson(
        "/api/chat-rooms/{$room->id}/messages"
    );

    $response->assertStatus(200)
             ->assertJsonStructure([
                 '*' => ['id', 'message', 'type', 'user']
             ]);
}


public function test_user_can_mark_messages_as_read()
{
    $user = \App\Models\User::factory()->create();
    $other = \App\Models\User::factory()->create();
    $room = \App\Models\ChatRoom::factory()->create();

    $message = \App\Models\Message::create([
        'chat_room_id' => $room->id,
        'user_id' => $other->id,
        'message' => 'Test'
    ]);

    $this->actingAs($user)->post("/api/chat-rooms/{$room->id}/read");

    $this->assertDatabaseHas('message_reads', [
        'message_id' => $message->id,
        'user_id' => $user->id
    ]);
}


public function test_the_application_returns_a_successful_response()

{
    $response = $this->get('/');

    $response->assertStatus(200);
}





}