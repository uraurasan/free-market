@extends('layouts.app')

@section('content')
<div class="container purchase-page">
    <form action="{{ route('purchase.checkout', $item->id) }}" method="POST" id="purchase-form">
        @csrf
        <input type="hidden" name="post_code" value="{{ $addressData['post_code'] }}">
        <input type="hidden" name="address" value="{{ $addressData['address'] }}">
        <input type="hidden" name="building_name" value="{{ $addressData['building_name'] }}">
        <div class="purchase-inner">
            
            <div class="purchase-left">
                <div class="purchase-item-info">
                    <div class="purchase-item-img">
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
                    <div class="purchase-item-text">
                        <h2 class="purchase-item-name">{{ $item->name }}</h2>
                        <p class="purchase-item-price">¥{{ number_format($item->price) }}</p>
                    </div>
                </div>

                <div class="purchase-section">
                    <h3 class="section-title">支払い方法</h3>
                    @error('payment_method')
                        <p style="color:red; margin-bottom:10px;">{{ $message }}</p>
                    @enderror
                    <select name="payment_method" id="payment-select" class="form-select">
                        <option value="" hidden>選択してください</option>
                        <option value="1" {{ old('payment_method') == 1 ? 'selected' : '' }}>コンビニ払い</option>
                        <option value="2" {{ old('payment_method') == 2 ? 'selected' : '' }}>カード支払い</option>
                    </select>
                </div>
                <div class="purchase-section">
                    <div class="section-header">
                        <h3 class="section-title">配送先</h3>
                        <a href="{{ route('purchase.address', ['item_id' => $item->id]) }}" class="change-link">変更する</a>
                    </div>
                    
                    <div class="address-info">
                        <p>〒 {{ $addressData['post_code'] }}</p>
                        <p>{{ $addressData['address'] }} {{ $addressData['building_name'] }}</p>
                    </div>
                </div>
            </div>

            <div class="purchase-right">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>商品代金</span>
                        <span>¥{{ number_format($item->price) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>支払い方法</span>
                        <span id="summary-payment-method">選択してください</span>
                    </div>
                </div>
                <button type="submit" class="btn-purchase-submit">購入する</button>
            </div>

        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentSelect = document.getElementById('payment-select');
        const summaryPayment = document.getElementById('summary-payment-method');

        paymentSelect.addEventListener('change', function() {
            // 選択されたオプションのテキスト（コンビニ払い etc）を取得
            const selectedText = this.options[this.selectedIndex].text;
            summaryPayment.textContent = selectedText;
        });
    });
</script>
@endsection