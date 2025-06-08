<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

if (!file_exists(ROOT_PATH . '/.env')) {
    copy(ROOT_PATH . '/.env.example', ROOT_PATH . '/.env');
    echo "Created .env file from .env.example\n";
}

$directories = [
    ROOT_PATH . '/var',
    ROOT_PATH . '/var/log',
    ROOT_PATH . '/var/storage',
    ROOT_PATH . '/var/cache',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            echo "Failed to create directory: {$dir}\n";
            exit(1);
        }
        echo "Created directory: " . basename($dir) . "\n";
    }
}

$envFile = ROOT_PATH . '/.env';
$envContent = file_get_contents($envFile);

if (str_contains($envContent, 'APP_KEY=base64:your-32-character-secret-key-here')) {
    $appKey = 'base64:' . base64_encode(random_bytes(32));
    $envContent = str_replace(
        'APP_KEY=base64:your-32-character-secret-key-here',
        "APP_KEY={$appKey}",
        $envContent
    );
    file_put_contents($envFile, $envContent);
    echo "Generated APP_KEY\n";
}

echo "Configuration completed successfully!\n";
