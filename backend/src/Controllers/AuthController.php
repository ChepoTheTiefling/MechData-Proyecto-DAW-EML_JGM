<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthException;
use App\Services\AuthService;
use App\Utils\Validator;
use Throwable;

final class AuthController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(Request $request): Response
    {
        $payload = $request->json();
        $errors = Validator::validateRegister($payload);

        if ($errors !== []) {
            return $this->errorResponse(
                422,
                'VALIDATION_ERROR',
                'Datos de registro no validos.',
                $errors
            );
        }

        try {
            $user = $this->authService->register(
                (string) ($payload['name'] ?? ''),
                (string) ($payload['email'] ?? ''),
                (string) ($payload['password'] ?? '')
            );
        } catch (AuthException $exception) {
            return $this->errorResponse(
                $exception->statusCode(),
                $exception->errorCode(),
                $exception->getMessage()
            );
        } catch (Throwable) {
            return $this->errorResponse(500, 'INTERNAL_ERROR', 'No se pudo completar el registro.');
        }

        return Response::json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
            'message' => 'Usuario registrado correctamente.',
        ], 201);
    }

    public function login(Request $request): Response
    {
        $payload = $request->json();
        $errors = Validator::validateLogin($payload);

        if ($errors !== []) {
            return $this->errorResponse(
                422,
                'VALIDATION_ERROR',
                'Datos de login no validos.',
                $errors
            );
        }

        try {
            $authData = $this->authService->login(
                (string) ($payload['email'] ?? ''),
                (string) ($payload['password'] ?? '')
            );
        } catch (AuthException $exception) {
            return $this->errorResponse(
                $exception->statusCode(),
                $exception->errorCode(),
                $exception->getMessage()
            );
        } catch (Throwable) {
            return $this->errorResponse(500, 'INTERNAL_ERROR', 'No se pudo completar el login.');
        }

        return Response::json([
            'success' => true,
            'data' => $authData,
            'message' => 'Login correcto.',
        ]);
    }

    /**
     * @param array<int, array<string, string>> $details
     */
    private function errorResponse(int $status, string $code, string $message, array $details = []): Response
    {
        return Response::json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }
}
