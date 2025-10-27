<?php
require __DIR__ . '/../vendor/autoload.php';

// Boot the framework partially so view() helper works
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Create a container and bind view environment
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Instantiate controller and call viewBill
$c = new App\Http\Controllers\Admin\BillingController();
try {
    $response = $c->viewBill(1);
    // The controller returns a View instance which when sent via HTTP would be converted
    echo 'Controller returned: ' . gettype($response) . "\n";
    if ($response instanceof Illuminate\View\View) {
        echo 'Rendering view to string...\n';
        echo $response->render();
    }
} catch (Throwable $e) {
    echo 'Exception: ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
