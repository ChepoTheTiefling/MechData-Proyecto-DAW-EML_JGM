<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuthTokenRepository;

final class AuthMiddleware
{
    public function __construct(private readonly AuthTokenRepository $authTokenRepository)
    {
    }

    /**
     * @return array{user_id:int, token_id:int}|Response
     */
    public function authenticate(Request $request): array|Response
    {
        $authorization = $request->header('Authorization');
        if ($authorization === null || trim($authorization) === '') {
            return $this->unauthorized();
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', trim($authorization), $matches)) {
            return $this->unauthorized();
        }

        $plainToken = trim($matches[1]);
        if ($plainToken === '') {
            return $this->unauthorized();
        }

        $tokenHash = hash('sha256', $plainToken);
        $token = $this->authTokenRepository->findValidByHash($tokenHash);

        if ($token === null) {
            return $this->unauthorized();
        }

        return [
            'user_id' => (int) $token['user_id'],
            'token_id' => (int) $token['id'],
        ];
    }

    private function unauthorized(): Response
    {
        return Response::json([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'No autorizado.',
                'details' => [],
            ],
        ], 401);
    }
}
