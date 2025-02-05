<?php

declare(strict_types=1);

use Carbon\Carbon;
use Elegantly\Conversation\Message;

it('can mark message read by a user', function () {
    $USER_ID = 102;

    /** @var Message */
    $message = Message::factory()->make();

    $message->markReadBy($USER_ID, now());

    expect($message->getReadBy($USER_ID))->toBeTruthy();
});

it('can retreive read datetime of a user as Carbon instance', function () {
    $USER_ID = 102;

    /** @var Message */
    $message = Message::factory()->make([
        'metadata' => [
            'read_by' => [
                $USER_ID => now(),
            ],
        ],
    ]);

    expect($message->getReadBy($USER_ID))->toBeInstanceOf(Carbon::class);
});
