<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Database\Factories;

use Elegantly\Conversation\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'conversation_id' => 0,
            'content' => fake()->sentence(),
            'metadata' => [],
        ];
    }
}
