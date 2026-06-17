<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, name, email, password_hash, created_at, updated_at
                FROM users
                WHERE email = :email
                LIMIT 1';
        $statement = $this->connection->prepare($sql);
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT id, name, email, created_at, updated_at
                FROM users
                WHERE id = :id
                LIMIT 1';
        $statement = $this->connection->prepare($sql);
        $statement->execute(['id' => $id]);

        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function create(string $name, string $email, string $passwordHash): array
    {
        $sql = 'INSERT INTO users (name, email, password_hash)
                VALUES (:name, :email, :password_hash)';
        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        return [
            'id' => (int) $this->connection->lastInsertId(),
            'name' => $name,
            'email' => $email,
        ];
    }
}
