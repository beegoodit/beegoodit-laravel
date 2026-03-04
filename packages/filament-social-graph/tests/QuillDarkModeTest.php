<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use Illuminate\Support\Facades\View;

class QuillDarkModeTest extends TestCase
{
    public function test_quill_dark_mode_partial_includes_dark_mode_css(): void
    {
        $html = View::make('filament-social-graph::feed.partials.quill-dark-mode')->render();

        $this->assertStringContainsString('.dark .ql-toolbar.ql-snow', $html);
        $this->assertStringContainsString('--color-zinc-800', $html);
    }
}
