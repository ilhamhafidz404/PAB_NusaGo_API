<?php

$routes = [
    '' => ['HomeController', 'index'],

    // Auth Route
    'login' => ['AuthController', 'login'],
    'registration' => ['AuthController', 'registration'],

    // Profile
    'users' => ['UserController', 'index'],
    'update-user' => ['UserController', 'update'],
    'delete-user' => ['UserController', 'delete'],

    // News
    'news' => ['NewsController', 'index'],
    'store-news' => ['NewsController', 'store'],
    'show-news' => ['NewsController', 'show'],
    'update-news' => ['NewsController', 'update'],
    'destroy-news' => ['NewsController', 'destroy'],

    // Transaction
    'transactions' => ['TransactionController', 'index'],
    'store-transaction' => ['TransactionController', 'store'],
    'export-transaction' => ['TransactionController', 'export'],
];
