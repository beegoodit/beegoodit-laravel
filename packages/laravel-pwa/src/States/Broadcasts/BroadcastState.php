<?php

namespace BeegoodIT\LaravelPwa\States\Broadcasts;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BroadcastState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, OnHold::class)
            ->allowTransition(OnHold::class, Pending::class)
            ->allowTransition(Pending::class, Processing::class)
            ->allowTransition(Processing::class, Completed::class)
            ->allowTransition(Processing::class, Failed::class)
            ->allowTransition(Completed::class, Pending::class) // For Resend
            ->allowTransition(Failed::class, Pending::class); // For Resend
    }
}
