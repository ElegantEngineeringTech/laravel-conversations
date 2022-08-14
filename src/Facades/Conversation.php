<?php

namespace Finller\Conversation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Finller\Conversation\Conversation
 */
class Conversation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Finller\Conversation\Conversation::class;
    }
}
