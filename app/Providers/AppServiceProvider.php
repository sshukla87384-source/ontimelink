<?php

namespace App\Providers;

use App\Models\Link;
use App\Models\User;
use App\Policies\LinkPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Link::class, LinkPolicy::class);
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        Password::defaults(fn () => $this->app->environment('production')
            ? Password::min(10)->letters()->numbers()->uncompromised()
            : Password::min(8));

        // Named limiters used by routes. Keys mix user id and IP so shared
        // NATs are not collectively punished, but one abuser still is.
        RateLimiter::for('redeem', fn (Request $r) => Limit::perMinute(20)->by($r->ip()));
        RateLimiter::for('generate', fn (Request $r) => Limit::perMinute(15)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('auth', fn (Request $r) => Limit::perMinute(6)->by(strtolower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('webhooks', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
    }
}
