<?php

namespace BeegoodIT\FilamentLegal\Http\Controllers;

use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

class LegalController extends Controller
{
    private const PAGE_MAP = [
        'imprint' => ['default' => 'imprint', 'branded' => 'branded.imprint', 'policy' => 'imprint'],
        'privacy' => ['default' => 'privacy-policy', 'branded' => 'branded.privacy-policy', 'policy' => 'privacy'],
        'cookie' => ['default' => 'cookie-policy', 'branded' => 'branded.cookie-policy', 'policy' => 'cookie-policy'],
    ];

    public function imprint(): View
    {
        return $this->showPage('imprint');
    }

    public function privacyPolicy(): View
    {
        return $this->showPage('privacy');
    }

    public function cookiePolicy(): View
    {
        return $this->showPage('cookie');
    }

    private function showPage(string $type): View
    {
        $config = self::PAGE_MAP[$type] ?? throw new \InvalidArgumentException("Invalid legal page type: {$type}");

        $owner = $this->resolveOwner();

        if (! $owner instanceof Model) {
            return view($config['default']);
        }

        return view($config['branded'], [
            'identity' => $owner->legalIdentity ?? null,
            'locations' => $owner->getAttribute('locations'),
            'policy' => LegalPolicy::getActive($config['policy'], $owner),
            'team' => $owner,
        ]);
    }

    private function resolveOwner(): mixed
    {
        if (! app()->bound('resolvedEntity')) {
            return null;
        }

        return resolve('resolvedEntity');
    }
}
