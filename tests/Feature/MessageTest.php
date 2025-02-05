<?php

declare(strict_types=1);

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\Message;
use Elegantly\Conversation\Tests\Models\User;

it('query unread messages', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user = User::create();
    $user2 = User::create();

    $conversation->users()->attach($user->id);
    $conversation->users()->attach($user2->id);

    $message = new Message;
    $message->user()->associate($user);
    $message->content = 'foo';

    $conversation->send($message);

    expect(
        $conversation->messages()->unread($user2)->count()
    )->toBe(1);

    expect(
        $conversation->messages()->unread($user)->count()
    )->toBe(0);

    $message->markAsReadBy($user2);

    expect(
        $conversation->messages()->unread($user2)->count()
    )->toBe(0);
});

it('query read messages', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user = User::create();
    $user2 = User::create();

    $conversation->users()->attach($user->id);
    $conversation->users()->attach($user2->id);

    $message = new Message;
    $message->user()->associate($user);
    $message->content = 'foo';

    $conversation->send($message);

    expect(
        $conversation->messages()->read($user)->count()
    )->toBe(1);

    expect(
        $conversation->messages()->read($user2)->count()
    )->toBe(0);

    $message->markAsReadBy($user2);

    expect(
        $conversation->messages()->read($user2)->count()
    )->toBe(1);
});
