<?php
declare(strict_types=1);

use App\Config\Database;
use App\Config\Env;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Middleware\AuthMiddleware;
use App\Repositories\AuthTokenRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;

return static function (Router $router): void {
    $router->get('/api/v1/health', static function () {
        return [
            'success' => true,
            'data' => [
                'status' => 'ok',
                'service' => 'garage-manager-api',
                'timestamp' => gmdate(DATE_ATOM),
            ],
        ];
    });

    $buildAuthController = static function (): AuthController {
        $connection = Database::getConnection();

        $configuredTtl = (int) (Env::get('TOKEN_TTL_HOURS', '24') ?? '24');
        $tokenTtlHours = $configuredTtl > 0 ? $configuredTtl : 24;

        $userRepository = new UserRepository($connection);
        $authTokenRepository = new AuthTokenRepository($connection);
        $authService = new AuthService($userRepository, $authTokenRepository, $tokenTtlHours);

        return new AuthController($authService);
    };

    $buildAuthMiddleware = static function (): AuthMiddleware {
        $connection = Database::getConnection();
        $authTokenRepository = new AuthTokenRepository($connection);
        return new AuthMiddleware($authTokenRepository);
    };

    $buildUserController = static function (): UserController {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        return new UserController($userRepository);
    };

    $router->post('/api/v1/auth/register', static function (Request $request) use ($buildAuthController): Response {
        try {
            return $buildAuthController()->register($request);
        } catch (\Throwable) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'No se pudo completar el registro.',
                    'details' => [],
                ],
            ], 500);
        }
    });

    $router->post('/api/v1/auth/login', static function (Request $request) use ($buildAuthController): Response {
        try {
            return $buildAuthController()->login($request);
        } catch (\Throwable) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'No se pudo completar el login.',
                    'details' => [],
                ],
            ], 500);
        }
    });

    $router->get('/api/v1/users/me', static function (Request $request) use ($buildAuthMiddleware, $buildUserController): Response {
        try {
            $authResult = $buildAuthMiddleware()->authenticate($request);
            if ($authResult instanceof Response) {
                return $authResult;
            }

            return $buildUserController()->me($authResult['user_id']);
        } catch (\Throwable) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'No se pudo obtener el perfil.',
                    'details' => [],
                ],
            ], 500);
        }
    });
};
