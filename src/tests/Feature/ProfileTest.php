<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 13: ユーザー情報取得
     */
    public function test_mypage_displays_user_info_and_items()
    {
        // ▼▼ 修正：img_url -> profile_image に変更！ ▼▼
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'profile_image' => 'profile.jpg', 
        ]);

        // 1. 出品した商品を作る
        $soldItem = Item::create([
            'user_id' => $user->id,
            'name' => '自分が出品した商品',
            'price' => 1000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test1.jpg',
            'brand_name' => 'brand'
        ]);

        // 2. 購入した商品を作る
        $otherUser = User::factory()->create();
        $boughtItem = Item::create([
            'user_id' => $otherUser->id,
            'name' => '自分が購入した商品',
            'price' => 2000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 2,
            'image_path' => 'test2.jpg',
            'brand_name' => 'brand'
        ]);
        
        // 購入履歴データ作成
        // (favoritesではなくpurchasesテーブルに相当する処理が必要ならここに追加)
        // ※Purchaseモデルを使って履歴を作る
        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $boughtItem->id,
            'payment_method' => 1,
            'payment_status' => 1,
        ]);

        // 3. マイページへアクセス
        $response = $this->actingAs($user)->get('/mypage');

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
        $response->assertSee('profile.jpg'); // 画像ファイル名が含まれているか

        // タブ切り替えで表示されるはずの商品名があるか
        $response->assertSee('自分が出品した商品');
        
        // ?tab=buy にアクセスして購入履歴もチェック
        $responseBuy = $this->actingAs($user)->get('/mypage?tab=buy');
        $responseBuy->assertStatus(200);
        $responseBuy->assertSee('自分が購入した商品');
    }

    /**
     * ID 14: ユーザー情報変更（初期値表示）
     */
    public function test_profile_edit_screen_shows_initial_values()
    {
        $user = User::factory()->create([
            'name' => '初期ネーム',
            'post_code' => '123-4567',
            'address' => '初期住所',
            'building_name' => '初期ビル',
        ]);

        $response = $this->actingAs($user)->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('value="初期ネーム"', false);
        $response->assertSee('value="123-4567"', false);
    }

    /**
     * ID 14: ユーザー情報変更（更新処理）
     */
    public function test_profile_can_be_updated()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('new_profile.jpg');

        // ▼▼ 修正：img_url -> profile_image に変更！ ▼▼
        $response = $this->actingAs($user)->post('/mypage/profile', [
            'name' => '変更後ネーム',
            'post_code' => '999-9999',
            'address' => '変更後住所',
            'building_name' => '変更後ビル',
            'profile_image' => $file, // フォームのname属性も profile_image のはず！
        ]);

        // マイページへリダイレクト
        $response->assertRedirect('/mypage');

        // DB更新チェック
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '変更後ネーム',
            'post_code' => '999-9999',
        ]);
        
        // ▼▼ 修正：DBのカラム名も profile_image に変更！ ▼▼
        $updatedUser = User::find($user->id);
        $this->assertNotNull($updatedUser->profile_image);
    }
}