# beegoodit/filament-ai

**Greenfield** Filament AI for Laravel apps: capability SPI, BYOK settings, Ask / Plan / Agent chat, conversations, workspace scoping, and remote MCP (`laravel/mcp` soft peer). TallCMS CMS tools stay in the TallCMS plugin; this package is the shared basis.

Developed and proven as a **normal Composer package** (Orchestra Testbench). TallCMS pack-copies into `src/FilamentAi/` (keeps `BeegoodIT\FilamentAi\`) via `scripts/sync-filament-ai.sh`. First non-TallCMS application host is **TBD**.

Chamaleon iteration: `128-split-ai-plugin`.

| Slice | Delivers |
|-------|----------|
| 001 | Package scaffold, empty registry, nav stub |
| 002 | SPI + BYOK / capabilities Settings |
| 003 | Chat + conversations + attachments (disk) |
| **004** | TallCMS pack-sync → `src/FilamentAi/` (package NS; host `SiteAiSetting` extends `Setting`) |
| **005** | Remote MCP (`laravel/mcp` soft peer) |
| 006 | First non-TallCMS host + capability providers (discuss first) |

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

Register on a Filament panel:

```php
use BeegoodIT\FilamentAi\Filament\FilamentAiPlugin;

$panel->plugin(FilamentAiPlugin::make());
```

Optional workspace + attachment config (**set workspace key type before first migrate**):

```php
config([
    'filament-ai.workspace_resolver' => MyWorkspaceResolver::class,
    'filament-ai.workspace_key_type' => 'int', // or 'uuid'
    'filament-ai.workspace_column' => 'workspace_id',
    'filament-ai.attachments.disk' => env('FILESYSTEM_DISK', 'local'), // s3 on staging/prod
    'filament-ai.attachments.directory' => 'filament-ai/chat-attachments',
]);
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

```php
use BeegoodIT\FilamentAi\Capabilities\CapabilityDefinition;
use BeegoodIT\FilamentAi\Capabilities\CapabilityRegistry;
use BeegoodIT\FilamentAi\Capabilities\Contracts\CapabilityProvider;
use BeegoodIT\FilamentAi\Models\Setting;

final class ExampleAiProvider implements CapabilityProvider
{
    public function id(): string { return 'example'; }

    public function contractVersion(): int { return CapabilityRegistry::SUPPORTED_CONTRACT_VERSION; }

    public function label(): string { return 'Example'; }

    public function description(): ?string { return null; }

    public function capabilities(): array
    {
        return [
            new CapabilityDefinition('manage', 'Manage', null, default: true, allowedInReadOnlyMode: false, tools: []),
        ];
    }

    public function alwaysOnTools(): array { return []; }

    public function instructions(Setting $settings): ?string { return null; }

    public function providerNativeTools(string $capabilityKey, Setting $settings): array { return []; }
}

app(CapabilityRegistry::class)->register(new ExampleAiProvider);
```

Tool helpers: `BeegoodIT\FilamentAi\Tools\Concerns\InteractsWithTools`.

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

- Tokens are workspace-scoped (`read` | `write`); workspace is on the token, not the URL.
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
