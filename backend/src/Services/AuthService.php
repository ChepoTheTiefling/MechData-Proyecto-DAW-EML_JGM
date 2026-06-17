<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuthTokenRepository;
use App\Repositories\UserRepository;
use DateTimeImmutable;
use PDOException;
use RuntimeException;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly int $tokenTtlHours = 24,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function register(string $name, string $email, string $password): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $cleanName = trim($name);

        $existingUser = $this->userRepository->findByEmail($normalizedEmail);
        if ($existingUser !== null) {
            throw new AuthException(409, 'EMAIL_ALREADY_EXISTS', 'El email ya esta registrado.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($passwordHash) || $passwordHash === '') {
            throw new RuntimeException('No se pudo generar el hash de contrasena.');
        }

        try {
            $user = $this->userRepository->create($cleanName, $normalizedEmail, $passwordHash);
        } catch (PDOException $exception) {
            if ($this->isDuplicateKeyError($exception)) {
                throw new AuthException(409, 'EMAIL_ALREADY_EXISTS', 'El email ya esta registrado.');
            }

            throw $exception;
        }

        return $this->sanitizeUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function login(string $email, string $password): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $user = $this->userRepository->findByEmail($normalizedEmail);

        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            throw new AuthException(401, 'INVALID_CREDENTIALS', 'Credenciales invalidas.');
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $ttlHours = $this->tokenTtlHours > 0 ? $this->tokenTtlHours : 24;
        $expiresAt = (new DateTimeImmutable())->modify('+' . $ttlHours . ' hours');
        $this->authTokenRepository->create(
            (int) $user['id'],
            $tokenHash,
            $expiresAt->format('Y-m-d H:i:s')
        );

        return [
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->format(DATE_ATOM),
            'user' => $this->sanitizeUser($user),
        ];
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function sanitizeUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
        ];
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function isDuplicateKeyError(PDOException $exception): bool
    {
        return (string) $exception->getCode() === '23000';
    }
}
