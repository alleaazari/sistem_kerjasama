<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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
        $this->configureRateLimiters();
    }

    /**
     * Limiter bernama untuk route publik yang rawan spam/DoS.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('pengajuan', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('mitra-login', fn (Request $request) => Limit::perMinute(5)->by(Str::lower((string) $request->input('username')).'|'.$request->ip()));
        RateLimiter::for('pddikti', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('mitra-profile', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Generate draf MoU: batasi per akun mitra + IP agar template tidak
        // dipanggil berulang (spam kiriman ke admin).
        RateLimiter::for('mou-draf', fn (Request $request) => Limit::perMinute(10)
            ->by(Str::lower((string) data_get($request->session()->get('mitra_data'), 'username')).'|'.$request->ip()));

        // Login panel admin: per IP + username, supaya satu akun tidak bisa
        // ditebak berulang dan satu IP tidak menyapu banyak akun sekaligus.
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('username')).'|'.$request->ip()));
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
