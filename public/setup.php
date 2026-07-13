<?php
/**
 * One-time browser installer for shared hosting (no SSH required).
 *
 * Usage:  https://yourdomain.com/setup.php?key=SETUP_KEY_BELOW
 *
 * It runs the database migrations, seeds the admin account from the
 * ADMIN_* variables in .env, creates the storage link, and builds the
 * production caches.
 *
 * >>> DELETE THIS FILE IMMEDIATELY AFTER A SUCCESSFUL RUN <<<
 */

const SETUP_KEY = 'CHANGE-THIS-BEFORE-UPLOADING';

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');
set_time_limit(300);

if (!hash_equals(SETUP_KEY, (string) ($_GET['key'] ?? ''))) {
    http_response_code(403);
    exit("Forbidden.\n\nOpen this file in the File Manager, copy the SETUP_KEY value,\nand call:  /setup.php?key=THE_KEY\n");
}

$root = dirname(__DIR__);

if (!file_exists($root.'/.env')) {
    http_response_code(400);
    exit("ERROR: .env not found.\n\nRename .env.hostinger to .env in the File Manager, fill in your\ndatabase + mail + ADMIN_* values, then reload this page.\n");
}

if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    http_response_code(400);
    exit('ERROR: PHP '.PHP_VERSION." detected. Select PHP 8.3 in hPanel -> PHP Configuration.\n");
}

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

function step(string $label, callable $fn): void
{
    echo str_pad($label.' ', 46, '.');
    try {
        $fn();
        echo " OK\n";
    } catch (Throwable $e) {
        echo " FAILED\n\n".$e->getMessage()."\n";
        echo "\nFix the problem above (usually .env values) and reload this page.\n";
        exit;
    }
}

echo "One-Time Link — installer\n";
echo str_repeat('=', 50)."\n";

step('Checking APP_KEY', function () {
    if (blank(config('app.key'))) {
        throw new RuntimeException('APP_KEY is empty in .env.');
    }
});

step('Checking database connection', function () {
    Illuminate\Support\Facades\DB::connection()->getPdo();
});

step('Running migrations', function () {
    Artisan::call('migrate', ['--force' => true]);
});

step('Seeding admin account', function () {
    Artisan::call('db:seed', ['--force' => true]);
    echo trim(preg_replace('/\s+/', ' ', Artisan::output())), ' ';
});

step('Creating storage link', function () {
    if (!file_exists(public_path('storage'))) {
        Artisan::call('storage:link');
    }
});

step('Caching configuration', fn () => Artisan::call('config:cache'));
step('Caching routes', fn () => Artisan::call('route:cache'));
step('Caching views', fn () => Artisan::call('view:cache'));

echo str_repeat('=', 50)."\n";
echo "SUCCESS — installation complete.\n\n";
echo ">>> DELETE public/setup.php NOW (File Manager) <<<\n\n";
echo "Then:\n";
echo "  1. Add the cron job (see HOSTINGER-README.txt, step 6)\n";
echo "  2. Log in at /login with your ADMIN_EMAIL and ADMIN_PASSWORD\n";
