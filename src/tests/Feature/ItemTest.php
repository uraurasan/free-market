<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItemTest extends TestCase
{
    use RefreshDatabase; // 毎回DBリセット

    /**
     * 出品画面が正しく開けるか
     */
    public function test_sell_screen_can_be_rendered()
    {
        // 1. ユーザーとカテゴリーを用意
        $user = User::factory()->create();
        $category = Category::create(['name' => 'ファッション']);

        // 2. ログインして出品画面(/sell)へアクセス
        $response = $this->actingAs($user)->get('/sell');

        // 3. 画面が表示されること(200 OK)
        $response->assertStatus(200);
        // カテゴリー名が表示されているか確認
        $response->assertSee('ファッション');
    }

    /**
     * バリデーションテスト（必須項目が空の場合）
     */
    public function test_sell_validation_error_if_fields_are_missing()
    {
        $user = User::factory()->create();

        // 空っぽのデータを送信
        $response = $this->actingAs($user)->post('/sell', []);

        // エラーが出るはず（ExhibitionRequestで設定したメッセージ）
        $response->assertSessionHasErrors([
            'name' => '商品名を入力してください',
            'description' => '商品説明を入力してください',
            'image' => '商品画像を選択してください',
            'categories' => 'カテゴリーを選択してください',
            'condition' => '商品の状態を選択してください',
            'price' => '販売価格を入力してください',
        ]);
    }

    /**
     * バリデーションテスト（価格が数値じゃない、カテゴリが選ばれてないなど）
     */
    public function test_sell_validation_error_invalid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'price' => '三百円', // 数値じゃない
            // categories 送らない
        ]);

        $response->assertSessionHasErrors([
            'price' => '販売価格は数値で入力してください',
            'categories' => 'カテゴリーを選択してください',
        ]);
    }

    /**
     * ID 15: 出品商品情報登録（正常系）
     * 商品出品画面にて必要な情報が保存できること
     */
    public function test_item_can_be_stored_successfully()
    {
        // 1. 準備
        Storage::fake('public'); // 仮想のストレージを用意（重要！）
        $user = User::factory()->create();
        $category1 = Category::create(['name' => 'ファッション']);
        $category2 = Category::create(['name' => 'メンズ']); // 複数選択テスト用

        // 偽の画像ファイルを作成
        $file = UploadedFile::fake()->image('test_product.jpg');

        // 2. 実行（送信するデータ）
        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'description' => 'これはテストです。',
            'price' => 1000,
            'category_id' => null, // 古いフォーム値（念のため）
            'categories' => [$category1->id, $category2->id], // 複数選択
            'condition' => 1, // 良好
            'image' => $file,
            'brand' => 'テストブランド'
        ]);

        // 3. 検証

        // DBに商品が保存されたか？
        $this->assertDatabaseHas('items', [
            'name' => 'テスト商品',
            'price' => 1000,
            'user_id' => $user->id,
            'brand_name' => 'テストブランド',
            'sales_status' => 1, // 出品中
        ]);

        // 中間テーブル（item_categories）に紐付いているか？
        $item = Item::where('name', 'テスト商品')->first();
        $this->assertTrue($item->categories->contains($category1->id));
        $this->assertTrue($item->categories->contains($category2->id));

        // 画像が保存されたか？（item_imagesディレクトリの中にハッシュ名で保存される）
        // パスは $item->image_path に入ってるはず
        Storage::disk('public')->assertExists($item->image_path);

        // マイページへリダイレクトされたか？
        $response->assertRedirect(route('mypage'));
    }

    /**
     * ID 6: 商品検索機能
     * 「商品名」で部分一致検索ができること
     */
    public function test_search_items_by_keyword()
    {
        $owner = User::factory()->create();

        // データを手動で準備
        // 検索に引っかかるやつ
        Item::create([
            'user_id' => $owner->id,
            'name' => '素晴らしい時計',
            'price' => 1000,
            'description' => '説明文',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test_image_1.jpg', // ▼▼ 追加！
            'brand_name' => 'テストブランド',    // ▼▼ 念のため追加！
        ]);

        // 引っかからないやつ
        Item::create([
            'user_id' => $owner->id,
            'name' => '普通のバッグ',
            'price' => 2000,
            'description' => '説明文',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test_image_2.jpg', // ▼▼ 追加！
            'brand_name' => 'テストブランド',    // ▼▼ 念のため追加！
        ]);

        // 「時計」で検索！
        $response = $this->get('/?keyword=時計');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('素晴らしい時計');
        $response->assertDontSee('普通のバッグ');
    }

    /**
     * ID 6 (応用): 検索状態がマイリストでも保持されている
     */
    public function test_search_keyword_is_retained_in_mylist_tab()
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        // 商品を手動で作成
        $item = Item::create([
            'user_id' => $owner->id,
            'name' => '激レア時計',
            'price' => 50000,
            'description' => 'すごい時計です',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test_image_3.jpg',
            'brand_name' => 'ロレックス',
        ]);
        
        // ▼▼ 修正：favorites テーブルにデータを登録！ ▼▼
        \Illuminate\Support\Facades\DB::table('favorites')->insert([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 検索ワード「時計」を持ってマイリストタブへアクセス
        $response = $this->actingAs($user)->get('/?tab=mylist&keyword=時計');

        // 検証
        $response->assertStatus(200);
        $response->assertSee('激レア時計');
        $response->assertSee('keyword=' . urlencode('時計'));
    }
}