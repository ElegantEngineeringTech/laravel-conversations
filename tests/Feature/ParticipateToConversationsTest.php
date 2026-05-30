<?php

declare(strict_types=1);

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\Message;
use Elegantly\Conversation\Tests\Models\User;

it('query user unread conversations', function () {

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
        $user->denormalizedUnreadConversations()->count()
    )->toBe(0);

    expect(
        $user2->denormalizedUnreadConversations()->count()
    )->toBe(1);

    $conversation
        ->getConversationUser($user2)
        ?->markAsRead($message);

    expect(
        $user->denormalizedUnreadConversations()->count()
    )->toBe(0);

    expect(
        $user2->denormalizedUnreadConversations()->count()
    )->toBe(0);
});

it('query user read conversations', function () {

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
        $user->denormalizedReadConversations()->count()
    )->toBe(1);

    expect(
        $user2->denormalizedReadConversations()->count()
    )->toBe(0);

    $conversation
        ->getConversationUser($user2)
        ?->markAsRead($message);

    expect(
        $user->denormalizedReadConversations()->count()
    )->toBe(1);

    expect(
        $user2->denormalizedReadConversations()->count()
    )->toBe(1);
});
