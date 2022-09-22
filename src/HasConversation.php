<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property ?Conversation $conversation
 */
trait HasConversation
{
    protected static function bootHasConversation()
    {
        // static::deleting(function (Model $model) {
        //     $model->conversation()->delete(); // @phpstan-ignore-line
        // });
    }

    public function conversation(): MorphOne
    {
        return $this->morphOne(Conversation::class, 'conversationable');
    }
}
