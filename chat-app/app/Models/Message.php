<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    use HasFactory;
    protected $fillable = [
        'chat_room_id', 'user_id', 'message', 'type', 'media_path'
    ];

    protected $appends = ['media_url']; 

    public function getMediaUrlAttribute()
    {
        return $this->media_path
            ? asset('storage/' . $this->media_path)
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

   public function seenBy()
{
    return $this->belongsToMany(
        User::class,
        'message_reads',
        'message_id', 
        'user_id'     
    )->withPivot('read_at')
     ->withTimestamps(); 
}
}