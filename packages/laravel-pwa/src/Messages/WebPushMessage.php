<?php

namespace BeegoodIT\LaravelPwa\Messages;

class WebPushMessage
{
    protected ?string $title = null;

    protected ?string $body = null;

    protected ?string $icon = null;

    protected ?string $badge = null;

    protected ?string $image = null;

    protected ?string $tag = null;

    protected array $data = [];

    protected array $actions = [];

    protected bool $requireInteraction = false;

    protected bool $renotify = false;

    protected bool $silent = false;

    /**
     * Set the notification title.
     */
    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the notification body.
     */
    public function body(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the notification icon.
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the notification badge.
     */
    public function badge(string $badge): self
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Set the notification image.
     */
    public function image(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Set the notification tag (for grouping/replacing).
     */
    public function tag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set custom data to pass to the service worker.
     */
    public function data(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Set a URL to open when notification is clicked.
     */
    public function url(string $url): self
    {
        $this->data['url'] = $url;

        return $this;
    }

    /**
     * Add an action button.
     */
    public function action(string $title, string $action, ?string $icon = null): self
    {
        $actionData = [
            'action' => $action,
            'title' => $title,
        ];

        if ($icon) {
            $actionData['icon'] = $icon;
        }

        $this->actions[] = $actionData;

        return $this;
    }

    /**
     * Require user interaction to dismiss.
     */
    public function requireInteraction(bool $require = true): self
    {
        $this->requireInteraction = $require;

        return $this;
    }

    /**
     * Renotify even if tag is the same.
     */
    public function renotify(bool $renotify = true): self
    {
        $this->renotify = $renotify;

        return $this;
    }

    /**
     * Make notification silent (no sound/vibration).
     */
    public function silent(bool $silent = true): self
    {
        $this->silent = $silent;

        return $this;
    }

    /**
     * Convert to array for JSON encoding.
     */
    public function toArray(): array
    {
        $payload = [
            'title' => $this->title ?? config('app.name'),
        ];

        if ($this->body) {
            $payload['body'] = $this->body;
        }

        if ($this->icon) {
            $payload['icon'] = $this->icon;
        }

        if ($this->badge) {
            $payload['badge'] = $this->badge;
        }

        if ($this->image) {
            $payload['image'] = $this->image;
        }

        if ($this->tag) {
            $payload['tag'] = $this->tag;
        }

        if ($this->data !== []) {
            $payload['data'] = $this->data;
        }

        if ($this->actions !== []) {
            $payload['actions'] = $this->actions;
        }

        if ($this->requireInteraction) {
            $payload['requireInteraction'] = true;
        }

        if ($this->renotify) {
            $payload['renotify'] = true;
        }

        if ($this->silent) {
            $payload['silent'] = true;
        }

        return $payload;
    }
}
