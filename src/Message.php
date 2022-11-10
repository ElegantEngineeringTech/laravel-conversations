<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property ?string $content
 * @property int $conversation_id
 * @property ?int $user_id
 * @property ?Carbon $created_at
 * @property ?Carbon $read_at
 * @property ?ArrayObject $metadata
 * @property Conversation $conversation
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'conversation_id',
        'user_id',
        'read_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_conversation'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_user'));
    }

    public function markAsRead(): static
    {
        $this->read_at = now();

        return $this;
    }

    public function markReadBy(int $id, ?Carbon $datetime = null): static
    {
        data_set($this->metadata, "read_by.$id", $datetime ?? now());

        return $this;
    }

    public function getReadBy(int $id): ?Carbon
    {
        $datetimeAsString = data_get($this->metadata, "read_by.$id");

        return $datetimeAsString ? Carbon::parse($datetimeAsString) : null;
    }

    /**
     * Widget are serialized classes just like Laravel Job or Livewire Component
     */
    protected function widget(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : unserialize($value),
            set: fn ($value) => $value === null ? null : serialize($value)
        )->shouldCache();
    }
}
