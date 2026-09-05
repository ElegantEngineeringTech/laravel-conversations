<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\CarbonInterface;
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
 * @property ?CarbonInterface $read_at
 * @property ?CarbonInterface $notified_at
 * @property ?string $notified_channel
 * @property int $message_id
 * @property int $user_id
 * @property CarbonInterface $updated_at
 * @property CarbonInterface $created_at
 * @property-read TUser $user
 * @property-read TMessage $message
 */
class MessageRead extends Model
{
    use HasUuid;

    protected $guarded = ['id', 'uuid'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    /**
     * @return class-string<TUser>
     */
    public static function getModelUser(): string
    {
        return config()->string('conversations.model_user');
    }

    /**
     * @return class-string<TMessage>
     */
    public static function getModelMessage(): string
    {
        return config()->string('conversations.model_message');
    }

    /**
     * @return BelongsTo<TUser, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(static::getModelUser());
    }

    /**
     * @return BelongsTo<TMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(static::getModelMessage());
    }
}
