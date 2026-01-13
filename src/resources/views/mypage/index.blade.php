@extends('layouts.app')

@section('content')
<div class="mypage-container">
    <div class="user-profile-section">
        <div class="user-avatar">
            @if(isset($user->profile_image) && $user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像">
            @else
                <div class="no-avatar"></div>
            @endif
        </div>
        <div class="user-info">
            <h2 class="user-name">{{ $user->name ?? 'ユーザー名未設定' }}</h2>
        </div>
        <div class="user-action">
            <a href="{{ route('profile.edit') }}" class="btn-edit-profile">プロフィールを編集</a>
        </div>
    </div>

    <div class="mypage-tabs">
        <a href="{{ route('mypage', ['tab' => 'sell']) }}"
        class="tab-item {{ $tab === 'sell' ? 'active' : '' }}">
            出品した商品
        </a>
        <a href="{{ route('mypage', ['tab' => 'buy']) }}"
        class="tab-item {{ $tab === 'buy' ? 'active' : '' }}">
            購入した商品
        </a>
    </div>

    <div class="product-grid">
        @foreach($items as $item)
            <div class="product-card">
                <a href="{{ route('item.detail', ['item_id' => $item->id]) }}">
                    <div class="product-image-wrapper">
                        @if (str_starts_with($item->image_path, 'http'))
                            {{-- httpから始まるならそのまま表示（Seederデータ） --}}
                            <img src="{{ $item->image_path }}" alt="{{ $item->name }}">
                        @else
                            {{-- それ以外なら storage をつけて表示（出品データ） --}}
                            <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                        @endif
                        @if($item->sales_status === 2)
                            <span class="sold-label">Sold</span>
                        @endif
                    </div>
                    <div class="product-name">{{ $item->name }}</div>
                </a>
            </div>
        @endforeach
    </div>

    @if($items->isEmpty())
        <p class="no-items-message">
            {{ $tab === 'buy' ? '購入した商品はありません' : '出品した商品はありません' }}
        </p>
    @endif
</div>
@endsection