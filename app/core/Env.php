<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;

class Env
{
    private static bool $loaded = false;
    private static array $values = [];

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        $dir = is_file($path) ? dirname($path) : $path;

        if (! file_exists($dir.'/.env')) {
            return;
        }

        $dotenv = Dotenv::createImmutable($dir);
        $dotenv->load();

        self::$values = $_ENV;
        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$values[$key] ?? $default;
    }

    public static function required(string $key): string
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            throw new \RuntimeException("Missing required env variable: {$key}");
        }

        return (string) $value;
    }
}
