<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentSplitButton;

use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class SplitButton
{
    /**
     * Build a fused [ primary | ⌵ ] control: the label runs `$primary`, the chevron opens `$dropdownActions`.
     *
     * @param  list<Action|ActionGroup>  $dropdownActions
     * @param  string|array<int|string, string|int>|Closure|null  $color
     */
    public static function make(
        Action $primary,
        array $dropdownActions = [],
        string|array|Closure|null $color = 'primary',
        string|BackedEnum|Htmlable|Closure|null $dropdownIcon = null,
        string|Htmlable|Closure|null $dropdownTooltip = null,
        string|Closure|null $dropdownPlacement = 'bottom-end',
    ): ActionGroup {
        $primary = $primary
            ->button()
            ->color($color);

        $dropdown = ActionGroup::make($dropdownActions)
            ->label($dropdownTooltip ?? __('filament-split-button::split_button.more'))
            ->icon($dropdownIcon ?? Heroicon::ChevronDown)
            ->button()
            ->hiddenLabel()
            ->color($color)
            ->dropdownPlacement($dropdownPlacement)
            ->tooltip($dropdownTooltip ?? __('filament-split-button::split_button.more'));

        return SplitButtonGroup::make([
            $primary,
            $dropdown,
        ])->buttonGroup();
    }
}
