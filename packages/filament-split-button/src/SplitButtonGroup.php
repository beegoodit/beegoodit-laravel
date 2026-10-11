<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentSplitButton;

use Filament\Actions\ActionGroup;

/**
 * ActionGroup::buttonGroup() hardcodes {@code class="fi-btn-group"} and drops
 * extraAttributes. This subclass adds {@code fi-split-button} so package CSS
 * can target the control without fragile :has() matching alone.
 */
final class SplitButtonGroup extends ActionGroup
{
    public function toEmbeddedHtml(): string
    {
        if (! $this->isButtonGroup()) {
            return parent::toEmbeddedHtml();
        }

        ob_start(); ?>

        <div class="fi-btn-group fi-split-button">
            <?php foreach ($this->getActions() as $action) { ?>
                <?php if ($action->isVisible()) { ?>
                    <?= $action->toHtml() ?>
                <?php } ?>
            <?php } ?>
        </div>

        <?php return (string) ob_get_clean();
    }
}
