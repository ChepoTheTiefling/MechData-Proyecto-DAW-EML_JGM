<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Repositories\UserRepository;

final class UserController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function me(int $authUserId): Response
    {
        $user = $this->userRepository->findById($authUserId);

        if ($user === null) {
            return Response::json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'No autorizado.',
                    'details' => [],
                ],
            ], 401);
        }

        return Response::json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (int) $user['id'],
                    'name' => (string) $user['name'],
                    'email' => (string) $user['email'],
                ],
            ],
        ]);
    }
}
