<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Files\File;
use Laravel\Ai\Files\Image;

class ChatAttachmentMapper
{
    /**
     * @var list<string>
     */
    private const IMAGE_MIMES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function disk(): string
    {
        return (string) (config('filament-ai.attachments.disk')
            ?: config('filesystems.default', 'local'));
    }

    public function directory(): string
    {
        return (string) config('filament-ai.attachments.directory', 'filament-ai/chat-attachments');
    }

    /**
     * @param  list<string>|string|null  $paths
     * @return list<File>
     */
    public function fromStoragePaths(array|string|null $paths): array
    {
        $paths = array_values(array_filter(
            is_array($paths) ? $paths : ($paths !== null && $paths !== '' ? [$paths] : []),
            fn (mixed $path): bool => is_string($path) && $path !== '',
        ));

        $disk = $this->disk();

        return array_map(
            fn (string $path): File => $this->mapPath($path, $disk),
            $paths,
        );
    }

    public function mapPath(string $path, ?string $disk = null): File
    {
        $disk ??= $this->disk();
        $mime = $this->mimeFor($path, $disk);

        if ($mime !== null && in_array(strtolower($mime), self::IMAGE_MIMES, true)) {
            return Image::fromStorage($path, $disk);
        }

        return Document::fromStorage($path, $disk);
    }

    /**
     * @param  list<array{name?: string, type?: string, path?: string, disk?: string}>  $attachments
     */
    public function promptContextBlock(array $attachments): string
    {
        $lines = [];

        foreach ($attachments as $attachment) {
            $path = trim((string) ($attachment['path'] ?? ''));

            if ($path === '') {
                continue;
            }

            $name = trim((string) ($attachment['name'] ?? ''));

            if ($name === '') {
                $name = basename($path);
            }

            $lines[] = sprintf(
                '%d. name=%s; path=%s; disk=%s',
                count($lines) + 1,
                $name,
                $path,
                (string) ($attachment['disk'] ?? $this->disk()),
            );
        }

        if ($lines === []) {
            return '';
        }

        return "Chat attachments (path + disk for host import tools):\n".implode("\n", $lines);
    }

    protected function mimeFor(string $path, string $disk): ?string
    {
        try {
            $mime = Storage::disk($disk)->mimeType($path);

            return is_string($mime) && $mime !== '' ? $mime : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
