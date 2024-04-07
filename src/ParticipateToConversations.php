<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property Collection<int, Conversation> $conversations
 * @property Collection<int, Message> $messages
 * @property ?Message $latestMessage
 */
trait ParticipateToConversations
{
    protected static function booted(): void
    {
        static::deleting(function ($model) {
            $model->conversations()->detach();

            if (config('conversations.cascade_user_delete_to_messages')) {
                $model->messages()->delete();
            }
        });
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(config('conversations.model_conversation'))
            ->using(config('conversations.model_conversation_user'))
            ->withPivot(['id', 'conversation_id', 'user_id', 'muted_at', 'archived_at', 'metadata'])
            ->withTimestamps();
    }

    public function conversationsNotMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivot('muted_at', null);
    }

    public function conversationsMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivot('muted_at', '!=', null);
    }

    public function conversationsNotArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivot('archived_at', null);
    }

    public function conversationsArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivot('archived_at', '!=', null);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(config('conversations.model_message'));
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(config('conversations.model_message'))->latestOfMany();
    }

    public function oldestMessage(): HasOne
    {
        return $this->hasOne(config('conversations.model_message'))->oldestOfMany();
    }
}
