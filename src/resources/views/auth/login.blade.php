@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="page-title">ログイン</h2>

    <form class="auth-form" action="{{ route('login') }}" method="POST" novalidate>
        @csrf

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

        <button type="submit" class="btn-submit">ログインする</button>

        <a href="{{ route('register') }}" class="auth-link">会員登録はこちら</a>
    </form>
</div>
@endsection