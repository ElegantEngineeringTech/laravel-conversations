<?php

declare(strict_types=1);

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\Message;
use Elegantly\Conversation\Tests\Models\User;

it('can have multiple participants', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user1 = User::create();
    $user2 = User::create();

    $conversation->users()->attach($user1->id);
    $conversation->users()->attach($user2->id);

    expect($conversation->users)->toHaveLength(2);
});

it('can send a message', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user = User::create();

    $conversation->users()->attach($user->id);

    $message = new Message;
    $message->user()->associate($user);
    $message->content = 'foo';

    $conversation->sendMessage($message);

    expect($conversation->latest_message_id)->toBe($message->id);
    expect($conversation->messaged_at == $message->created_at)->toBe(true);
});

it('can read a message', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user = User::create();
    $user2 = User::create();

    $conversation->users()->sync([$user->id, $user2]);

    $message = new Message;
    $message->user()->associate($user);
    $message->content = 'foo';

    $conversation->sendMessage($message);

    expect($message->isReadBy($user))->toBe(true);

    expect($message->isReadBy($user2))->toBe(false);
    expect($message->reads)->toHaveLength(0);

    $message->markAsReadBy($user2);

    expect($message->isReadBy($user2))->toBe(true);

    expect($message->reads)->toHaveLength(1);
});
