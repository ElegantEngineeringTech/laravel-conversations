<?php

namespace Finller\Conversation\Commands;

use Illuminate\Console\Command;

class ConversationCommand extends Command
{
    public $signature = 'laravel-conversations';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
