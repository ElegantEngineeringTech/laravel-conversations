<?php

namespace Finller\Conversation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ConversationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-conversations')
            ->hasMigrations([
                'create_conversations_table',
                'create_messages_table',
                'create_conversation_user_table',
                'add_uuid_to_conversations_table',
            ])
            ->hasConfigFile();
    }
}
