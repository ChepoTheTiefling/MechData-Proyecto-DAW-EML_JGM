<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AuthTokenRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function create(int $userId, string $tokenHash, string $expiresAt): void
    {
        $sql = 'INSERT INTO auth_tokens (user_id, token_hash, expires_at)
                VALUES (:user_id, :token_hash, :expires_at)';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findValidByHash(string $tokenHash): ?array
    {
        $sql = 'SELECT id, user_id, expires_at
                FROM auth_tokens
                WHERE token_hash = :token_hash
                  AND revoked_at IS NULL
                  AND expires_at > NOW()
                LIMIT 1';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'token_hash' => $tokenHash,
        ]);

        $token = $statement->fetch();
        return is_array($token) ? $token : null;
    }
}
