<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    public function users()
    {
        return $this->belongsToMany(config('conversations.model_user'));
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_user'));
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function oldestMessage()
    {
        return $this->hasOne(Message::class)->oldestOfMany();
    }
}
