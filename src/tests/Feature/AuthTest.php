<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    //ID 1: 会員登録機能のテスト
    public function test_register_validation_error_if_name_is_missing()
    {
        // 名前を空にして送信
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // RegisterRequestで設定したメッセージと完全一致するかチェック
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);
    }

    public function test_register_validation_error_if_email_is_missing()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    public function test_register_validation_error_if_password_is_missing()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    public function test_register_validation_error_if_password_is_too_short()
    {
        // 7文字で送信
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
    }

    public function test_register_validation_error_if_password_mismatch()
    {
        // 不一致のパスワード
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password999',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);
    }

    public function test_register_success_and_redirects_to_verify_email()
    {
        // メール通知
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // DBに保存されたか
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);

        // メール認証画面(/email/verify)へリダイレクトされたか
        $response->assertRedirect('/email/verify');

        // 認証メールが通知されたか確認
        $user = User::where('email', 'newuser@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //ID 2: ログイン機能のテスト
    public function test_login_validation_error_if_email_is_missing()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // LoginRequestで設定したメッセージ
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    public function test_login_validation_error_if_password_is_missing()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        // ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 間違ったパスワードでログイン試行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        //lang/ja/auth.php'failed'チェック
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません。'
        ]);
    }

    public function test_login_success()
    {
        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'loginuser@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        // 認証後はホームへ
        $response->assertStatus(302);
    }


    //ID 3: ログアウト機能

    public function test_logout_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user); // ログイン状態にする

        $response = $this->post('/logout');

        $this->assertGuest(); // ゲストに戻ったか
        $response->assertRedirect('/'); // トップへリダイレクト
    }


    //ID 16 (応用): メール認証の流れ
    public function test_email_verification_process()
    {
        Notification::fake();

        // 1. 未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 2. ログインしてメール認証画面へ飛ばされるか確認
        $response = $this->actingAs($user)->get('/mypage/profile');
        $response->assertRedirect('/email/verify');

        // 3. 認証リンクの生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 4. 認証リンクを踏む
        $response = $this->actingAs($user)->get($verificationUrl);

        // 5. 認証完了してプロフィール設定へリダイレクト
        $response->assertRedirect('/mypage/profile');

        // 6. DB上で認証済みになっているか確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}