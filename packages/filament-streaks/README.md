# beegoodit/filament-streaks

Headless streak engine for Laravel with optional Filament v4 admin resources. Track continuous **runs** per subject and strategy, record **qualifications** (proof events), evaluate periods (extend / at-risk / break), and send capped notifications.

## What it does

- **Engine** — discovers events from host-defined strategies, upserts qualifications, opens/extends/breaks streak runs, and sends at-risk / broken notifications when configured.
- **Persistence** — `Streak` (a run), `Qualification` (proof row), `Notification` (sent log for channel kinds).
- **Admin** — Filament resources for support and debugging, plus relation managers on the streak edit screen.
- **Headless** — player UI, fuel sources, and strategy logic live in the host app via `StrategyContract` implementations.

## Installation

```bash
composer require beegoodit/filament-streaks
```

Publish config, migrations, and optional translations:

```bash
php artisan vendor:publish --tag=filament-streaks-config
php artisan vendor:publish --tag=filament-streaks-migrations
php artisan vendor:publish --tag=filament-streaks-lang   # optional overrides
php artisan migrate
```

Register the Filament plugin on your admin panel:

```php
use BeegoodIT\FilamentStreaks\Filament\FilamentStreaksPlugin;

$panel->plugin(FilamentStreaksPlugin::make());
```

Implement at least one strategy class and register it in `config/filament-streaks.php` (see below).

## Configuration overview

`config/filament-streaks.php`:

| Key | Purpose |
|-----|---------|
| `user_model` | Auth user model for Admin userstamps (`created_by` / `updated_by`) — **not** streak subject eligibility |
| `notification_channels` | Laravel notification channels, e.g. `['mail']` or `['mail', 'webPush']` |
| `admin_event_models` | Morph types shown in qualification forms (host events) |
| `event_title_attributes` / `scope_title_attributes` | Display labels for morph selects |
| `subject_title_attributes` | Optional display labels for subject MorphToSelect (default `name`) |
| `strategies` | Named strategy configs (engine keys + optional host `ui` presentation) |

Each strategy entry:

```php
'play_week' => [
    'strategy' => \App\Strategies\Streaks\PlayWeekStrategy::class,
    'open_on' => 'first_event',       // or 'at_risk'
    'active_from' => '2026-01-01',    // optional; strategy may override
    'active_to' => null,
    'when_inactive' => 'break',       // or 'pause'
    'at_risk' => [
        'notifications' => true,
        'lead' => '24h',              // duration before period end
    ],
    'breaks' => [
        'grace_period' => '2d',
        'notifications' => true,
        'notification_delay' => '5m',
    ],
    'extended' => [
        'notifications' => false,     // in-app only in host UI when false
    ],
    'ui' => [                         // optional; ignored by the engine
        'icon' => 'heroicon-o-fire',
        'color' => 'warning',
        'label' => 'player.streaks.play_week',
    ],
],
```

Duration strings use `5m`, `1h`, `2d`, etc. (parsed by the package `DurationParser`).

Optional `ui` keys are for host presentation (player chrome / hero). The engine never reads them.

## StrategyContract

Host strategies implement `BeegoodIT\FilamentStreaks\Contracts\StrategyContract`:

| Method | Responsibility |
|--------|----------------|
| `subjectType()` | Eloquent class for the streak subject (usually your user model) |
| `subjects()` | Eloquent `Builder` of eligible subjects (history walk, Admin picker, record guard). Default pattern: `subjectType()::query()` narrowed as needed |
| `scopeType()` | Optional scope model class, or `null` for overall streaks |
| `periodicity()` | `PeriodicityContract` (package ships `IsoWeekStrategy`) |
| `activeFrom(?Model $scope)` | Start of activity window for this run (may ignore config) |
| `activeTo(?Model $scope)` | End of activity window, or `null` |
| `events(...)` | Return `Collection<StreakEventData>` for a subject/scope and period range |

The engine calls `events()` to find fuel, writes `Qualification` rows, and uses periodicity + config to decide extend, at-risk, and break behaviour.

Wire fuel in the host (observers, jobs, or manual history replay) by calling `RecordStreakEvents::run(...)` and scheduling `EvaluateStreakPeriods`.

## Derived `current`

`Streak::currentCount()` / `StreakCurrentCalculator` returns a **Duolingo-style consecutive length** ending at “now” (subject timezone):

1. Walk periods newest → oldest from `start_at` through now (or `end_at`).
2. If the **current** period has no qualification yet, **skip it** (does not inflate).
3. Count consecutive periods that each have ≥1 qualification.
4. Stop at the first finished period without a qualification.

Open runs with historical gaps therefore show the trailing consecutive length, not “all periods since `start_at`”.

## `start_at` / `end_at` (cached bounds)

Each streak row is one contiguous run. Bounds are denormalized period edges (not “job ran on”):

| Column | Meaning |
|--------|---------|
| `start_at` | First day of the first qualified period (ISO week → Monday) |
| `end_at` | Last day of the **last qualified** period (ISO week → Sunday); `null` = open |

Closing a run (evaluate past grace, or record detecting a skipped period before a new event) sets `end_at` to that last qualified period end — **not** `now()` and **not** the empty miss week. The next play opens a **new** row.

Evaluate/history still close open runs when grace expires with no fuel; record splits history into multiple rows across gaps.

## Commands

Schedule these in the host app (typical: hourly):

```php
Schedule::command('filament-streaks:evaluate')->hourly();
Schedule::command('filament-streaks:at-risk')->hourly();
```

| Command | Description |
|---------|-------------|
| `php artisan filament-streaks:evaluate` | Close periods past grace with no qualifications; send broken notifications when enabled |
| `php artisan filament-streaks:at-risk` | Send at-risk notifications within the lead window (once per streak + period) |
| `php artisan filament-streaks:history {from} {to}` | Queue a history replay run (record + evaluate). Use `--sync` to run inline |

Examples:

```bash
php artisan filament-streaks:evaluate
php artisan filament-streaks:at-risk
php artisan filament-streaks:history 2026-01-01 2026-03-31
php artisan filament-streaks:history 2026-01-01 2026-03-31 --sync
```

History creates an **operation run** (`streaks.history`), queues `ProcessStreakHistoryRun` by default, then walks every configured strategy and eligible subject from `subjects()` (and scope when required), runs the same record path as live fuel, and evaluates as of **`min(to, now)`** so a future range end cannot close streaks that are still live. Prefer the Filament **History** resource under the Streaks cluster for day-to-day ops.

## Notifications

When `at_risk.notifications` or `breaks.notifications` is `true`, the engine sends Laravel notifications using channels from `notification_channels`.

- **Mail** — `AtRiskNotification` / `BrokenNotification` with translated subject and body (`resources/lang/*/notification.php`).
- **Web push** — optional `webPush` channel when `beegoodit/laravel-pwa` is installed; push copy uses the `push.*` translation keys.

Sent channel notifications are logged in `beegoodit_filament_streaks_notifications` with kind `at_risk` or `broken` and unique `(streak_id, period_key, kind)` so repeats are suppressed.

Set `extended.notifications => false` to keep “streak saved” feedback in-app only (host responsibility).

## Filament admin

Resources register via `FilamentStreaksPlugin`:

- **Overview** — `/admin/streaks` — currently open (running) streaks with current count and at-risk status.
- **Streaks** — `/admin/streaks/streaks` — list/create/edit runs; edit page includes **Qualifications** and **Notifications** relation managers.
- **Qualifications** — `/admin/streaks/qualifications` — manual proof rows (support).
- **Notifications** — `/admin/streaks/notifications` — sent log (support).
- **History** — `/admin/streaks/history` — queued date-range replays (create → show with live status; database notification to the requester when finished).

Policies ship for admin authorization; customize in the host if needed.

## Testing

Package tests (Pest):

```bash
composer test
# or
./vendor/bin/pest
```

Cover strategies, period evaluation, notification caps, and open-streak uniqueness in the host app with feature tests that call the same actions/commands.

## License

MIT
