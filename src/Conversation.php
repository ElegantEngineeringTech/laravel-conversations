<?php

namespace Finller\Conversation;

use Carbon\Carbon;
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
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property Collection $users
 * @property ?int $owner_id
 * @property ?User $owner
 * @property Collection<int, Message> $messages
 * @property Collection<int, User> $users
 * @property ?Message $latestMessage
 * @property ?Message $oldestMessage
 * @property ?Model $conversationable
 * @property ?int $conversationable_id
 * @property ?string $conversationable_type
 * @property ?Carbon $messaged_at
 * @property ?int $latest_message_id
 */
class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'messaged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Conversation $conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });

        /**
         * Cleanup pivot records
         * We choose to not use onCascade Delete at the database level for 3 reasons:
         * - Transactions performance
         * - Compatibility with cloud database like PlanetScale and Vitess
         * - Flexibility: You can choose how to deal with it
         */
        static::deleting(function (Conversation $conversation) {
            $conversation->users()->detach();

            if (config('conversations.cascade_conversation_delete_to_messages')) {
                $conversation->messages()->delete();
            }
        });
    }

    public function conversationable(): MorphTo
    {
        return $this->morphTo();
    }

    public function users(): BelongsToMany
    {
        // @phpstan-ignore-next-line
        return $this->belongsToMany(config('conversations.model_user'))
            ->using(config('conversations.model_conversation_user'))
            ->withPivot(['id', 'conversation_id', 'user_id', 'muted_at', 'archived_at', 'metadata'])
            ->withTimestamps()
            ->withTrashed();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_user'))->withTrashed(); // @phpstan-ignore-line
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

    public function denormalizedLatestMessage(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_message'), 'latest_message_id');
    }

    public function send(Message $message): static
    {
        $this->messages()->save($message);

        $this->latest_message_id = $message->id;
        $this->messaged_at = $message->created_at->clone();
        $this->save();

        return $this;
    }
}
