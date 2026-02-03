<?php

namespace BeegoodIT\LaravelPwa\States\Messages;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class MessageState extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, OnHold::class)
            ->allowTransition(OnHold::class, Pending::class)
            ->allowTransition(Pending::class, Sent::class)
            ->allowTransition(Pending::class, Failed::class)
            ->allowTransition(Sent::class, Pending::class) // For Resend
            ->allowTransition(Failed::class, Pending::class); // For Resend
    }
}
