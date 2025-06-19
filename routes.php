<?php

$routes = [
    '' => ['HomeController', 'index'],

    // Auth Route
    'login' => ['AuthController', 'login'],
    'registration' => ['AuthController', 'registration'],

    // Profile
    'update-profile' => ['ProfileController', 'update'],

    'news' => ['NewsController', 'index'],
    'store-news' => ['NewsController', 'store'],
    'show-news' => ['NewsController', 'show'],
    'update-news' => ['NewsController', 'update'],
    'destroy-news' => ['NewsController', 'destroy'],
];
