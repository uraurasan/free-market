<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <header class="header">
        <div class="header-inner container">
            <h1 class="header-logo">
                <a href="/">
                    {{--テキストを画像に変更--}}
                    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
                </a>
            </h1>

            @if( !Request::is('register') && !Request::is('login') && !Request::is('email/verify') )
                <div class="header-search">
                    <form action="{{ route('root') }}" method="GET">
                        <input type="text" name="keyword" placeholder="なにをお探しですか？" value="{{ request('keyword') }}">
                    </form>
                </div>

                <nav class="header-nav">
                    <ul>
                        @auth
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit">ログアウト</button>
                                </form>
                            </li>
                            <li><a href="{{ url('/mypage') }}">マイページ</a></li>
                            <li><a href="{{ url('/sell') }}" class="btn-header-sell">出品</a></li>
                        @else
                            <li><a href="{{ route('login') }}">ログイン</a></li>
                            <li><a href="{{ route('register') }}">会員登録</a></li>
                        @endauth
                    </ul>
                </nav>
            @endif
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>

</body>
</html>