<?php

declare(strict_types=1);

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\Message;
use Elegantly\Conversation\Tests\Models\User;

it('query unread messages', function () {

    $conversation = new Conversation;
    $conversation->save();

    /** @var User */
    $user = User::create();
    /** @var User */
    $user2 = User::create();

    $conversation->users()->sync([$user, $user2]);

    $conversation->send($message = new Message([
        'user_id' => $user->id,
        'content' => 'foo',
    ]));

    expect(
        $conversation->messages()->unreadBy($user2)->count()
    )->toBe(1);

    expect(
        $conversation->messages()->unreadBy($user)->count()
    )->toBe(0);

    $message->markAsReadBy($user2);

    expect(
        $conversation->messages()->unreadBy($user2)->count()
    )->toBe(0);

    expect(
        $conversation->messages()->unreadBy($user)->count()
    )->toBe(0);
});

it('query read messages', function () {

    $conversation = new Conversation;
    $conversation->save();

    /** @var User */
    $user = User::create();
    /** @var User */
    $user2 = User::create();

    $conversation->users()->sync([$user, $user2]);

    $message = new Message([
        'user_id' => $user->id,
        'content' => 'foo',
    ]);

    $conversation->send($message);

    expect(
        $conversation->messages()->readBy($user2)->count()
    )->toBe(0);

    expect(
        $conversation->messages()->readBy($user)->count()
    )->toBe(1);

    $message->markAsReadBy($user2);

    expect(
        $conversation->messages()->readBy($user2)->count()
    )->toBe(1);

    expect(
        $conversation->messages()->readBy($user)->count()
    )->toBe(1);
});
