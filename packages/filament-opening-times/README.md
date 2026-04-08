# filament-opening-times

Weekly **opening hours** for Laravel and **Filament**: polymorphic **`Schedule`** + **`Slot`** models, timezone-aware evaluation, non-overlapping active windows, slot validation (including overnight intervals), Laravel Actions for upsert and evaluation, optional Filament resource and relation manager.

**Tables:** `opening_times_schedules`, `opening_times_slots` (FK `schedule_id`). Names are prefixed for grouping and collision avoidance in shared databases.

## Install

```bash
composer require beegoodit/filament-opening-times
```

The service provider is auto-discovered. **Publish migrations** once, then migrate:

```bash
php artisan vendor:publish --tag=filament-opening-times-migrations
php artisan migrate
```

If `config('database.migrations.update_date_on_publish')` is `true`, Laravel may rewrite migration filenames on publish. Rename the copied files to match the package prefixes (`2026_04_07_000001_*`, `2026_04_07_000002_*`) before committing, or disable that option for a stable name.

Publish tags: `filament-opening-times-migrations`, `filament-opening-times-config`, `filament-opening-times-lang`.

## Configuration

Publish or add `config/filament-opening-times.php`:

- `openable_models` — Eloquent classes allowed in the standalone resource `MorphToSelect` (e.g. `[\App\Models\Location::class]`).

## Filament

1. Register **`ScheduleResource`** on the **admin** panel `resources([...])` for full CRUD.
2. Add the translated navigation group to your panel’s `navigationGroups()` — the resource uses `__('filament-opening-times::opening_schedule.navigation_group')`.
3. On a **portal** (or other panel), attach **`OpeningTimeSchedulesRelationManager`** to a resource whose model defines **`openingTimeSchedules()`** as `morphMany(\BeegoodIT\FilamentOpeningTimes\Models\Schedule::class, 'openable')`. Omit the standalone resource from that panel if hours should only appear on the parent record.

## Authorization

**`SchedulePolicy`** delegates `view`, `update`, `delete`, etc. to the **`openable`** model (`Gate` checks on the related record).

## Actions

- **`UpsertScheduleWithSlots`** — creates/updates a schedule, replaces slots, validates overlaps (active window vs other schedules for the same openable; slot overlaps per weekday including overnight splits).
- **`EvaluateScheduleForOpenable`** — returns **`ScheduleStatusResult`** (`has_active_schedule`, `is_open`, optional `next_transition` placeholder) for a given instant.

## Tests (monorepo)

From the BeegoodIT Laravel packages repository root:

```bash
./vendor/bin/phpunit packages/filament-opening-times/tests/
```

## Early adopters (schema changes)

If you already ran older package migrations (`bg_opening_*`, `opening_schedules`, etc.), add a one-off rename/drop in your app or refresh a dev database before relying on `opening_times_*` + `schedule_id`.

## License

MIT. See [LICENSE](LICENSE).
