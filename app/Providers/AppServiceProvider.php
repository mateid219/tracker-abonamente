<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePasswordRules();

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Minimum password requirements, used by registration, reset and password change.
     * In production the password is also checked against known data breaches
     * (Have I Been Pwned, k-anonymity: only a hash prefix leaves the server).
     */
    protected function configurePasswordRules(): void
    {
        Password::defaults(function () {
            $rule = Password::min(10)->letters()->mixedCase()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
