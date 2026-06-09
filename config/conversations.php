<?php

declare(strict_types=1);

use Elegantly\Conversation\Conversation;
use Elegantly\Conversation\ConversationUser;
use Elegantly\Conversation\Message;
use Elegantly\Conversation\MessageRead;
use Illuminate\Foundation\Auth\User;

return [

    /**
     * The Model used with the user_id and owner_id
     */
    'model_user' => User::class,

    'model_message' => Message::class,

    'model_conversation' => Conversation::class,

    'model_conversation_user' => ConversationUser::class,

    'model_read' => MessageRead::class,

    /**
     * When a User is deleted, his messages will be deleted
     */
    'cascade_user_delete_to_messages' => false,

    /**
     * When a User is deleted, his messages will be deleted
     */
    'cascade_conversation_delete_to_messages' => false,

    /**
     * When the parent of a conversation is deleted, the conversation is deleted
     */
    'cascade_conversationable_delete_to_conversation' => false,

    'markdown' => [
        'environment' => [
            'allow_unsafe_links' => false,
            'html_input' => 'strip',
            'external_link' => [
                'internal_hosts' => env('APP_URL', '') ?: null,
                'open_in_new_window' => true,
                'html_class' => '',
                'nofollow' => 'external',
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ],
    ],

];
