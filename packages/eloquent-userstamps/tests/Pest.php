<?php

use BeeGoodIT\EloquentUserstamps\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

uses(TestCase::class)->in(__DIR__);

// Ensure Model connection resolver is set before each test
beforeEach(function () {
    // Set the connection resolver from the app container
    Model::setConnectionResolver($this->app['db']);
    Model::setEventDispatcher($this->app['events']);
});

