<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User;

/**
 * @template TUser of User
 * @template TConversation of Conversation
 * @template TMessage of Message
 *
 * @property int $id
 * @property int $conversation_id
 * @property int $user_id
 * @property ?int $last_read_message_id
 * @property ?ArrayObject $metadata
 * @property ?CarbonInterface $muted_at
 * @property ?CarbonInterface $archived_at
 * @property CarbonInterface $updated_at
 * @property CarbonInterface $created_at
 */
class ConversationUser extends Pivot
{
    public $incrementing = true;

    protected $guarded = ['id'];

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

    /**
     * @param  TMessage|int  $message
     */
    public function hasRead(Message|int $message): bool
    {
        $messageId = $message instanceof Message ? $message->id : $message;

        return $this->last_read_message_id && $this->last_read_message_id >= $messageId;
    }

    public function markAsRead(
        Message|int $message,
        bool $force = false,
    ): static {
        $messageId = $message instanceof Message ? $message->id : $message;

        if ($force) {
            $this->last_read_message_id = $messageId;
        } elseif (
            $this->last_read_message_id === null ||
            $this->last_read_message_id < $messageId
        ) {
            $this->last_read_message_id = $messageId;
        }

        $this->save();

        return $this;
    }
}
