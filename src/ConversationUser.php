<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User;

/**
 * @template TConversation of Conversation
 * @template TUser of User
 *
 * @property int $id
 * @property int $conversation_id
 * @property int $user_id
 * @property int $last_read_message_id
 * @property ?ArrayObject $metadata
 * @property ?Carbon $muted_at
 * @property ?Carbon $archived_at
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class ConversationUser extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'muted_at' => 'datetime',
        'archived_at' => 'datetime',
        'metadata' => AsArrayObject::class,
    ];

    /**
     * @return class-string<TUser>
     */
    public static function getModelUser(): string
    {
        return config()->string('conversations.model_user');
    }

    /**
     * @return class-string<TConversation>
     */
    public static function getModelConversation(): string
    {
        return config()->string('conversations.model_conversation');
    }

    /**
     * @return BelongsTo<TUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(static::getModelUser());
    }

    /**
     * @return BelongsTo<TConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(static::getModelConversation());
    }

    public function isMessageRead(Message|int $message): bool
    {
        $messageId = $message instanceof Message ? $message->id : $message;

        return $this->last_read_message_id && $this->last_read_message_id >= $messageId;
    }

    public function markAsDenormalizedRead(Message|int $message): static
    {
        $messageId = $message instanceof Message ? $message->id : $message;

        if (
            $this->last_read_message_id === null ||
            $this->last_read_message_id < $messageId
        ) {
            $this->last_read_message_id = $messageId;
        }

        return $this;
    }
}
