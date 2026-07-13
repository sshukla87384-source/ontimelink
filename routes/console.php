<?php

use App\Services\LinkService;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduler (single shared-hosting cron entry)
|--------------------------------------------------------------------------
| Hostinger cron: * * * * * php /home/USER/domains/DOMAIN/artisan schedule:run
*/

Schedule::call(fn () => app(LinkService::class)->markExpired())
    ->name('links:mark-expired')->hourly()->withoutOverlapping();

// Session/cache tables are database-backed; prune stale rows nightly.
Schedule::command('model:prune')->daily();
Schedule::command('auth:clear-resets')->daily();
