<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Strategies;

use BeegoodIT\Pdf\Contracts\RendererContract;
use BeegoodIT\Pdf\Support\ChromiumDevToolsPdf;
use BeegoodIT\Pdf\Support\ChromiumRuntime;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/** Chromium/Chrome headless PDF via DevTools when possible, CLI print-to-pdf as fallback. */
/** @SuppressWarnings(PHPMD.ErrorControlOperator) */
final class ChromiumStrategy implements RendererContract
{
    public static function isAvailable(): bool
    {
        return (new self)->executablePath() !== null;
    }

    public function htmlToPdf(string $html, array $options = []): string
    {
        $executable = $this->executablePath();
        if ($executable === null) {
            throw new RuntimeException('No Chromium/Chrome executable found for PDF generation.');
        }

        $workingDirectory = ChromiumRuntime::workingDirectory();
        $htmlFile = tempnam($workingDirectory, 'laravel-pdf-renderer-html-');
        $pdfFile = tempnam($workingDirectory, 'laravel-pdf-renderer-out-');
        if ($htmlFile === false || $pdfFile === false) {
            throw new RuntimeException('Unable to allocate temp files for PDF generation.');
        }

        $htmlPath = $htmlFile.'.html';
        $pdfPath = $pdfFile.'.pdf';
        rename($htmlFile, $htmlPath);
        @unlink($pdfFile);

        file_put_contents($htmlPath, $html);

        try {
            if (($options['displayHeaderFooter'] ?? false) === true) {
                try {
                    return (new ChromiumDevToolsPdf)->print($executable, $htmlPath, $options);
                } catch (\Throwable $exception) {
                    Log::error('Chromium DevTools PDF failed; refusing CLI fallback because header/footer were required.', [
                        'message' => $exception->getMessage(),
                    ]);

                    throw new RuntimeException(
                        'Chromium DevTools PDF failed (header/footer required): '.$exception->getMessage(),
                        previous: $exception,
                    );
                }
            }

            return $this->printWithCli($executable, $htmlPath, $pdfPath);
        } finally {
            @unlink($htmlPath);
            @unlink($pdfPath);
        }
    }

    private function printWithCli(string $executable, string $htmlPath, string $pdfPath): string
    {
        $userDataDir = ChromiumRuntime::allocateProfileDirectory('chromium-cli-');

        try {
            $process = new Process(array_values(array_filter([
                $executable,
                '--headless',
                '--no-pdf-header-footer',
                ...ChromiumRuntime::stabilityFlags($userDataDir),
                filter_var(config('laravel-pdf-renderer.no_sandbox', true), FILTER_VALIDATE_BOOLEAN) ? '--no-sandbox' : null,
                '--print-to-pdf='.$pdfPath,
                'file://'.$htmlPath,
            ])));
            $process->setTimeout((int) config('laravel-pdf-renderer.timeout', 60));
            $process->setEnv(ChromiumRuntime::processEnvironment($userDataDir));
            $process->run();

            $pdf = is_file($pdfPath) ? (string) file_get_contents($pdfPath) : '';
            if ($pdf === '' || ! str_starts_with($pdf, '%PDF')) {
                throw new RuntimeException(
                    'Chromium PDF generation failed: '.$process->getErrorOutput()
                );
            }

            return $pdf;
        } finally {
            ChromiumRuntime::removeDirectory($userDataDir);
        }
    }

    private function executablePath(): ?string
    {
        foreach ([
            config('laravel-pdf-renderer.executable'),
            env('GROVER_EXECUTABLE_PATH'),
            env('CHROMIUM_PATH'),
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/snap/bin/chromium',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ] as $path) {
            if (is_string($path) && $path !== '' && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }
}
