<?php

declare(strict_types=1);

namespace Elegantly\Conversation;

use Carbon\Carbon;
use Elegantly\Conversation\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User;

/**
 * @template TUser of User
 * @template TMessage of Message
 * @template TConversationUser of ConversationUser
 *
 * @property int $id
 * @property string $uuid
 * @property ?int $owner_id
 * @property ?TUser $owner
 * @property ?int $conversationable_id
 * @property ?string $conversationable_type
 * @property ?Model $conversationable
 * @property Collection<int, TUser> $users
 * @property Collection<int, TMessage> $messages
 * @property ?TMessage $denormalizedLatestMessage
 * @property ?TMessage $latestMessage
 * @property ?TMessage $oldestMessage
 * @property ?Carbon $messaged_at
 * @property ?int $latest_message_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class Conversation extends Model
{
    use HasFactory;
    use HasUuid;

    protected $guarded = [];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'messaged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        /**
         * Cleanup pivot records
         * We choose to not use onCascade Delete at the database level for 3 reasons:
         * - Transactions performance
         * - Compatibility with cloud database like PlanetScale and Vitess
         * - Flexibility: You can choose how to deal with it
         */
        static::deleting(function (self $conversation) {
            $conversation->users()->detach();

            if (config('conversations.cascade_conversation_delete_to_messages')) {
                $conversation->messages()->delete();
            }
        });
    }

    /**
     * @return class-string<TMessage>
     */
    public static function getModelMessage(): string
    {
        return config()->string('conversations.model_message');
    }

    /**
     * @return class-string<TUser>
     */
    public static function getModelUser(): string
    {
        return config()->string('conversations.model_user');
    }

    /**
     * @return class-string<TConversationUser>
     */
    public static function getModelConversationUser(): string
    {
        return config()->string('conversations.model_conversation_user');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function conversationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsToMany<TUser, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(static::getModelUser())
            ->using(static::getModelConversationUser())
            ->withPivot(['id', 'last_read_message_id', 'muted_at', 'archived_at', 'conversation_id', 'user_id', 'metadata'])
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<TUser, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(static::getModelUser());
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

    /**
     * @return BelongsTo<TMessage, $this>
     */
    public function denormalizedLatestMessage(): BelongsTo
    {
        return $this->belongsTo(static::getModelMessage(), 'latest_message_id');
    }

    /**
     * @param  TMessage  $message
     */
    public function sendMessage(Message $message): static
    {
        $this->messages()->save($message);

        $this->latest_message_id = $message->id;
        $this->messaged_at = $message->created_at->clone();
        $this->save();

        return $this;
    }
}
