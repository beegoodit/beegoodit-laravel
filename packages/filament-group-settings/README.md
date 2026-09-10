# BeeGoodIT Filament Group Settings

Team **policy** settings pages owned by a Filament **navigation group**, plus an optional landing page that only **links** to those pages.

This package is **IA and chrome**, not a settings store. Keep queryable flags as columns (or a real table). It does not use Spatie Laravel Settings.

Do **not** put group settings pages in a Filament Settings **cluster** — that would pull them out of their department nav group.

## Installation

```bash
composer require beegoodit/filament-group-settings
```

The service provider is auto-discovered (translations and views only). **Composer require does not add navigation.**

Register the plugin on the panel and **list** each page:

```php
use BeegoodIT\FilamentGroupSettings\GroupSettingsPlugin;
use App\Filament\Pages\Settings\BillingSettings;

$panel->plugin(
    GroupSettingsPlugin::make()
        ->pages([
            BillingSettings::class,
        ])
        // ->landing()
);
```

`GroupSettingsPlugin::make()` with no `pages()` and no `landing()` adds **no** nav items.

Pages set `$isDiscovered = false`, so `discoverPages()` cannot sneak a subclass into the sidebar. You must list the class on `->pages()`.

## Create a page

```bash
php artisan make:filament-group-settings-page BillingSettings --group-key=billing --navigation-group=navigation.groups.billing
```

Writes `app/Filament/Pages/Settings/BillingSettings.php`. **Still add the class to `->pages()`.**

`getGroupKey()` is a stable URL key (not a translated label): `/billing/settings`.

```php
class BillingSettings extends GroupSettingsPage
{
    public static function getGroupKey(): string
    {
        return 'billing';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.billing');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->topicTabs([
                Tab::make('Agreements')
                    ->schema([
                        Section::make()
                            ->schema([
                                Toggle::make('auto_draft_enabled'),
                            ]),
                    ]),
            ]),
        ]);
    }
}
```

The form binds to `Filament::getTenant()` and saves **form state** onto that model. Fill only hydrates **form field** attributes, not the whole tenant row. Default nav: **Settings** / **Einstellungen**, cog icon, sort 99. Page heading is that same label; breadcrumbs are **navigation group › Settings** (Filament chevrons, not a joined title). Use `topicTabs()` so the tab bar sits **above** the form card (`contained(false)`). `getNavigationGroup()` is required (`->pages()` rejects a blank group). Do not pre-create empty tabs.

## Landing

Opt-in:

```php
GroupSettingsPlugin::make()
    ->pages([BillingSettings::class])
    ->landing()
    ->landingNavigationLabel(__('…')); // optional
```

Top-level **Settings** with a cog. Lists registered pages the user `canAccess()`. No forms.

## Tenancy

Default record is `Filament::getTenant()`. Override `resolveRecord()` if needed. There is no hard dependency on `beegoodit/filament-tenancy`.

`canAccess()` requires an authenticated user, a current tenant, and the page to be in the plugin list. Override `canManage()` on the page when the host app has roles (whole page, not tabs).

## Why not Spatie settings

`spatie/laravel-settings` and the Filament Settings plugin are a typed JSON settings store. They do not place pages in nav groups, do not give a link-only landing, and do not fit Filament `team_id` tenancy or `Model::where('flag', true)` across tenants.

## Tests (monorepo)

```bash
./vendor/bin/phpunit packages/filament-group-settings/tests/
```

## License

MIT. See [LICENSE](LICENSE).
