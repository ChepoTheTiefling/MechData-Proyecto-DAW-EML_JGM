<?php
declare(strict_types=1);

namespace App\Config;

final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    public static function load(string $filePath): void
    {
        if (!is_file($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
            $key = trim($key);
            $value = self::normalizeValue(trim($value));

            if ($key === '') {
                continue;
            }

            self::$values[$key] = $value;
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return (string) $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    private static function normalizeValue(string $value): string
    {
        if (strlen($value) >= 2) {
            $firstChar = $value[0];
            $lastChar = $value[strlen($value) - 1];

            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                return substr($value, 1, -1);
            }
        }

        return $value;
    }
}
