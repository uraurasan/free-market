@extends('layouts.app')

@section('content')
<div class="auth-page">
    <h2 class="page-title">プロフィール設定</h2>

    <form class="auth-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group profile-image-section">
            <div class="profile-image-wrapper">
                <img id="profile-image-preview"
                    src="{{ isset($user->profile_image) ? asset('storage/' . $user->profile_image) : '#' }}"
                    alt="プロフィール画像"
                    class="profile-image-preview"
                    style="{{ isset($user->profile_image) ? '' : 'display: none;' }}">
            </div>
            <label for="profile_image" class="btn-select-image">
                画像を選択する
            </label>
            <input type="file" id="profile_image" name="profile_image" class="input-file-hidden" accept="image/jpeg,image/png" onchange="previewImage(this);">
            @error('profile_image')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="name" class="form-label">ユーザー名</label>
            <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $user->name) }}">
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="post_code" class="form-label">郵便番号</label>
            <input type="text" id="post_code" name="post_code" class="form-input" value="{{ old('post_code', $user->post_code) }}">
            @error('post_code')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="address" class="form-label">住所</label>
            <input type="text" id="address" name="address" class="form-input" value="{{ old('address', $user->address) }}">
            @error('address')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="building_name" class="form-label">建物名</label>
            <input type="text" id="building_name" name="building_name" class="form-input" value="{{ old('building_name', $user->building_name) }}">
            @error('building_name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit">更新する</button>

    </form>
</div>

<script>
function previewImage(input) {
    // ファイルが選択されているかチェック
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        // ファイル読み込みが完了したら実行される
        reader.onload = function (e) {
            var preview = document.getElementById('profile-image-preview');
            preview.src = e.target.result; // 読み込んだ画像のデータをsrcにセット
            preview.style.display = 'block'; // 画像を表示
        }

        // ファイルを読み込む
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection