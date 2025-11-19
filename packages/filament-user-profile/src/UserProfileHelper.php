<?php

namespace BeeGoodIT\FilamentUserProfile;

use BeeGoodIT\FilamentUserProfile\Filament\Pages\Appearance;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Password;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\Profile;
use BeeGoodIT\FilamentUserProfile\Filament\Pages\TwoFactor;
use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Features;

class UserProfileHelper
{
    /**
     * Cache for column existence check.
     */
    private static ?bool $hasTwoFactorColumnsCache = null;

    /**
     * Get user menu items for the main panel.
     * Only Profile is shown here - other items (Password, Appearance, 2FA) are in the settings panel navigation.
     *
     * @return array<string, MenuItem>
     */
    public function getUserMenuItems(): array
    {
        return [
            'profile' => MenuItem::make()
                ->label(__('Profile'))
                ->icon('heroicon-o-user-circle')
                ->url(fn () => Profile::getUrl())
                ->sort(0),
        ];
    }

    /**
     * Check if the users table has the required two-factor authentication columns.
     *
     * @return bool
     */
    public static function hasTwoFactorColumns(): bool
    {
        // Use cached result if available
        if (self::$hasTwoFactorColumnsCache !== null) {
            return self::$hasTwoFactorColumnsCache;
        }

        try {
            // Check if users table exists
            if (!Schema::hasTable('users')) {
                self::$hasTwoFactorColumnsCache = false;
                return false;
            }

            // Check for all required columns
            $hasSecret = Schema::hasColumn('users', 'two_factor_secret');
            $hasRecoveryCodes = Schema::hasColumn('users', 'two_factor_recovery_codes');
            $hasConfirmedAt = Schema::hasColumn('users', 'two_factor_confirmed_at');

            $result = $hasSecret && $hasRecoveryCodes && $hasConfirmedAt;

            // Cache the result
            self::$hasTwoFactorColumnsCache = $result;

            // Log if columns are missing
            if (!$result) {
                $missingColumns = [];
                if (!$hasSecret) {
                    $missingColumns[] = 'two_factor_secret';
                }
                if (!$hasRecoveryCodes) {
                    $missingColumns[] = 'two_factor_recovery_codes';
                }
                if (!$hasConfirmedAt) {
                    $missingColumns[] = 'two_factor_confirmed_at';
                }

                $message = 'Filament User Profile: Two-factor authentication columns are missing from users table.';
                $context = [
                    'missing_columns' => $missingColumns,
                    'migration_command' => 'php artisan vendor:publish --tag=filament-user-profile-migrations && php artisan migrate',
                ];

                // Log to file
                Log::warning($message, $context);

                // Also output to console in development
                if (app()->environment(['local', 'development', 'testing'])) {
                    $missingColumnsStr = implode(', ', $missingColumns);
                    error_log("\n⚠️  {$message}");
                    error_log("   Missing columns: {$missingColumnsStr}");
                    error_log("   Run: {$context['migration_command']}\n");
                }
            }

            return $result;
        } catch (\Exception $e) {
            // If we can't check (e.g., database connection issue), assume false and log
            Log::error('Filament User Profile: Could not check for two-factor authentication columns.', [
                'error' => $e->getMessage(),
            ]);

            self::$hasTwoFactorColumnsCache = false;
            return false;
        }
    }

    /**
     * Clear the column existence cache.
     * Useful for testing or after running migrations.
     */
    public static function clearColumnCache(): void
    {
        self::$hasTwoFactorColumnsCache = null;
    }
}

