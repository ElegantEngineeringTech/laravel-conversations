<?php

namespace Finller\Conversation;

use Illuminate\Queue\SerializesModels;

class WidgetExample
{
    use SerializesModels;

    public function __construct(public string $title, public ?string $content)
    {
        //
    }

    public function render()
    {
        return "{$this->title} : {$this->content}";
    }
}
