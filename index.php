<?php
require_once 'functions/response.php';
require_once 'config/database.php';
require_once 'routes.php';

// 
// 
require_once 'controllers/HomeController.php'; 
require_once 'controllers/AuthController.php'; 
require_once 'controllers/UserController.php'; 
require_once 'controllers/NewsController.php'; 
require_once 'controllers/TransactionController.php'; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

if (array_key_exists($url, $routes)) {
    list($controllerName, $method) = $routes[$url];
    $controller = new $controllerName();
    call_user_func([$controller, $method]);
} else {
    http_response_code(404);
    echo "404 - Halaman tidak ditemukan";
}