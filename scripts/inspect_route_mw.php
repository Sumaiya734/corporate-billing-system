<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/','GET');
$routes = $app['router']->getRoutes();
$route = $routes->getByName('admin.dashboard');
if (!$route) {
    echo "Route not found\n";
    exit(1);
}
$mw = $route->gatherMiddleware();
echo json_encode($mw, JSON_PRETTY_PRINT) . "\n";
