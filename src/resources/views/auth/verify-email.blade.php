@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="page-title" style="font-size: 16px; margin-bottom: 30px;">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </h2>

    <div style="margin-bottom: 20px;">
        <a href="http://localhost:8025" target="_blank" class="btn-submit" style="background-color: #ccc; color: #333; font-weight: normal; padding: 10px 30px; text-decoration: none; display: inline-block;">
            認証はこちらから
        </a>
    </div>

    <form method="POST" action="{{ route('verification.send') }}" style="margin-top: 20px;">
        @csrf
        <button type="submit" style="background: none; border: none; color: #007bff; cursor: pointer; text-decoration: none;">
            認証メールを再送する
        </button>
    </form>
</div>

@if (session('status') == 'verification-link-sent')
    <div style="color: green; text-align: center; margin-top: 20px;">
        新しい認証リンクを送信しました。
    </div>
@endif

@endsection