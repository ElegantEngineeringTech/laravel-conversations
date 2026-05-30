<?php

declare(strict_types=1);

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\Message;
use Elegantly\Conversation\Tests\Models\User;

it('can send a message', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user = User::create();

    $conversation->users()->attach($user->id);

    $message = new Message;
    $message->user()->associate($user);
    $message->content = 'foo';

    $conversation->send($message);

    expect($conversation->latest_message_id)->toBe($message->id);
    expect($conversation->messaged_at == $message->created_at)->toBe(true);

    expect($conversation->getConversationUser($user)->hasRead($message))->toBe(true);

    expect($message->isReadBy($user))->toBe(true);
});

it('can read a message', function () {

    $conversation = new Conversation;
    $conversation->save();

    $user = User::create();
    $user2 = User::create();

    $conversation->users()->sync([$user, $user2]);

    $message = new Message;
    $message->user()->associate($user);
    $message->content = 'foo';

    $conversation->send($message);

    expect($message->isReadBy($user))->toBe(true);

    expect($message->isReadBy($user2))->toBe(false);

    $message->markAsReadBy($user2);

    expect($message->isReadBy($user2))->toBe(true);

});

it('query unread conversations', function () {

    $conversation = new Conversation;
    $conversation->save();

    /** @var User */
    $user = User::create();
    /** @var User */
    $user2 = User::create();

    $conversation->users()->sync([$user, $user2]);

    $message = $conversation->send(new Message([
        'user_id' => $user->id,
        'content' => 'foo',
    ]));

    expect(
        Conversation::query()->unreadBy($user)->count()
    )->toBe(0);

    expect(
        Conversation::query()->unreadBy($user2)->count()
    )->toBe(1);

    $message->markAsReadBy($user2);

    expect(
        Conversation::query()->unreadBy($user)->count()
    )->toBe(0);

    expect(
        Conversation::query()->unreadBy($user2)->count()
    )->toBe(0);

    $message2 = $conversation->send(new Message([
        'user_id' => $user2->id,
        'content' => 'bar',
    ]));

    expect(
        Conversation::query()->unreadBy($user)->count()
    )->toBe(1);

    expect(
        Conversation::query()->unreadBy($user2)->count()
    )->toBe(0);

    $message2->markAsReadBy($user);

    expect(
        Conversation::query()->unreadBy($user)->count()
    )->toBe(0);

    expect(
        Conversation::query()->unreadBy($user2)->count()
    )->toBe(0);

});

it('query read conversations', function () {

    $conversation = new Conversation;
    $conversation->save();

    /** @var User */
    $user = User::create();
    /** @var User */
    $user2 = User::create();

    $conversation->users()->sync([$user, $user2]);

    $message = $conversation->send(new Message([
        'user_id' => $user->id,
        'content' => 'foo',
    ]));

    expect(
        Conversation::query()->readBy($user)->count()
    )->toBe(1);

    expect(
        Conversation::query()->readBy($user2)->count()
    )->toBe(0);

    $message->markAsReadBy($user2);

    expect(
        Conversation::query()->readBy($user)->count()
    )->toBe(1);

    expect(
        Conversation::query()->readBy($user2)->count()
    )->toBe(1);

    $message2 = $conversation->send(new Message([
        'user_id' => $user2->id,
        'content' => 'bar',
    ]));

    expect(
        Conversation::query()->readBy($user)->count()
    )->toBe(0);

    expect(
        Conversation::query()->readBy($user2)->count()
    )->toBe(1);

    $message2->markAsReadBy($user);

    expect(
        Conversation::query()->readBy($user)->count()
    )->toBe(1);

    expect(
        Conversation::query()->readBy($user2)->count()
    )->toBe(1);
});
