<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TestOpenable extends Model
{
    use HasUuids;

    protected $table = 'opening_times_test_openables';

    protected $fillable = [];
}
