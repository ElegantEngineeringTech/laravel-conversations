<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property Collection<int, Conversation> $conversations
 * @property Collection<int, Message> $messages
 * @property ?Message $latestMessage
 */
trait ParticipateToConversations
{
    protected static function booted()
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
        return $this->belongsToMany(config('conversations.model_conversation'));
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
