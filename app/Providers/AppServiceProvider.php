<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
        $this->configureDefaults();

        // Fuerza que toda URL absoluta generada (redirect(), url(), assets,
        // rutas con nombre) use APP_URL como raíz, en vez del host/puerto que
        // el request "percibe" internamente. Sin esto, detrás de mapeos de
        // puerto (Docker) o proxies que no reenvían el host/puerto real, los
        // redirects (p. ej. tras el login) terminan apuntando a un origen
        // distinto al que el navegador realmente usa, rompiendo la sesión.
        if ($appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
        }

        if (config('app.env') === 'production' || str_starts_with(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
