<?php
declare(strict_types=1);

use App\Config\Env;
use App\Http\Request;
use App\Http\Router;

const BASE_PATH = __DIR__ . '/../';
const COMPOSER_AUTOLOAD = BASE_PATH . 'vendor/autoload.php';

if (is_file(COMPOSER_AUTOLOAD)) {
    require COMPOSER_AUTOLOAD;
} else {
    spl_autoload_register(static function (string $className): void {
        $prefix = 'App\\';
        $baseDir = BASE_PATH . 'src/';

        if (!str_starts_with($className, $prefix)) {
            return;
        }

        $relativeClass = substr($className, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    });
}

Env::load(BASE_PATH . '.env');

$request = Request::fromGlobals();
$router = new Router();

$registerRoutes = require BASE_PATH . 'routes/api.php';
$registerRoutes($router);

$response = $router->dispatch($request);
$response->send();