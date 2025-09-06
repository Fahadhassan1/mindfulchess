<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'content',
        'original_content',
        'is_flagged',
        'flagged_reasons',
        'is_read',
        'read_at',
        'status',
        'moderated_by',
        'moderation_notes',
        'moderated_at',
    ];

    protected $casts = [
        'flagged_reasons' => 'array',
        'is_flagged' => 'boolean',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'moderated_at' => 'datetime',
    ];

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient of the message.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the moderator who reviewed this message.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope to get messages between two specific users.
     */
    public function scopeConversation($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('recipient_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)->where('recipient_id', $userId1);
        });
    }

    /**
     * Scope to get unread messages for a user.
     */
    public function scopeUnreadForUser($query, $userId)
    {
        return $query->where('recipient_id', $userId)->where('is_read', false);
    }

    /**
     * Scope to get flagged messages.
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Scope to get active messages (not deleted/hidden).
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Flag the message for moderation.
     */
    public function flagForModeration(array $reasons)
    {
        $this->update([
            'is_flagged' => true,
            'flagged_reasons' => $reasons,
        ]);
    }
}
