<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ Rate limit (un singur limiter pentru guest + auth)
        RateLimiter::for('create-ad', function (Request $request) {
            if ($request->user()) {
                // Auth: 5 anunțuri / 5 minute / user
                return Limit::perMinutes(5, 5)->by($request->user()->id);
            }

            // Guest: 1 anunț / 5 minute / IP
            return Limit::perMinutes(5, 1)->by($request->ip());
        });

        // ✅ Customizăm emailul de resetare parolă (codul tău)
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Resetare parolă MeseriasBun.ro')
                ->greeting('Salut!')
                ->line('Ai cerut resetarea parolei pentru contul tău MeseriasBun.ro.')
                ->action('Resetează parola', $url)
                ->line('Dacă nu ai cerut resetarea, poți ignora acest email.');
        });
    }
}
