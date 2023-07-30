<?php

namespace Finller\Conversation;

use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\MarkdownConverter;

/**
 * @property ?string $content
 * @property ?array $widget
 * @property int $conversation_id
 * @property ?int $user_id
 * @property ?Carbon $created_at
 * @property ?Carbon $read_at
 * @property ?ArrayObject $metadata
 * @property Conversation $conversation
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'conversation_id',
        'user_id',
        'read_at',
        'metadata',
        'widget',
    ];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'read_at' => 'datetime',
        'widget' => 'array'
    ];

    /**
     * Update conversation updated_at when a message is saved
     */
    protected $touches = ['conversation'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_conversation'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('conversations.model_user'));
    }

    public function markAsRead(): static
    {
        $this->read_at = now();

        return $this;
    }

    public function markReadBy(int $id, ?Carbon $datetime = null): static
    {
        data_set($this->metadata, "read_by.$id", $datetime ?? now());

        return $this;
    }

    public function getReadBy(int $id): ?Carbon
    {
        $datetimeAsString = data_get($this->metadata, "read_by.$id");

        return $datetimeAsString ? Carbon::parse($datetimeAsString) : null;
    }

    public function toMarkdown(): HtmlString
    {
        $environment = new Environment(config('conversations.markdown.environment'));

        $environment->addExtension(new InlinesOnlyExtension);

        $converter = new MarkdownConverter($environment);

        return new HtmlString($converter->convert($this->content)->getContent());
    }

    public function hasWidget(): bool
    {
        return (bool) $this->getWidgetComponent();
    }

    public function getWidgetComponent(): ?string
    {
        return data_get($this->widget, "component");
    }

    public function setWidget(string $componentName, array $props): static
    {
        $this->widget = [
            'component' => $componentName,
            'props' => $props,
        ];

        return $this;
    }

    public function getWidgetProps(): array
    {
        return data_get($this->widget, 'props', []);
    }
}
