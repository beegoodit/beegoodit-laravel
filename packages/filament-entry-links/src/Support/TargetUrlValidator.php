<?php

namespace BeegoodIT\FilamentEntryLinks\Support;

class TargetUrlValidator
{
    public function allows(string $url): bool
    {
        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['scheme'], $parsed['host'])) {
            return false;
        }

        $scheme = strtolower((string) $parsed['scheme']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $mode = config('filament-entry-links.allowed_url_mode', 'same_app');

        return match ($mode) {
            'off' => true,
            'same_app' => $this->hostMatchesApp($parsed['host']),
            'allowlist' => $this->hostInAllowlist($parsed['host']),
            default => false,
        };
    }

    private function hostMatchesApp(string $host): bool
    {
        $appUrl = config('app.url');

        if (! is_string($appUrl) || $appUrl === '') {
            return false;
        }

        $appHost = parse_url($appUrl, PHP_URL_HOST);

        if (! is_string($appHost) || $appHost === '') {
            return false;
        }

        return strcasecmp($host, $appHost) === 0;
    }

    private function hostInAllowlist(string $host): bool
    {
        /** @var array<int, string> $allowed */
        $allowed = config('filament-entry-links.allowed_hosts', []);

        foreach ($allowed as $candidate) {
            if (strcasecmp($host, $candidate) === 0) {
                return true;
            }
        }

        return false;
    }
}
