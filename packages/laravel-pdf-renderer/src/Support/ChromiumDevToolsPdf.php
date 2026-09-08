<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Print HTML to PDF via Chromium DevTools Protocol (supports header/footer templates).
 *
 * @SuppressWarnings(PHPMD.ErrorControlOperator)
 */
final class ChromiumDevToolsPdf
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function print(string $executable, string $htmlPath, array $options = []): string
    {
        $userDataDir = ChromiumRuntime::allocateProfileDirectory('chromium-cdp-');
        $process = null;

        try {
            $process = new Process(array_values(array_filter([
                $executable,
                '--headless=new',
                '--remote-debugging-port=0',
                ...ChromiumRuntime::stabilityFlags($userDataDir),
                filter_var(config('laravel-pdf-renderer.no_sandbox', true), FILTER_VALIDATE_BOOLEAN) ? '--no-sandbox' : null,
                'about:blank',
            ])));
            $process->setTimeout((int) config('laravel-pdf-renderer.timeout', 60));
            $process->setEnv(ChromiumRuntime::processEnvironment($userDataDir));
            $process->start();

            $port = $this->waitForDebugPort($userDataDir, $process);
            $wsUrl = $this->createPageTarget($port);
            $client = ChromiumDevToolsClient::connect($wsUrl);

            try {
                $client->send('Page.enable');
                $client->send('Runtime.enable');
                $client->send('Page.navigate', ['url' => 'file://'.$htmlPath]);
                $this->waitUntilDocumentReady($client);
                $this->waitUntilImagesLoaded($client);

                $result = $client->send('Page.printToPDF', [
                    'printBackground' => true,
                    'displayHeaderFooter' => (bool) ($options['displayHeaderFooter'] ?? false),
                    'headerTemplate' => (string) ($options['headerTemplate'] ?? '<div></div>'),
                    'footerTemplate' => (string) ($options['footerTemplate'] ?? '<div></div>'),
                    'marginTop' => (float) ($options['marginTop'] ?? 0.4),
                    'marginBottom' => (float) ($options['marginBottom'] ?? 0.4),
                    'marginLeft' => (float) ($options['marginLeft'] ?? 0.4),
                    'marginRight' => (float) ($options['marginRight'] ?? 0.4),
                    'paperWidth' => (float) ($options['paperWidth'] ?? Din5008Metrics::PAPER_WIDTH_IN),
                    'paperHeight' => (float) ($options['paperHeight'] ?? Din5008Metrics::PAPER_HEIGHT_IN),
                    'preferCSSPageSize' => (bool) ($options['preferCSSPageSize'] ?? false),
                ]);
            } finally {
                $client->close();
            }

            $pdf = base64_decode((string) ($result['data'] ?? ''), true);
            if ($pdf === false || $pdf === '' || ! str_starts_with($pdf, '%PDF')) {
                throw new RuntimeException('Chromium DevTools PDF generation returned invalid PDF bytes.');
            }

            return $pdf;
        } finally {
            if ($process instanceof Process && $process->isRunning()) {
                $process->stop(1);
            }
            ChromiumRuntime::removeDirectory($userDataDir);
        }
    }

    private function waitForDebugPort(string $userDataDir, Process $process): int
    {
        $portFile = $userDataDir.DIRECTORY_SEPARATOR.'DevToolsActivePort';
        $deadline = microtime(true) + 15.0;

        while (microtime(true) < $deadline) {
            if (! $process->isRunning()) {
                throw new RuntimeException(
                    'Chromium exited before DevTools became ready: '.$process->getErrorOutput()
                );
            }

            if (is_file($portFile)) {
                $lines = file($portFile, FILE_IGNORE_NEW_LINES);
                $port = isset($lines[0]) ? (int) $lines[0] : 0;
                if ($port > 0) {
                    return $port;
                }
            }

            usleep(50_000);
        }

        throw new RuntimeException('Timed out waiting for Chromium DevToolsActivePort.');
    }

    private function createPageTarget(int $port): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'PUT',
                'timeout' => 5,
                'ignore_errors' => true,
                'header' => "Content-Length: 0\r\n",
            ],
        ]);
        $response = @file_get_contents('http://127.0.0.1:'.$port.'/json/new?about:blank', false, $context);
        if ($response === false) {
            throw new RuntimeException('Unable to create Chromium DevTools target.');
        }

        /** @var array{webSocketDebuggerUrl?: string} $payload */
        $payload = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $wsUrl = $payload['webSocketDebuggerUrl'] ?? null;
        if (! is_string($wsUrl) || $wsUrl === '') {
            throw new RuntimeException('Chromium DevTools target missing webSocketDebuggerUrl.');
        }

        return $this->ensureWebSocketPort($wsUrl, $port);
    }

    private function ensureWebSocketPort(string $wsUrl, int $port): string
    {
        $parts = parse_url($wsUrl);
        if ($parts === false || ! isset($parts['host'], $parts['path'])) {
            return $wsUrl;
        }

        if (isset($parts['port'])) {
            return $wsUrl;
        }

        return sprintf(
            'ws://%s:%d%s%s',
            $parts['host'],
            $port,
            $parts['path'],
            isset($parts['query']) ? '?'.$parts['query'] : '',
        );
    }

    private function waitUntilDocumentReady(ChromiumDevToolsClient $client): void
    {
        $deadline = microtime(true) + 15.0;

        while (microtime(true) < $deadline) {
            $result = $client->send('Runtime.evaluate', [
                'expression' => 'document.readyState',
                'returnByValue' => true,
            ]);
            $state = $result['result']['value'] ?? null;
            if ($state === 'complete' || $state === 'interactive') {
                return;
            }

            usleep(50_000);
        }

        throw new RuntimeException('Timed out waiting for Chromium document readyState.');
    }

    private function waitUntilImagesLoaded(ChromiumDevToolsClient $client): void
    {
        $deadline = microtime(true) + 15.0;

        while (microtime(true) < $deadline) {
            $result = $client->send('Runtime.evaluate', [
                'expression' => 'Array.from(document.images).every((img) => img.complete)',
                'returnByValue' => true,
            ]);

            if (($result['result']['value'] ?? false) === true) {
                return;
            }

            usleep(50_000);
        }

        throw new RuntimeException('Timed out waiting for Chromium images to load.');
    }
}
