<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Services;

use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Support\FilamentAi;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class AccessService
{
    public function settings(): ?Setting
    {
        return FilamentAi::settingsModel()::forCurrentTenant();
    }

    public function hasValidByok(): bool
    {
        $settings = $this->settings();

        // Match TallCMS host BYOK: enabled + configured key. Model may be null
        // (UI placeholder / provider default); provider is optional for the gate.
        return $settings !== null
            && $settings->enabled
            && $settings->hasConfiguredKey();
    }

    public function canUseAssistant(?Authenticatable $user = null): bool
    {
        if (! $this->hasValidByok()) {
            return false;
        }

        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->passesPermission($user, config('filament-ai.permissions.use_assistant'));
    }

    public function canManageSettings(?Authenticatable $user = null): bool
    {
        $user ??= auth()->user();

        if ($user === null) {
            return false;
        }

        return $this->passesPermission($user, config('filament-ai.permissions.manage_settings'));
    }

    private function passesPermission(Authenticatable $user, mixed $permission): bool
    {
        if ($permission === null || $permission === '' || $permission === false) {
            return true;
        }

        $ability = (string) $permission;

        return Gate::forUser($user)->check($ability)
            || (method_exists($user, 'can') && $user->can($ability));
    }
}
