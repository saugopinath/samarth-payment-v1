<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Otp\OtpGenerator::class, function ($app) {
            return new \App\Services\Otp\OtpGenerator();
        });

        $this->app->singleton(\App\Services\Otp\OtpService::class, function ($app) {
            return new \App\Services\Otp\OtpService($app->make(\App\Services\Otp\OtpGenerator::class));
        });

        $this->app->singleton(\App\Services\Otp\OtpActionRegistry::class, function ($app) {
            return new \App\Services\Otp\OtpActionRegistry();
        });

        // Contextual binding: inject SBI implementation for PushToSBIController
        $this->app->when(\App\Http\Controllers\PushToSBIController::class)
            ->needs(\App\Services\PushProviderInterface::class)
            ->give(function ($app) {
                $sbiServer = \App\Helpers\Helper::getSBISftpServer();
                $enc = \Illuminate\Support\Facades\Storage::get('cert_enc/sbi-public-key.pem');
                $dec = \Illuminate\Support\Facades\Storage::get('cert_enc/jb-private-key.pem');
                return new \App\Services\SbiPushService($sbiServer, $enc, $dec);
            });

        // Contextual binding: inject IFMS implementation for PushToIfmsController
        $this->app->when(\App\Http\Controllers\PushToIfmsController::class)
            ->needs(\App\Services\PushProviderInterface::class)
            ->give(function ($app) {
                return new \App\Services\IfmsPushService();
            });

      
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $registry = $this->app->make(\App\Services\Otp\OtpActionRegistry::class);
        
        $registry->register('reset_password', function (\App\Models\User $user) {
            session(['auth.reset_password_user_id' => encrypt($user->id)]);
            return route('password.reset.otp');
        });

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
