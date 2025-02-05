<?php

declare(strict_types=1);

namespace Finller\Conversation\Concerns;

use Finller\Conversation\Conversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @template TConversation of Conversation
 *
 * @property ?TConversation $conversation
 */
trait HasConversation
{
    protected static function bootHasConversation(): void
    {
        static::deleting(function (Model $model) {
            if (config('conversations.cascade_conversationable_delete_to_conversation')) {
                $model->conversation->delete();
            }
        });
    }

    /**
     * @return class-string<TConversation>
     */
    public static function getModelConversation(): string
    {
        return config()->string('conversations.model_conversation');
    }

    /**
     * @return MorphOne<TConversation, $this>
     */
    public function conversation(): MorphOne
    {
        return $this->morphOne(static::getModelConversation(), 'conversationable');
    }
}
