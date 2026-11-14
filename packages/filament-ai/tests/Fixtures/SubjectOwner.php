<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentAi\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class SubjectOwner extends Model
{
    protected $table = 'filament_ai_subject_owners';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];
}
