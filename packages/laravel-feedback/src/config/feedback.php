<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class that will be used for feedback submissions.
    |
    */

    'user_model' => env('FEEDBACK_USER_MODEL', \App\Models\User::class),
];
