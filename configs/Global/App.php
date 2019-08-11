<?php

//Configs in directory Local will override configs in directory Global

return [
    'middleware' => [
        \App\Api\Middleware\Auth\InitUser::class
    ]
];