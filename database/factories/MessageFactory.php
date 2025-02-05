<?php

declare(strict_types=1);

namespace Finller\Conversation\Database\Factories;

use Finller\Conversation\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

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
