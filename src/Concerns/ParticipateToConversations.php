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
use Illuminate\Support\Facades\DB;

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
        return $this->belongsToMany(static::getModelConversation())
            ->as('conversationUser')
            ->using(static::getModelConversationUser())
            ->withPivot(['id', 'last_read_message_id', 'muted_at', 'archived_at', 'conversation_id', 'user_id', 'metadata'])
            ->withTimestamps();
    }

    /**
     * Return unread conversations using the `message_reads` table
     *
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsUnread(): BelongsToMany
    {
        return $this->conversations()->unread($this->id);
    }

    /**
     * Return read conversations using the `message_reads` table
     *
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsRead(): BelongsToMany
    {
        return $this->conversations()->read($this->id);
    }

    /**
     * Return unread conversations using the pivot column `conversation_user.last_read_message_id`
     *
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function denormalizedConversationsUnread(): BelongsToMany
    {
        return $this
            ->conversations()
            ->where('conversations.latest_message_id', '!=', null)
            ->where(function ($query) {
                return $query
                    ->where('conversation_user.last_read_message_id', '=', null)
                    ->orWhere('conversation_user.last_read_message_id', '<', DB::raw('conversations.latest_message_id'));
            });

    }

    /**
     * Return read conversations using the pivot column `conversation_user.last_read_message_id`
     *
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function denormalizedConversationsRead(): BelongsToMany
    {
        return $this
            ->conversations()
            ->where(function ($query) {
                $query
                    ->where('conversations.latest_message_id', '=', null)
                    ->orWhere('conversation_user.last_read_message_id', '>=', DB::raw('conversations.latest_message_id'));
            });
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsNotMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivot('muted_at', null);
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsMuted(): BelongsToMany
    {
        return $this->conversations()->wherePivot('muted_at', '!=', null);
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
     */
    public function conversationsNotArchived(): BelongsToMany
    {
        return $this->conversations()->wherePivot('archived_at', null);
    }

    /**
     * @return BelongsToMany<TConversation, $this, TConversationUser, 'conversationUser'>
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
