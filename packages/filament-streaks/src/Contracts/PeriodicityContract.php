<?php

namespace BeegoodIT\FilamentStreaks\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface PeriodicityContract
{
    /**
     * @return array{key: string, start: CarbonInterface, end: CarbonInterface}
     */
    public function periodContaining(CarbonInterface $instant, string $timezone): array;

    /**
     * @return Collection<int, array{key: string, start: CarbonInterface, end: CarbonInterface}>
     */
    public function periodsBetween(CarbonInterface $from, CarbonInterface $to, string $timezone): Collection;
}
