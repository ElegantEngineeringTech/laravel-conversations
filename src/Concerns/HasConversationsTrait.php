<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Concerns;

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\ConversationUser;
use Elegantly\Conversation\Message;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @template TConversationUser of ConversationUser
 * @template TConversation of Conversation
 * @template TMessage of Message
 *
 * @property Collection<int, TConversation> $conversations
 * @property Collection<int, TMessage> $messages
 * @property ?TMessage $latestMessage
 * @property ?TConversationUser $conversationUser Conversation Pivot
 */
trait HasConversationsTrait
{
    protected static function bootHasConversationsTrait(): void
    {
        static::deleting(function ($model) {
            $model->conversations()->detach();

            if (config('conversations.cascade_user_delete_to_messages')) {
                $model->messages()->delete();
            }
        });
    }

    /**
     * @return class-string<TConversation>
     */
    public static function getModelConversation(): string
    {
        return config()->string('conversations.model_conversation');
    }

    /**
     * @return class-string<TConversationUser>
     */
    public static function getModelConversationUser(): string
    {
        return config()->string('conversations.model_conversation_user');
    }

    /**
     * @return class-string<TMessage>
     */
    public static function getModelMessage(): string
    {
        return config()->string('conversations.model_message');
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(static::getModelConversation())
            ->as('conversationUser')
            ->using(static::getModelConversationUser())
            ->withPivot(['id', 'last_read_message_id', 'muted_at', 'archived_at', 'conversation_id', 'user_id', 'metadata'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversationsUnread(): BelongsToMany
    {
        return $this->conversations()->unread($this->id);
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversationsRead(): BelongsToMany
    {
        return $this->conversations()->read($this->id);
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversationsNotMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivot('muted_at', null);
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversationsMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivot('muted_at', '!=', null);
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversationsNotArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivot('archived_at', null);
    }

    /**
     * @return BelongsToMany<TConversation, $this>
     */
    public function conversationsArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivot('archived_at', '!=', null);
    }

    /**
     * @return HasMany<TMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(static::getModelMessage());
    }

    /**
     * @return HasOne<TMessage, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(static::getModelMessage())->latestOfMany();
    }

    /**
     * @return HasOne<TMessage, $this>
     */
    public function oldestMessage(): HasOne
    {
        return $this->hasOne(static::getModelMessage())->oldestOfMany();
    }
}
