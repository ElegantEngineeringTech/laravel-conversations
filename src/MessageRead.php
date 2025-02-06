<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\Carbon;
use Elegantly\Conversation\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

/**
 * @template TUser of User
 * @template TMessage of Message
 *
 * @property int $id
 * @property ?string $origin
 * @property ?Carbon $read_at
 * @property int $message_id
 * @property TMessage $message
 * @property int $user_id
 * @property TUser $user
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class MessageRead extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * @return class-string<TUser>
     */
    public static function getModelUser(): string
    {
        return config()->string('conversations.model_user');
    }

    /**
     * @return BelongsTo<TUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(static::getModelUser());
    }

    /**
     * @return class-string<TMessage>
     */
    public static function getModelMessage(): string
    {
        return config()->string('conversations.model_message');
    }

    /**
     * @return BelongsTo<TMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(static::getModelMessage());
    }
}
