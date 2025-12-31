@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="page-title">会員登録</h2>

    <form class="auth-form" action="{{ route('register') }}" method="POST" novalidate>
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">ユーザー名</label>
            <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}">
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}">
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" id="password" name="password" class="form-input">
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">確認用パスワード</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
        </div>

        <button type="submit" class="btn-submit">登録する</button>

        <a href="{{ route('login') }}" class="auth-link">ログインはこちら</a>
    </form>
</div>
@endsection