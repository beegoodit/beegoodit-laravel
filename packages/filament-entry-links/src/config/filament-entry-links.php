<?php

return [

    'route_prefix' => 'link',

    'middleware' => ['web'],

    'allowed_url_mode' => 'same_app',

    'allowed_hosts' => [],

    'home_url' => null,

    /*
    | When set (e.g. layouts.public), unavailable and coming-soon pages @extend this layout.
    | The layout should @yield('content'). Use @section('title', ...) for the document title if your head reads it.
    */
    'public_layout' => null,

];
