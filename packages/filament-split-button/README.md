# beegoodit/filament-split-button

Filament **split button**: a fused `[ Label | ⌵ ]` control. The label runs a primary `Action`; the chevron opens a dropdown of more actions.

Built on Filament [`ActionGroup::buttonGroup()`](https://filamentphp.com/docs/5.x/actions/grouping-actions#using-a-grouped-button-design) plus a nested dropdown group — not a reimplementation of the Blade dropdown component.

## Install

```bash
composer require beegoodit/filament-split-button
```

## Usage

```php
use BeegoodIT\FilamentSplitButton\SplitButton;
use Filament\Actions\Action;

SplitButton::make(
    Action::make('publish')
        ->label('Publish')
        ->action(fn () => $this->publish()),
    [
        Action::make('schedule')
            ->label('Schedule…')
            ->action(fn () => $this->schedule()),
        Action::make('saveDraft')
            ->label('Save draft')
            ->action(fn () => $this->saveDraft()),
    ],
);
```

Use the returned `ActionGroup` anywhere Filament accepts actions (header actions, schema `belowContent()`, `Actions::make([...])`, etc.).

### Options

| Argument | Default | Purpose |
|----------|---------|---------|
| `$color` | `'primary'` | Shared color for label + chevron |
| `$dropdownIcon` | `Heroicon::ChevronDown` | Chevron icon |
| `$dropdownTooltip` | translated “More options” | Chevron tooltip |
| `$dropdownPlacement` | `'bottom-end'` | Dropdown placement |

Give every action a **unique** `make()` name within the Livewire page — including across primary vs dropdown.

### Theming

Pass Filament color names (`primary`, `danger`, `gray`, …) via `$color` — both segments use Action `->color()`, so fill/text follow the panel theme. The join divider is a light/dark contrast line (not brand hues). Do not hardcode brand colors in app CSS.

### Assets

The package registers CSS for join rounding, chevron padding, and the divider. Markup includes `fi-split-button` on the group. After install or CSS changes:

```bash
php artisan filament:assets
```

Stylesheet URLs use the CSS file mtime as `?v=` so path-repo / branch package versions do not leave browsers stuck on a stale sheet.
