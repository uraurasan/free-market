@extends('layouts.app')

@section('content')
<div class="address-change-page">
    <h2 class="address-change-title">住所の変更</h2>

    <form action="{{ route('purchase.address.update', ['item_id' => $item->id]) }}" method="POST" class="address-form">
        @csrf

        <div class="form-group">
            <label for="post_code" class="form-label">郵便番号</label>
            <input type="text" name="post_code" id="post_code" class="form-input" 
                   value="{{ old('post_code', $addressData['post_code']) }}">
            @error('post_code')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="address" class="form-label">住所</label>
            <input type="text" name="address" id="address" class="form-input" 
                   value="{{ old('address', $addressData['address']) }}">
            @error('address')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="building_name" class="form-label">建物名</label>
            <input type="text" name="building_name" id="building_name" class="form-input" 
                   value="{{ old('building_name', $addressData['building_name']) }}">
            @error('building_name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-update">更新する</button>
    </form>
</div>
@endsection