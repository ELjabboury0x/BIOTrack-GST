<?php

use Illuminate\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// This project serves assets from workspace-level public/.
$app->bind('path.public', function () {
    return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'public';
});

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Ensure the container has the 'files' binding early so packages
// that expect the filesystem are available during bootstrap.
$app->singleton('files', function () {
    return new Illuminate\Filesystem\Filesystem();
});

return $app;
