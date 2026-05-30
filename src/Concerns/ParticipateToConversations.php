<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Concerns;

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\ConversationUser;
use Elegantly\Conversation\Message;
use Illuminate\Database\Eloquent\Collection;
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
trait ParticipateToConversations
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
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversations(): BelongsToMany
    {
        return $this
            ->belongsToMany(static::getModelConversation())
            ->using(static::getModelConversationUser())
            ->as('conversationUser')
            ->withPivot(['id', 'last_read_message_id', 'muted_at', 'archived_at', 'conversation_id', 'user_id', 'metadata'])
            ->withTimestamps();

    }

    /**
     * Return unread conversations using the pivot column `conversation_user.last_read_message_id`
     *
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function denormalizedUnreadConversations(): BelongsToMany
    {
        return $this
            ->conversations()
            ->whereNotNull('conversations.latest_message_id')
            ->where(function ($query) {
                $query
                    ->whereNull('conversation_user.last_read_message_id')
                    ->orWhereColumn('conversation_user.last_read_message_id', '<', 'conversations.latest_message_id');
            });

    }

    /**
     * Return read conversations using the pivot column `conversation_user.last_read_message_id`
     *
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function denormalizedReadConversations(): BelongsToMany
    {
        return $this
            ->conversations()
            ->where(function ($query) {
                $query
                    ->whereNull('conversations.latest_message_id')
                    ->orWhereColumn('conversation_user.last_read_message_id', '>=', 'conversations.latest_message_id');
            });
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsNotMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivotNull('muted_at');
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivotNotNull('muted_at');
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsNotArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivotNull('archived_at');
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivotNotNull('archived_at');
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
