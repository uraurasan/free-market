<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('メールアドレスの認証について')
                ->greeting('ご登録ありがとうございます。')
                ->line('以下のボタンをクリックして、メールアドレスの認証プロセスを完了してください。')
                ->action('メールアドレスを認証する', $url)
                ->line('もしこのアカウントを作成した覚えがない場合は、何もする必要はありません。');
        });
    }
}
