<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property ?string $uuid
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid(); // @phpstan-ignore-line
            }
        });
    }
}
