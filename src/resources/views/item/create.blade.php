@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="page-title">商品の出品</h2>
    <form class="auth-form item-form" action="{{ route('item.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="form-group">
            <label class="form-label">商品画像</label>
            <div class="image-upload-area">
                <img id="item-image-preview" src="#" alt="プレビュー" class="image-preview" style="display: none;">
                <label for="item_image" class="btn-select-image-rect">
                    画像を選択する
                </label>
                <input type="file" id="item_image" name="image" class="input-file-hidden" accept="image/jpeg,image/png" onchange="previewImage(this);">
            </div>
            @error('image')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="section-title">商品の詳細</h3>
        <div class="form-group">
            <label class="form-label">カテゴリー</label>
            <div class="category-list">
                @foreach($categories as $category)
                    <input type="checkbox" name="categories[]" id="cat_{{ $category->id }}" value="{{ $category->id }}" class="category-checkbox"
                    {{ is_array(old('categories')) && in_array($category->id, old('categories')) ? 'checked' : '' }}>

                    <label for="cat_{{ $category->id }}" class="category-label">{{ $category->name }}</label>
                @endforeach
            </div>
            @error('categories')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="condition" class="form-label">商品の状態</label>
            <div class="select-wrapper">
                <select name="condition" id="condition" class="form-input form-select">
                    <option value="" disabled selected>選択してください</option>
                    @foreach($conditions as $key => $value)
                        <option value="{{ $key }}" {{ old('condition') == $key ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
                </div>
            @error('condition')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <h3 class="section-title">商品名と説明</h3>
        <div class="form-group">
            <label for="name" class="form-label">商品名</label>
            <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}">
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="brand" class="form-label">ブランド名</label>
            <input type="text" id="brand" name="brand" class="form-input" value="{{ old('brand') }}">
        </div>

        <div class="form-group">
            <label for="description" class="form-label">商品の説明</label>
            <textarea name="description" id="description" rows="5" class="form-input">{{ old('description') }}</textarea>
            @error('description')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="price" class="form-label">販売価格</label>
            <div class="price-input-container">
                <span class="currency-mark">¥</span>
                <input type="number" id="price" name="price" class="form-input price-input-field" value="{{ old('price') }}">
            </div>
            @error('price')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit btn-sell-submit">出品する</button>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var preview = document.getElementById('item-image-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection