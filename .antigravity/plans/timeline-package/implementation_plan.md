# Abstract Timeline Package (`beegoodit/filament-timeline`)

> **Vision**: Create a reusable, abstract package in the `beegoodit-laravel` monorepo to handle a chronological history feed for any Eloquent model using a Discovery Aggregator and Flux UI.

## Proposed Changes

### [Phase 1] Core Structure & DTOs

#### [NEW] [TimelineEntry.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Data/TimelineEntry.php)
- **DTO**: Holds the data for a single timeline item (title, body, icon, color, timestamp, url).

#### [NEW] [ProvidesTimelineEvent.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Contracts/ProvidesTimelineEvent.php)
- **Interface (Singular)**: For classes that *are* a single event (e.g., Domain Events).
- Method: `toTimelineEvent(): TimelineEntry`.

#### [NEW] [ProvidesTimeline.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Contracts/ProvidesTimeline.php)
- **Interface (Plural)**: For classes that *contain* multiple history points (e.g., Models).
- Method: `toTimeline(): Collection<TimelineEntry>`.

#### [NEW] [TimelineProvider.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Contracts/TimelineProvider.php)
- **Interface**: `discover(Model $subject): Collection<TimelineEntry>`.

#### [NEW] [HasTimeline.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Concerns/HasTimeline.php)
- **Trait**: Provides a `getTimeline()` method that utilizes the Aggregator.

### [Phase 2] The Discovery Engine

#### [NEW] [TimelineAggregator.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Services/TimelineAggregator.php)
- **Engine**: Accepts a model, finds its registered Providers, and merges/sorts their results.

#### [NEW] [Filament-Timeline Service Provider](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/FilamentTimelineServiceProvider.php)
- Package registration and config publishing.

### [Phase 3] Responsive UI (Flux UI)

#### [NEW] [TimelineWidget.php](file:///home/robo/projects/composer/beegoodit-laravel/packages/filament-timeline/src/Components/TimelineWidget.php)
- **Livewire Component**: Renders the vertical feed.
- **Frontend**: Uses Flux UI Free components for better aesthetics.
- **Responsiveness**: Horizontal option for desktop/wider viewports if appropriate, or a clean mobile-first vertical list.

## Verification Plan

### Automated Tests
- Implementation of mock providers and models to verify aggregation logic in the package's own test suite.
