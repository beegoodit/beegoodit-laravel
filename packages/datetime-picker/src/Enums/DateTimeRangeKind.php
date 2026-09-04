<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Enums;

enum DateTimeRangeKind: string
{
    case Day = 'day';
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';
    case Custom = 'custom';
}
