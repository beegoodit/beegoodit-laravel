<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Filament\Pages;

use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Filament\Concerns\ManagesMcpTokens;
use BeegoodIT\FilamentAi\Models\Setting;
use BeegoodIT\FilamentAi\Services\AccessService;
use BeegoodIT\FilamentAi\Services\PersistSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Laravel\Ai\Enums\Lab;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;
    use ManagesMcpTokens;

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament-ai::pages.settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('filament-ai::ai.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-ai::ai.navigation.settings');
    }

    public static function canAccess(): bool
    {
        return app(AccessService::class)->canManageSettings();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $settings = Setting::forCurrentWorkspace() ?? new Setting([
            'provider' => config('filament-ai.default_provider', Lab::OpenAI->value),
            'model' => config('filament-ai.default_model'),
            'enabled' => false,
            'capabilities' => app(CapabilityRegistry::class)->defaults(),
        ]);

        $this->form->fill([
            'provider' => $settings->provider?->value ?? $settings->provider,
            'model' => $settings->model,
            'api_key' => '',
            'enabled' => (bool) $settings->enabled,
            'mcp_enabled' => (bool) ($settings->mcp_enabled ?? false),
            'custom_instructions' => $settings->custom_instructions,
            'capabilities' => $settings->exists
                ? $settings->resolvedCapabilities()
                : app(CapabilityRegistry::class)->defaults(),
            'has_existing_key' => $settings->hasConfiguredKey(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $registry = app(CapabilityRegistry::class);
        $providerSections = [];

        foreach ($registry->providers() as $provider) {
            $toggles = [];
            foreach ($provider->capabilities() as $capability) {
                $toggles[] = Toggle::make("capabilities.{$provider->id()}.{$capability->key}")
                    ->label($capability->label)
                    ->helperText($capability->help);
            }

            $providerSections[] = Section::make($provider->label())
                ->description($provider->description())
                ->schema($toggles)
                ->columns(2);
        }

        return $schema
            ->components([
                Tabs::make('aiSettingsTabs')
                    ->tabs([
                        Tabs\Tab::make(__('filament-ai::ai.pages.settings.tabs.connection'))
                            ->icon('heroicon-o-key')
                            ->schema([
                                Select::make('provider')
                                    ->label(__('filament-ai::ai.pages.settings.provider'))
                                    ->options([
                                        Lab::OpenAI->value => 'OpenAI',
                                        Lab::Anthropic->value => 'Anthropic',
                                        Lab::Gemini->value => 'Gemini',
                                        Lab::Mistral->value => 'Mistral',
                                        Lab::Groq->value => 'Groq',
                                    ])
                                    ->required(),
                                TextInput::make('api_key')
                                    ->label(__('filament-ai::ai.pages.settings.api_key'))
                                    ->password()
                                    ->revealable()
                                    ->helperText(fn (callable $get): string => $get('has_existing_key')
                                        ? __('filament-ai::ai.pages.settings.api_key_saved')
                                        : __('filament-ai::ai.pages.settings.api_key_help')),
                                TextInput::make('model')
                                    ->label(__('filament-ai::ai.pages.settings.model'))
                                    ->placeholder('gpt-4o-mini'),
                                Toggle::make('enabled')
                                    ->label(__('filament-ai::ai.pages.settings.enabled')),
                            ]),
                        Tabs\Tab::make(__('filament-ai::ai.pages.settings.tabs.context'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Textarea::make('custom_instructions')
                                    ->label(__('filament-ai::ai.pages.settings.custom_instructions'))
                                    ->helperText(__('filament-ai::ai.pages.settings.custom_instructions_help'))
                                    ->rows(12)
                                    ->columnSpanFull(),
                            ]),
                        Tabs\Tab::make(__('filament-ai::ai.pages.settings.tabs.capabilities'))
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema($providerSections),
                        Tabs\Tab::make(__('filament-ai::ai.pages.settings.tabs.mcp'))
                            ->icon('heroicon-o-signal')
                            ->schema([
                                Toggle::make('mcp_enabled')
                                    ->label(__('filament-ai::ai.mcp.enabled'))
                                    ->helperText(__('filament-ai::ai.mcp.enabled_help')),
                                ViewField::make('mcp_panel')
                                    ->view('filament-ai::pages.partials.mcp-tab')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(PersistSettings::class)->save([
            'enabled' => (bool) ($data['enabled'] ?? false),
            'mcp_enabled' => (bool) ($data['mcp_enabled'] ?? false),
            'provider' => $data['provider'] ?? null,
            'model' => $data['model'] ?? null,
            'api_key' => $data['api_key'] ?? null,
            'capabilities' => is_array($data['capabilities'] ?? null) ? $data['capabilities'] : null,
            'custom_instructions' => $data['custom_instructions'] ?? null,
        ]);

        Notification::make()
            ->title(__('filament-ai::ai.pages.settings.saved'))
            ->success()
            ->send();

        $this->form->fill([
            ...$data,
            'api_key' => '',
            'mcp_enabled' => (bool) $settings->mcp_enabled,
            'custom_instructions' => $settings->custom_instructions,
            'capabilities' => $settings->resolvedCapabilities(),
            'has_existing_key' => $settings->hasConfiguredKey(),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-ai::ai.pages.settings.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('filament-ai::ai.pages.settings.heading');
    }
}
