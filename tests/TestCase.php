<?php

declare(strict_types=1);

namespace Elegantly\Conversation\Tests;

use Elegantly\Conversation\ConversationServiceProvider;
use Elegantly\Conversation\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as Orchestra;

use function Orchestra\Testbench\package_path;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Elegantly\\Conversation\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ConversationServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('conversations.model_user', User::class);

        Model::shouldBeStrict(true);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(package_path('tests/migrations'));

        foreach (
            [
                'create_conversations_table',
                'create_messages_table',
                'create_conversation_user_table',
                'create_reads_table',
            ] as $migration
        ) {
            (include package_path("database/migrations/{$migration}.php.stub"))->up();
        }
    }
}
