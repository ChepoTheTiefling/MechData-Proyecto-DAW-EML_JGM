<?php
declare(strict_types=1);

namespace App\Http;

final class Router
{
    /** @var array<string, array<string, callable(Request):array|Response>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, callable $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $this->normalizePath($request->path());
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Ruta no encontrada.',
                ],
            ], 404);
        }

        $result = $handler($request);

        if ($result instanceof Response) {
            return $result;
        }

        return Response::json($result);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '' || $trimmed === '/') {
            return '/';
        }

        return '/' . trim($trimmed, '/');
    }
}
