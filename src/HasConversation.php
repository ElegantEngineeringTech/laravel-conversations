<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property ?Conversation $conversation
 */
trait HasConversation
{
    protected static function bootHasConversation(): void
    {
        static::deleting(function (Model $model) {
            if (config('conversations.cascade_conversationable_delete_to_conversation')) {
                $model->conversation()->delete();
            }
        });
    }

    public function conversation(): MorphOne
    {
        return $this->morphOne(config('conversations.model_conversation'), 'conversationable');
    }
}
