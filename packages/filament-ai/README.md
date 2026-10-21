# beegoodit/filament-ai

**Greenfield** Filament AI for Laravel apps: capability SPI, BYOK settings, Ask / Plan / Agent chat, conversations, tenant scoping, and remote MCP (`laravel/mcp` soft peer). TallCMS CMS tools stay in the TallCMS plugin; this package is the shared basis.

Developed and proven as a **normal Composer package** (Orchestra Testbench). TallCMS pack-copies into `src/FilamentAi/` (keeps `BeegoodIT\FilamentAi\`) via `scripts/sync-filament-ai.sh`. First non-TallCMS application host is **TBD**.

Chamaleon iteration: `128-split-ai-plugin`.

| Slice | Delivers |
|-------|----------|
| 001 | Package scaffold, empty registry, nav stub |
| 002 | SPI + BYOK / capabilities Settings |
| 003 | Chat + conversations + attachments (disk) |
| **004** | TallCMS pack-sync → `src/FilamentAi/` (package NS; host `SiteAiSetting` extends `Setting`) |
| **005** | Remote MCP (`laravel/mcp` soft peer) |
| 006 | First non-TallCMS host + capability providers (see below) |

## Requirements

- PHP `^8.2` (runtime of `laravel/ai` may require `^8.3`)
- Filament `^4` or `^5`
- Laravel / Illuminate `^11`–`^13`
- `laravel/ai` `^0.7` or `^1.0`

## Install

```bash
composer require beegoodit/filament-ai
php artisan vendor:publish --tag=filament-ai-config
php artisan vendor:publish --tag=ai-migrations   # laravel/ai conversations
php artisan migrate
```

The package adds **`mode`** on `agent_conversations`. It does **not** add a tenant column there — the host owns tenancy shape (column name, int vs uuid, whether multi-tenant at all). When multi-tenant, add that column in a host migration, then point config at it. Package code only scopes conversations when the configured column exists.

Register on a Filament panel:

```php
use BeegoodIT\FilamentAi\Filament\FilamentAiPlugin;

$panel->plugin(FilamentAiPlugin::make());
```

Optional tenant + attachment config (**set key types before first migrate** of package-owned tables such as settings / MCP tokens):

```php
config([
    'filament-ai.tenant_resolver' => MyTenantResolver::class,
    'filament-ai.tenant_key_type' => 'int', // or 'uuid'
    'filament-ai.user_key_type' => 'int', // or 'uuid' when users use HasUuids
    'filament-ai.tenant_column' => 'tenant_id', // must match host column on conversations
    'filament-ai.attachments.disk' => env('FILESYSTEM_DISK', 'local'), // s3 on staging/prod
    'filament-ai.attachments.directory' => 'filament-ai/chat-attachments',
]);
```

Example host migration for conversations (uuid teams):

```php
Schema::table('agent_conversations', function (Blueprint $table): void {
    $table->uuid('tenant_id')->nullable()->index();
});
```

Settings table / `enabled` column are package constants (`Setting::TABLE`, column `enabled`). Hosts with a different schema subclass `Setting` (override `getTable()` / `enabledColumn()`) and register it:

```php
use BeegoodIT\FilamentAi\Support\FilamentAi;

FilamentAi::useSettingsModel(SiteAiSetting::class);
```

## Chat modes

| Mode | Behaviour |
|------|-----------|
| **Ask** | Read-only tools (`allowedInReadOnlyMode`) |
| **Plan** | Read-only; plan-oriented instructions |
| **Agent** | Write tools when capabilities enabled |

Agent class: `BeegoodIT\FilamentAi\Agents\ShellAgent`. Tools = registry ∩ settings ∩ mode.

## Attachments

Composer uploads use Filament `FileUpload` → configured **private disk** (local or S3). Message JSON stores `path` + `disk`. Cleanup runs on conversation delete. TallCMS media library import is host glue later (copy from path) — not Spatie in this package.

## Capability providers

This package registers a default shell provider (`filament_ai` / **General** / **Allgemein**) with an off-by-default **Web access** toggle (`WebSearch` + `WebFetch` via `providerNativeTools`). Domain tools and further Settings → Fähigkeiten sections come from host/plugin `CapabilityProvider`s registered in `boot()` — do not re-bind the `CapabilityRegistry` singleton.

### SPI

| Method | Role |
|--------|------|
| `id()` | Stable snake_case section id (`tallcms`, `speisekarte`, `timesloth`, …) |
| `contractVersion()` | Must equal `CapabilityRegistry::SUPPORTED_CONTRACT_VERSION` (currently `1`) or register is skipped |
| `label()` / `description()` | Settings UI section title / help |
| `capabilities()` | List of `CapabilityDefinition` (toggles + tool classes) |
| `alwaysOnTools()` | Tool classes always available when AI runs (not toggle-gated) |
| `instructions(Setting)` | Optional system-prompt fragment when **any** group for this provider is enabled |
| `providerNativeTools($key, Setting)` | Instantiated tools for a key (e.g. Laravel `WebSearch`), not class-strings |

`CapabilityDefinition`:

| Field | Meaning |
|-------|---------|
| `key` | Stored under `capabilities.{providerId}.{key}` |
| `label` / `help` | Toggle UI |
| `default` | On/off when unset in storage |
| `allowedInReadOnlyMode` | `true` → available in Ask/Plan; `false` → Agent only |
| `tools` | `list<class-string>` of Laravel AI tool classes |
| `requires` | Same-provider keys that must also be enabled |
| `group` | Optional Settings group label within the provider section (heading + horizontal rule between aggregates) |

### How tools are selected

```
Tools = alwaysOn
      ∪ (enabled Settings toggles
         ∩ mode-allowed (Ask/Plan drop !allowedInReadOnlyMode)
         ∩ requires-met
         ∪ providerNativeTools)
```

| Mode | Read-only? | Effect |
|------|------------|--------|
| **Ask** | yes | Only capabilities with `allowedInReadOnlyMode: true` |
| **Plan** | yes | Same tool filter; plan-oriented instructions |
| **Agent** | no | Write/delete tools when Settings allow |

MCP tokens use the same filter: read scope ≈ Ask, write scope ≈ Agent ∩ Settings.

### Recommended group shape (TallCMS domain plugins)

Prefer risk-based groups, not one toggle per model. Common triad:

| Key | Default | Ask/Plan? | Tools |
|-----|---------|-----------|-------|
| `read` | on | yes | List/Get/Search |
| `manage` | on | no | Create/Update/Upsert |
| `delete` | **off** | no | Destructive ops |

Keep high-risk actions (publish, finalize invoice, bulk delete) default **off**, Agent-only. Use `requires: ['manage']` (or similar) when a group depends on another.

Core hosts may register a richer catalog (TallCMS `CoreTallcmsAiProvider` + `AiCapabilities`); sibling plugins typically use the triad above.

### Register

Package binds an empty singleton in `register()`. Hosts and sibling plugins **add** providers in `boot()` (soft-dep so the plugin still boots without filament-ai):

```php
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;

public function boot(): void
{
    if (class_exists(CapabilityRegistry::class)) {
        app(CapabilityRegistry::class)->register(new ExampleAiProvider);
    }
}
```

Last register wins per `id()`. Do not re-bind the singleton and wipe other providers.

### Minimal provider

```php
use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Capabilities\Contracts\CapabilityProvider;
use BeegoodIT\FilamentAi\Models\Setting;

final class ExampleAiProvider implements CapabilityProvider
{
    public const ID = 'example';

    public function id(): string
    {
        return self::ID;
    }

    public function contractVersion(): int
    {
        return CapabilityRegistry::SUPPORTED_CONTRACT_VERSION;
    }

    public function label(): string
    {
        return 'Example';
    }

    public function description(): ?string
    {
        return 'Demo capability groups.';
    }

    public function capabilities(): array
    {
        return [
            new CapabilityDefinition(
                key: 'read',
                label: 'Read',
                help: 'List/get example records.',
                default: true,
                allowedInReadOnlyMode: true,
                tools: [
                    // App\Ai\Tools\ListExampleRecords::class,
                ],
            ),
            new CapabilityDefinition(
                key: 'manage',
                label: 'Manage',
                help: 'Create/update. Agent mode only.',
                default: true,
                allowedInReadOnlyMode: false,
                tools: [
                    // App\Ai\Tools\UpsertExampleRecord::class,
                ],
            ),
            new CapabilityDefinition(
                key: 'delete',
                label: 'Delete',
                help: 'Destructive. Off by default.',
                default: false,
                allowedInReadOnlyMode: false,
                tools: [
                    // App\Ai\Tools\DeleteExampleRecord::class,
                ],
            ),
        ];
    }

    public function alwaysOnTools(): array
    {
        return [];
    }

    public function instructions(Setting $settings): ?string
    {
        if (! $settings->can(self::ID, 'read')
            && ! $settings->can(self::ID, 'manage')
            && ! $settings->can(self::ID, 'delete')) {
            return null;
        }

        return 'Example domain: use Example* tools only for demo records.';
    }

    public function providerNativeTools(string $capabilityKey, Setting $settings): array
    {
        return [];
    }
}
```

### Tools

Implement Laravel AI `Tool` classes. Use `BeegoodIT\FilamentAi\Concerns\InteractsWithTools` and call `assertProviderCapability($providerId, $key)` inside the tool so Settings cannot be bypassed if a tool is somehow still attached.

Tool **class basenames must be unique** across all registered providers (`DuplicateToolNameException` on register).

## Remote MCP

Soft peer: `composer require laravel/mcp` (^1.0 with current `laravel/ai`; older hosts may use ^0.8). Without it the package still boots; Settings shows a warning and `/ai/mcp` is not registered.

```php
// config/filament-ai.php (excerpt)
'mcp' => [
    'path' => '/ai/mcp',
    'server_name' => 'Filament AI',
    'tokens_table' => 'beegoodit_filament_ai_mcp_tokens',
    'token_prefix' => 'fa_mcp_',
    // Optional: pin host tenancy after token auth
    'after_authenticate' => null,
],
```

- Tokens are tenant-scoped (`read` | `write`); tenant is on the token, not the URL.
- Tools = registry ∩ settings capabilities ∩ token scope (Ask vs Agent).
- Settings → MCP tab: enable remote MCP, create/revoke tokens (secret shown once).

## Parity vs TallCMS (intentional omissions in the MIT shell)

- No CMS tools / TipTap / Generate-with-AI / media library tab
- No Multisite site-mismatch / audit banners (TallCMS uses `mcp.after_authenticate` + `McpTokenSiteBridge`)
- Chat UI is simpler (no tool-activity chrome / inline media extractors)

## Development

```bash
cd packages/filament-ai
composer update
composer test
```

Proof for this slice is **Testbench only** — no application host.

## License

MIT
