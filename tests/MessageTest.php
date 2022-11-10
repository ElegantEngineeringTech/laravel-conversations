<?php

use Carbon\Carbon;
use Finller\Conversation\Message;
use Finller\Conversation\WidgetExample;

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
                $USER_ID => now()
            ]
        ]
    ]);

    expect($message->getReadBy($USER_ID))->toBeInstanceOf(Carbon::class);
});

it('serialize and unserialize widget', function () {
    /** @var Message */
    $message = Message::factory()->make([
        'widget' => new WidgetExample("Hello", "World") // will call Attribute set function
    ]);

    expect($message->widget)->toBeInstanceOf(WidgetExample::class);
});
