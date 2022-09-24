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

/**
 * @property Collection $users
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

    public function conversationable(): MorphTo
    {
        return $this->morphTo();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('conversations.model_user'));
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
