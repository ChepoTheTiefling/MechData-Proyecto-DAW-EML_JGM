<?php
declare(strict_types=1);

namespace App\Utils;

final class Validator
{
    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, string>>
     */
    public static function validateRegister(array $payload): array
    {
        $errors = [];

        $name = self::readString($payload, 'name');
        if ($name === '') {
            $errors[] = self::error('name', 'El nombre es obligatorio.');
        } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $errors[] = self::error('name', 'El nombre debe tener entre 2 y 120 caracteres.');
        }

        $email = self::readString($payload, 'email');
        if ($email === '') {
            $errors[] = self::error('email', 'El email es obligatorio.');
        } elseif (mb_strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = self::error('email', 'El email no tiene un formato valido.');
        }

        $password = self::readString($payload, 'password');
        if ($password === '') {
            $errors[] = self::error('password', 'La contrasena es obligatoria.');
        } elseif (strlen($password) < 8 || strlen($password) > 72) {
            $errors[] = self::error('password', 'La contrasena debe tener entre 8 y 72 caracteres.');
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, string>>
     */
    public static function validateLogin(array $payload): array
    {
        $errors = [];

        $email = self::readString($payload, 'email');
        if ($email === '') {
            $errors[] = self::error('email', 'El email es obligatorio.');
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = self::error('email', 'El email no tiene un formato valido.');
        }

        $password = self::readString($payload, 'password');
        if ($password === '') {
            $errors[] = self::error('password', 'La contrasena es obligatoria.');
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function readString(array $payload, string $field): string
    {
        if (!isset($payload[$field]) || !is_string($payload[$field])) {
            return '';
        }

        return trim($payload[$field]);
    }

    /**
     * @return array<string, string>
     */
    private static function error(string $field, string $message): array
    {
        return [
            'field' => $field,
            'message' => $message,
        ];
    }
}
