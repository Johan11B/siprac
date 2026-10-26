<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Restablece tu contraseña de SIPRAC')
                ->greeting('Hola, '.$notifiable->name)
                ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en SIPRAC.')
                ->action('Restablecer contraseña', $url)
                ->line('Este enlace caduca en '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.')
                ->line('Si no pediste este cambio, puedes ignorar este correo.');
        });
    }
}
