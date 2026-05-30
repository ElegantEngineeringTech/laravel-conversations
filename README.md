# Attach a chat to any model with granular read tracking

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-conversations.svg)](https://packagist.org/packages/elegantly/laravel-conversations)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-conversations.svg)](https://packagist.org/packages/elegantly/laravel-conversations)
[![Tests](https://github.com/ElegantEngineeringTech/laravel-conversations/actions/workflows/run-tests.yml/badge.svg)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions/workflows/run-tests.yml)
[![Laravel Pint](https://github.com/ElegantEngineeringTech/laravel-conversations/actions/workflows/pint.yml/badge.svg)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions/workflows/pint.yml)
[![PHPStan](https://github.com/ElegantEngineeringTech/laravel-conversations/actions/workflows/phpstan.yml/badge.svg)](https://github.com/ElegantEngineeringTech/laravel-conversations/actions/workflows/phpstan.yml)

This package gives your Laravel app a lightweight, backend-only chat system. It supports multiple users per conversation, per-message read tracking, pivot-level read pointers, and optional model attachment via polymorphic relations.

## Features

- **Multiple participants** per conversation
- **Per-message read receipts** (`MessageRead` model)
- **Fast unread queries** via denormalized `last_read_message_id` on the pivot
- **Mute and archive** conversations per user
- **Attach conversations** to any Eloquent model (e.g. a `Mission`, `Order`, or `Project`)
- **Widget messages** for rich content (e.g. custom Livewire/Vue components)
- **Markdown helper** with inline-only parsing, autolinks, and safe external links

## Requirements

- PHP ^8.1
- Laravel 11, 12, or 13

## Installation

```bash
composer require elegantly/laravel-conversations
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag="conversations-migrations"
php artisan migrate
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="conversations-config"
```

## Setup

### 1. Let users participate in conversations

Add the `ParticipateToConversations` trait to your `User` model:

```php
use Elegantly\Conversation\Concerns\ParticipateToConversations;

class User extends Authenticatable
{
    use ParticipateToConversations;
}
```

### 2. Attach a conversation to another model (optional)

If you want a conversation tied to, say, a `Mission` or `Project`, use `HasConversation`:

```php
use Elegantly\Conversation\Concerns\HasConversation;

class Mission extends Model
{
    use HasConversation;
}
```

## Usage

### Create a conversation

```php
use Elegantly\Conversation\Conversation;

$conversation = new Conversation();
$conversation->save();

// Attach participants
$conversation->users()->sync([$user->id, $user2->id]);

// Optional: attach to a parent model
$conversation->conversationable()->associate($mission);
$conversation->save();

// Optional: set an owner
$conversation->owner()->associate($admin);
$conversation->save();
```

### Send a message

```php
use Elegantly\Conversation\Message;

$message = new Message([
    'content' => 'Hey team, the deployment is live!',
]);

$message->user()->associate(auth()->user());

$conversation->send($message);
```

When you call `send()`, the package will:

1. Save the message
2. Update the conversation’s `latest_message_id` and `messaged_at`
3. Automatically mark the message as read for the sender

### Read status

#### Mark a message as read

```php
$message->markAsReadBy($user);

// Or force-update the read timestamp
$message->markAsReadBy($user, force: true);
```

#### Mark a message as unread

```php
$message->markAsUnreadBy($user);
```

#### Check read status

```php
$message->isReadBy($user);
$message->isNotReadBy($user);
$message->isReadByAnyone();
$message->isReadByAll([$user1, $user2]);

// Read timestamp
$message->getReadByAt($user);
```

#### Mark everything as read via the pivot (fast denormalized pointer)

```php
$conversation
    ->getConversationUser($user)
    ?->markAsRead($message);
```

This updates the `conversation_user.last_read_message_id` column, which makes unread-conversation queries extremely fast.

### Query unread / read conversations

#### Via Conversation scopes (checks `MessageRead` rows)

```php
use Elegantly\Conversation\Conversation;

// Conversations with unread messages for a user
Conversation::query()->unreadBy($user)->get();

// Conversations fully read by a user
Conversation::query()->readBy($user)->get();
```

#### Via the User relationship (denormalized pivot)

```php
// Fast unread queries using the pivot column
$user->denormalizedUnreadConversations()->get();

$user->denormalizedReadConversations()->get();
```

### Query unread / read messages

```php
// Messages inside a conversation
$conversation->messages()->unreadBy($user)->get();
$conversation->messages()->readBy($user)->get();

// Sent by a specific user
$conversation->messages()->byUser($user)->get();

// Not sent by a specific user
$conversation->messages()->notByUser($user)->get();
```

### Mute and archive conversations

Each participant can mute or archive a conversation via the pivot:

```php
// Mute
$conversation->users()->updateExistingPivot($user->id, ['muted_at' => now()]);

// Unmute
$conversation->users()->updateExistingPivot($user->id, ['muted_at' => null]);

// Archive
$conversation->users()->updateExistingPivot($user->id, ['archived_at' => now()]);

// Unarchive
$conversation->users()->updateExistingPivot($user->id, ['archived_at' => null]);
```

Convenience accessors on the user:

```php
$user->conversationsNotMuted()->get();
$user->conversationsMuted()->get();
$user->conversationsNotArchived()->get();
$user->conversationsArchived()->get();
```

### Widget messages

Sometimes a message is not plain text but a UI component. You can store a widget payload:

```php
$message = new Message();
$message->user()->associate($user);
$message->setWidget('invoice-widget', [
    'invoice_id' => 123,
    'total' => 499.00,
]);

$conversation->send($message);
```

Helpers on the message:

```php
$message->hasWidget();                 // true
$message->getWidgetComponent();      // 'invoice-widget'
$message->getWidgetProps();            // ['invoice_id' => 123, 'total' => 499.0, 'message' => $message]
```

### Markdown helper

Messages can be rendered as safe inline Markdown:

```php
// On an existing message
$message->toMarkdown();

// Or manually
Message::markdown($rawString);
```

This uses `league/commonmark` with inline-only, autolinks, and safe external links.

## Configuration

```php
return [
    'model_user' => User::class,
    'model_message' => Message::class,
    'model_conversation' => Conversation::class,
    'model_conversation_user' => ConversationUser::class,
    'model_read' => MessageRead::class,

    // When a User is deleted, also delete his messages
    'cascade_user_delete_to_messages' => false,

    // When a Conversation is deleted, also delete its messages
    'cascade_conversation_delete_to_messages' => false,

    // When the parent model is deleted, also delete the conversation
    'cascade_conversationable_delete_to_conversation' => false,

    'markdown' => [
        'environment' => [
            'allow_unsafe_links' => false,
        ],
    ],
];
```

## Custom models

You can extend any model and override it in the config. For example, if you need extra casts or methods on `Message`:

```php
namespace App\Models;

use Elegantly\Conversation\Message as BaseMessage;

class Message extends BaseMessage
{
    protected static function booted(): void
    {
        parent::booted();
        // your logic
    }
}
```

Then update `config/conversations.php`:

```php
'model_message' => App\Models\Message::class,
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/quentinGab/.github/blob/main/CONTRIBUTING.md) for details.

## Security

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Quentin Gabriele](https://github.com/quentinGab)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
