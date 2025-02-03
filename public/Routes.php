<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/models/Database.php';

$pdo = getPDOInstance();

$router = new AltoRouter();

$router->setBasePath('/aduan2');

function checkSession()
{
    if (!isset($_SESSION['token'])) {
        header('Location: /aduan2/');
        exit;
    }
}

// Define routes
$router->map('GET', '/', function () {
    require '../src/views/login/index.php';
});

$router->map('GET', '/index', function () {
    require '../src/views/login/index.php';
});

$router->map('GET|POST', '/register', function () use ($pdo) {
    require '../src/views/register/index.php';
});

$router->map('GET|POST', '/proses_login', function () use ($pdo) {
    require '../src/controllers/AuthController.php';
});

// Views routes with session check
$router->map('GET|POST', '/dashboard', function () use ($pdo) {
    checkSession();
    require '../src/views/dashboard/index.php';
});

// Views routes with session check
$router->map('GET|POST', '/dashboardcontent', function () use ($pdo) {
    checkSession();
    require '../src/views/dashboard/Content.php';
});

$router->map('GET|POST', '/koordinator', function () use ($pdo) {
    checkSession();
    require '../src/views/koordinator/index.php';
});

$router->map('GET|POST', '/pic', function () use ($pdo) {
    checkSession();
    require '../src/views/pic/index.php';
});

$router->map('GET|POST', '/logs', function () use ($pdo) {
    checkSession();
    require '../src/views/logs/index.php';
});

$router->map('GET|POST', '/completestatus', function () use ($pdo) {
    checkSession();
    require '../src/views/complete_status/index.php';
});

$router->map('GET|POST', '/about', function () use ($pdo) {
    checkSession();
    require '../src/views/about/index.php';
});

$router->map('GET|POST', '/SearchSuggestions', function () use ($pdo) {
    checkSession();
    require '../src/controllers/SearchSuggestions.php';
});

$router->map('GET|POST', '/edit_complaint', function () use ($pdo) {
    checkSession();
    require '../src/controllers/ComplaintController.php';
});

$router->map('GET|POST', '/update_complaint', function () use ($pdo) {
    checkSession();
    require '../src/controllers/UpdateComplaint.php';
});

$router->map('GET|POST', '/logout', function () use ($pdo) {
    checkSession();
    require '../src/controllers/logout.php';
});

$router->map('GET|POST', '/proses_register', function () use ($pdo) {
    checkSession();
    require '../src/controllers/register.php';
});

// Match current request URL
$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // No route was matched
    require '../src/views/errors/404.php';
}
