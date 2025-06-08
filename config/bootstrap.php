<?php

declare(strict_types=1);

define("ROOT_PATH", dirname(__DIR__));

if (!function_exists('directory')) {
    function directory(string $name): string
    {
        $directories = [
            'app' => ROOT_PATH,
            'runtime' => ROOT_PATH . '/var',
            'logs' => ROOT_PATH . '/var/log',
            'storage' => ROOT_PATH . '/var/storage',
            'public' => ROOT_PATH . '/public',
            'cache' => ROOT_PATH . '/var/cache',
        ];

        if (!isset($directories[$name])) {
            throw new InvalidArgumentException("Unknown directory: {$name}");
        }

        $path = $directories[$name];

        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException("Failed to create directory: {$path}");
        }

        return $path;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)', '' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}
