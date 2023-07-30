<?php

namespace Finller\Conversation;

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
 * @property Collection $users
 * @property ?User $owner
 * @property Collection<int, Message> $messages
 * @property Collection<int, User> $users
 * @property ?Message $latestMessage
 * @property ?Message $oldestMessage
 * @property ?Model $conversationable
 * @property ?int $conversationable_id
 * @property ?string $conversationable_type
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        /**
         * Cleanup pivot records
         * We choose to not use onCascade Delete at the database level for 3 reasons:
         * - Transactions performance
         * - Compatibility with cloud database like PlanetScale and Vitess
         * - Flexibility: so you can choose how to deal with it
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
        return $this->belongsToMany(config('conversations.model_user'))->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_user'));
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
