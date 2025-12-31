@extends('layouts.app')

@section('content')
<div class="container item-detail-page">
    <div class="item-detail-inner">
        
        <div class="item-detail-image">
            @if ($item->image_path)
                @if (str_starts_with($item->image_path, 'http'))
                    <img src="{{ $item->image_path }}" alt="{{ $item->name }}">
                @else
                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                @endif
            @else
                <img src="{{ asset('images/no-image.png') }}" alt="No Image">
            @endif
        </div>

        <div class="item-detail-info">
            
            <h2 class="item-name-large">{{ $item->name }}</h2>
            <p class="item-brand-name">{{ $item->brand_name }}</p>

            <div class="item-price-area">
                <span class="price-value">¥{{ number_format($item->price) }}</span>
                <span class="price-tax">(税込)</span>
            </div>

            <div class="item-action-icons">
                <div class="icon-block like-btn" 
                    data-item-id="{{ $item->id }}" 
                    data-auth="{{ Auth::check() ? 'true' : 'false' }}">
                    
                    {{-- ▼▼ ハートアイコン：ログインしていて、いいね済みなら「赤」、それ以外は「グレー」を表示 ▼▼ --}}
                    @if (Auth::user() && Auth::user()->favorites()->where('item_id', $item->id)->exists())
                        <img src="{{ asset('images/icon_like_active.png') }}" class="icon-img heart-icon" alt="いいね">
                    @else
                        <img src="{{ asset('images/icon_like.png') }}" class="icon-img heart-icon" alt="いいね">
                    @endif

                    <span class="icon-count" id="like-count">{{ $likeCount }}</span>
                </div>

                <div class="icon-block">
                    {{-- ▼▼ コメントアイコン：吹き出し画像に変更 ▼▼ --}}
                    <img src="{{ asset('images/icon_comment.png') }}" class="icon-img comment-icon" alt="コメント">
                    <span class="icon-count">{{ $commentCount }}</span>
                </div>
            </div>

            @auth
                <a href="{{ route('item.purchase', ['item_id' => $item->id]) }}" class="btn-purchase">購入手続きへ</a>
            @endauth

            @guest
                <a href="javascript:void(0);" class="btn-purchase" onclick="alert('ログインが必要です')">購入手続きへ</a>
            @endguest

            <h3 class="detail-section-title">商品説明</h3>
            <div class="detail-description-text">
                {!! nl2br(e($item->description)) !!}
            </div>

            <h3 class="detail-section-title">商品の情報</h3>
            <div class="detail-info-row">
                <span class="detail-info-label">カテゴリー</span>
                <div class="detail-category-tags">
                    @foreach($categories as $category)
                        <span class="category-tag">{{ $category->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="detail-info-row">
                <span class="detail-info-label">商品の状態</span>
                <span class="detail-info-value">{{ $conditionName }}</span>
            </div>

            <h3 class="detail-section-title">コメント({{ $commentCount }})</h3>
            <div class="comment-list">
                @foreach($item->comments as $comment)
                    <div class="comment-item">
                        <div class="comment-user-icon">
                            @if(isset($comment->user) && $comment->user->profile_image)
                                @if (str_starts_with($comment->user->profile_image, 'http'))
                                    <img src="{{ $comment->user->profile_image }}" alt="user" class="user-icon-img">
                                @else
                                    <img src="{{ asset('storage/' . $comment->user->profile_image) }}" alt="user" class="user-icon-img">
                                @endif
                            @else
                                <img src="{{ asset('images/user-icon-placeholder.png') }}" alt="user" class="user-icon-img">
                            @endif
                        </div>
                        <div class="comment-body">
                            <div class="comment-user-name">{{ $comment->user->name ?? '退会済みユーザー' }}</div>
                            <div class="comment-content">{{ $comment->comment }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="comment-form-area">
                <h4 class="comment-form-title">商品へのコメント</h4>
                <form action="{{ route('item.comment.store', ['item_id' => $item->id]) }}" method="POST">
                    @csrf
                    <textarea name="comment" class="comment-textarea" rows="4">{{ old('comment') }}</textarea>

                    @error('comment')
                        <p class="form-error" style="color: red; font-size: 14px; margin-bottom: 10px;">
                            {{ $message }}
                        </p>
                    @enderror

                    @auth
                        <button type="submit" class="btn-submit btn-comment-submit">コメントを送信する</button>
                    @endauth

                    @guest
                        <button type="button" class="btn-submit btn-comment-submit" onclick="alert('ログインが必要です')">コメントを送信する</button>
                    @endguest
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const likeBtn = document.querySelector('.like-btn');
        const likeIcon = document.querySelector('.heart-icon'); // imgタグを取得
        const likeCount = document.querySelector('#like-count');

        // ▼▼ JSでの画像切り替え用パス（Bladeのassetヘルパーで展開しておく） ▼▼
        const iconActive = "{{ asset('images/icon_like_active.png') }}";
        const iconDefault = "{{ asset('images/icon_like.png') }}";

        if (likeBtn) {
            likeBtn.addEventListener('click', function () {
                const isItemAuth = this.dataset.auth === 'true';
                if (!isItemAuth) {
                    alert('ログインが必要です');
                    return;
                }
                const itemId = this.dataset.itemId;

                fetch(`/item/${itemId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // ▼▼ 画像の src を書き換える処理に変更！ ▼▼
                        if (data.like_status === 'added') {
                            likeIcon.src = iconActive; // 赤ハートにする
                        } else {
                            likeIcon.src = iconDefault; // グレーに戻す
                        }
                        likeCount.textContent = data.count;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('いいねの処理に失敗しました。');
                });
            });
        }
    });
</script>
@endsection