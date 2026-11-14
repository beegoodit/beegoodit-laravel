<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use Throwable;

class ProviderErrorPresenter
{
    public function present(Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        if ($message === '') {
            return __('filament-ai::ai.chat.errors.generic');
        }

        return $message;
    }
}
