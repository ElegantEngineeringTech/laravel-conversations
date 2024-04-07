<?php

namespace Finller\Conversation;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property Carbon $muted_at
 * @property Carbon $archived_at
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
}
