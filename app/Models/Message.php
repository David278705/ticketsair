<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model 
{
    protected $fillable = [
        'from_user_id', 
        'to_user_id', 
        'thread_id',
        'body', 
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    public function from() 
    { 
        return $this->belongsTo(User::class, 'from_user_id'); 
    }

    public function to() 
    { 
        return $this->belongsTo(User::class, 'to_user_id'); 
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    // Scopes útiles
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('from_user_id', $userId)
              ->orWhere('to_user_id', $userId);
        });
    }

    public function scopeConversationBetween($query, $user1Id, $user2Id)
    {
        return $query->where(function ($q) use ($user1Id, $user2Id) {
            $q->where(function ($subQ) use ($user1Id, $user2Id) {
                $subQ->where('from_user_id', $user1Id)
                     ->where('to_user_id', $user2Id);
            })->orWhere(function ($subQ) use ($user1Id, $user2Id) {
                $subQ->where('from_user_id', $user2Id)
                     ->where('to_user_id', $user1Id);
            });
        });
    }
}
