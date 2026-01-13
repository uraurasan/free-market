@extends('layouts.app')

@section('content')
<div class="container top-page">

    <div class="tab-nav">
        <a href="{{ route('root', ['tab' => 'recommend', 'keyword' => $keyword]) }}"
        class="tab-item {{ $tab !== 'mylist' ? 'active' : '' }}">
            おすすめ
        </a>

        <a href="{{ route('root', ['tab' => 'mylist', 'keyword' => $keyword]) }}"
        class="tab-item {{ $tab === 'mylist' ? 'active' : '' }}">
            マイリスト
        </a>
    </div>

    <div class="item-list">
        @forelse($items as $item)
            <div class="item-card">
                <a href="{{ route('item.detail', ['item_id' => $item->id]) }}" class="item-link">

                    <div class="item-img-wrapper">
                        @if ($item->image_path)
                            @if (str_starts_with($item->image_path, 'http'))
                                <img src="{{ $item->image_path }}" alt="{{ $item->name }}">
                            @else
                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                            @endif
                        @else
                            <img src="{{ asset('images/no-image.png') }}" alt="No Image">
                        @endif

                        {{-- sold-labelの表示 --}}
                        @if($item->sales_status !== 1)
                            <div class="sold-label">SOLD</div>
                        @endif
                    </div>

                    <div class="item-name">{{ $item->name }}</div>
                </a>
            </div>
        @empty
            <p>出品されている商品はありません。</p>
        @endforelse
    </div>
</div>
@endsection