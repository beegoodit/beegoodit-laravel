# Filament Timeline Package

An abstract chronological history feed for Filament applications.

## Features
- **Abstract Discovery**: Decoupled from specific models via `ProvidesTimeline` and `ProvidesTimelineEvent` contracts.
- **Multi-Source Aggregation**: Collect entries from models and external `TimelineProvider` services.
- **Horizontal & Vertical Layouts**: Responsive timeline UI with smooth scrolling.
- **Premium Interaction**: 
    - Drag-to-scroll
    - Dynamic width measurement
    - Fading edge effects
    - Year-clustered navigation dots
- **Fully Localized**: Support for English, German, and Spanish.

## Installation

```bash
composer require beegoodit/filament-timeline
```

## Configuration

Register your providers in `config/filament-timeline.php`:

```php
return [
    'providers' => [
        App\Services\Timeline\Providers\AchievementProvider::class,
    ],
];
```

## Usage

### 1. Implement Contracts

Add the `ProvidesTimeline` interface to your model:

```php
use BeegoodIT\FilamentTimeline\Contracts\ProvidesTimeline;
use BeegoodIT\FilamentTimeline\Data\TimelineEntry;

class Project extends Model implements ProvidesTimeline
{
    public function toTimeline(): Collection
    {
        return $this->milestones->map(fn ($m) => TimelineEntry::make(
            title: $m->name,
            occurredAt: $m->completed_at
        ));
    }
}
```

### 2. UI Component

Add the `TimelineWidget` to your Filament page or dashboard:

```php
protected function getFooterWidgets(): array
{
    return [
        TimelineWidget::make([
            'subject' => $this->record,
            'direction' => 'horizontal', // or 'vertical'
        ]),
    ];
}
```

## Testing

The package includes a Pest-based test suite.

```bash
vendor/bin/pest
```

## Standards
- **PHP**: 8.4+
- **Laravel**: 12.0+
- **Filament**: 4.0+
- **Livewire**: 3.0+
- **Code Style**: PSR-12 (via Pint/Rector)
- **Static Analysis**: PHPStan Level Max
