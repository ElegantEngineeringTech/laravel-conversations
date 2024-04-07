<?php

// config for Finller/Conversation

use Finller\Conversation\Conversation;
use Finller\Conversation\ConversationUser;
use Finller\Conversation\Message;
use Illuminate\Foundation\Auth\User;

return [

    /**
     * The Model used with the user_id and owner_id
     */
    'model_user' => User::class,

    'model_message' => Message::class,

    'model_conversation' => Conversation::class,

    'model_conversation_user' => ConversationUser::class,

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
        ],
    ],

];
