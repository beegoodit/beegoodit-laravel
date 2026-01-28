<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\Components;

use BeegoodIT\FilamentTenancyDomains\Domain;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class WebPresence
{
    public static function make(): Section
    {
        $platformDomain = config('filament-tenancy-domains.platform_domain', 'domain.local');
        $customDomainRecord = Filament::getTenant()->domains()->where('type', 'custom_domain')->first();

        return Section::make(__('filament-tenancy-domains::domains.web_presence'))
            ->description(__('filament-tenancy-domains::domains.web_presence_description'))
            ->schema([
                TextInput::make('platform_subdomain')
                    ->label(__('filament-tenancy-domains::domains.platform_subdomain'))
                    ->helperText(__('filament-tenancy-domains::domains.platform_subdomain_helper'))
                    ->prefix('https://')
                    ->suffix('.' . $platformDomain)
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/')
                    ->validationMessages([
                        'regex' => __('filament-tenancy-domains::domains.invalid_subdomain'),
                    ])
                    ->rules([
                        fn () => function (string $attribute, $value, \Closure $fail) use ($platformDomain) {
                            $team = Filament::getTenant();
                            $domainPath = $value . '.' . $platformDomain;
                            
                            $exists = Domain::where('domain', $domainPath)
                                ->whereNot(function($query) use ($team) {
                                    $query->where('model_type', get_class($team))
                                          ->where('model_id', $team->id);
                                })
                                ->exists();

                            if ($exists) {
                                $fail(__('filament-tenancy-domains::domains.subdomain_taken'));
                            }
                        },
                    ]),

                TextInput::make('custom_domain')
                    ->label(__('filament-tenancy-domains::domains.custom_domain'))
                    ->helperText(__('filament-tenancy-domains::domains.custom_domain_description'))
                    ->placeholder('www.yourdomain.com')
                    ->regex('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i')
                    ->validationMessages([
                        'regex' => __('filament-tenancy-domains::domains.invalid_custom_domain'),
                    ])
                    ->unique(Domain::class, 'domain', ignorable: function () use ($customDomainRecord) {
                        return $customDomainRecord;
                    }),

                Section::make(__('filament-tenancy-domains::domains.dns_setup'))
                    ->description(__('filament-tenancy-domains::domains.dns_setup_instructions'))
                    ->schema([
                        Placeholder::make('dns_instructions')
                            ->hiddenLabel()
                            ->content(fn () => new HtmlString(sprintf(
                                '<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-sm space-y-2">' .
                                '<div><span class="text-gray-500">%s:</span> TXT</div>' .
                                '<div><span class="text-gray-500">%s:</span> _foosbeaver-verification.%s</div>' .
                                '<div><span class="text-gray-500">%s:</span> <span class="text-primary-600 dark:text-primary-400">%s</span></div>' .
                                '</div>',
                                __('filament-tenancy-domains::domains.dns_record_type'),
                                __('filament-tenancy-domains::domains.dns_record_name'),
                                $customDomainRecord?->domain,
                                __('filament-tenancy-domains::domains.dns_record_value'),
                                $customDomainRecord?->verification_token ?? '-'
                            ))),

                        Placeholder::make('verification_status')
                            ->hiddenLabel()
                            ->content(function () {
                                $record = Filament::getTenant()->domains()->where('type', 'custom_domain')->first();
                                
                                if (!$record) return null;

                                $bgClass = $record->is_verified 
                                    ? 'bg-green-50 text-green-700 border-green-100 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800' 
                                    : ($record->last_verification_error 
                                        ? 'bg-red-50 text-red-700 border-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800' 
                                        : 'bg-yellow-50 text-yellow-700 border-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 dark:border-yellow-800');

                                $statusLabel = $record->is_verified 
                                    ? __('filament-tenancy-domains::domains.verified') 
                                    : ($record->last_verification_error 
                                        ? __('filament-tenancy-domains::domains.failed') 
                                        : __('filament-tenancy-domains::domains.pending'));

                                $errorMessage = $record->last_verification_error 
                                    ? ': ' . e($record->last_verification_error) 
                                    : '';

                                return new HtmlString(sprintf(
                                    '<div class="rounded-lg border p-3 text-sm %s" wire:poll.5s>' .
                                    '<span class="font-bold uppercase tracking-wide text-xs">%s</span>' .
                                    '<span>%s</span>' .
                                    '</div>',
                                    $bgClass,
                                    $statusLabel,
                                    $errorMessage
                                ));
                            }),

                        Actions::make([
                            Action::make('verify_now')
                                ->label(__('filament-tenancy-domains::domains.verify_now'))
                                ->button()
                                ->color('primary')
                                ->action(function () use ($customDomainRecord) {
                                    if ($customDomainRecord) {
                                        $success = $customDomainRecord->verify();
                                        
                                        if ($success) {
                                            \Filament\Notifications\Notification::make()
                                                ->title(__('filament-tenancy-domains::domains.verification_success'))
                                                ->success()
                                                ->send();
                                        } else {
                                            \Filament\Notifications\Notification::make()
                                                ->title(__('filament-tenancy-domains::domains.verification_failed'))
                                                ->danger()
                                                ->persistent()
                                                ->send();
                                        }
                                    }
                                }),
                        ]),
                    ])
                    ->compact()
                    ->visible(fn () => $customDomainRecord && !$customDomainRecord->is_verified),
            ]);
    }

    public static function mutateFormDataBeforeFill(array $data): array
    {
        $platformDomain = config('filament-tenancy-domains.platform_domain', 'domain.local');
        
        $platform = Filament::getTenant()->domains()
            ->where('type', 'platform')
            ->first();

        if ($platform) {
            $data['platform_subdomain'] = str_replace('.' . $platformDomain, '', $platform->domain);
        }

        $custom = Filament::getTenant()->domains()
            ->where('type', 'custom_domain')
            ->first();

        if ($custom) {
            $data['custom_domain'] = $custom->domain;
            
            // Ensure token exists for existing records
            if (!$custom->verification_token) {
                $custom->update(['verification_token' => Str::random(32)]);
            }
        }

        return $data;
    }

    public static function handleSave(array $data): void
    {
        $platformDomain = config('filament-tenancy-domains.platform_domain', 'domain.local');
        
        // Handle Platform Subdomain
        if ($subdomain = $data['platform_subdomain'] ?? null) {
            $domainPath = $subdomain . '.' . $platformDomain;

            Filament::getTenant()->domains()->updateOrCreate(
                ['type' => 'platform'],
                [
                    'domain' => $domainPath,
                    'is_primary' => true,
                    'is_active' => true,
                    'verified_at' => now(),
                ]
            );
        }

        // Handle Custom Domain
        if ($customDomain = $data['custom_domain'] ?? null) {
            $record = Filament::getTenant()->domains()->where('type', 'custom_domain')->first();
            
            Filament::getTenant()->domains()->updateOrCreate(
                ['type' => 'custom_domain'],
                [
                    'domain' => $customDomain,
                    'is_active' => true,
                    'verified_at' => $record?->domain === $customDomain ? $record->verified_at : null,
                    'verification_token' => $record?->verification_token ?? Str::random(32),
                ]
            );
        } else {
            // Remove custom domain if cleared
            Filament::getTenant()->domains()->where('type', 'custom_domain')->delete();
        }
    }
}
