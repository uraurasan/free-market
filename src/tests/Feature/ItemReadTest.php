<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class ItemReadTest extends TestCase
{
    use RefreshDatabase;

    //ID 4: 商品一覧表示
    public function test_index_excludes_own_items_and_shows_sold_label()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 1. 自分の出品商品
        Item::create([
            'user_id' => $me->id,
            'name' => '俺の商品',
            'price' => 1000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'my.jpg',
            'brand_name' => 'mybrand',
        ]);

        // 2. 他人の出品商品
        Item::create([
            'user_id' => $other->id,
            'name' => '他人の商品',
            'price' => 2000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'other.jpg',
            'brand_name' => 'otherbrand',
        ]);

        // 3. 他人の売り切れ商品
        Item::create([
            'user_id' => $other->id,
            'name' => '売り切れ商品',
            'price' => 3000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 2, // Sold out
            'image_path' => 'sold.jpg',
            'brand_name' => 'soldbrand',
        ]);

        // ログインしてトップページへ
        $response = $this->actingAs($me)->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('俺の商品'); // 自分のは見えない
        $response->assertSee('他人の商品');   // 他人のは見える
        $response->assertSee('売り切れ商品'); // 売り切れ
        $response->assertSee('SOLD');        // 「Sold」の文字がある
    }

    //ID 5: マイリスト一覧
    public function test_mylist_shows_liked_items_only()
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 商品作成
        $likedItem = Item::create([
            'user_id' => $other->id,
            'name' => '好きな商品',
            'price' => 1000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'liked.jpg',
            'brand_name' => 'brand',
        ]);

        $notLikedItem = Item::create([
            'user_id' => $other->id,
            'name' => '興味ない商品',
            'price' => 1000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'ignore.jpg',
            'brand_name' => 'brand',
        ]);

        //テーブルに登録 (favoritesテーブル)
        DB::table('favorites')->insert([
            'user_id' => $me->id,
            'item_id' => $likedItem->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // マイリストタブへアクセス
        $response = $this->actingAs($me)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('好きな商品');
        $response->assertDontSee('興味ない商品');
    }

    //ID 7: 商品詳細表示
    public function test_detail_shows_all_information()
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        // カテゴリ作成
        $cat1 = Category::create(['name' => '洋服']);
        $cat2 = Category::create(['name' => 'メンズ']);

        $item = Item::create([
            'user_id' => $owner->id,
            'name' => '詳細テスト商品',
            'price' => 12345,
            'description' => '詳しい説明文です',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'detail.jpg',
            'brand_name' => 'ハイブランド',
        ]);

        // カテゴリ紐付け
        $item->categories()->attach([$cat1->id, $cat2->id]);

        // 詳細ページへ
        $response = $this->actingAs($user)->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('詳細テスト商品');
        $response->assertSee('12,345'); // 金額フォーマット(カンマ)チェック
        $response->assertSee('詳しい説明文です');
        $response->assertSee('ハイブランド');
        $response->assertSee('洋服'); // カテゴリ1
        $response->assertSee('メンズ'); // カテゴリ2
    }

    //ID 8: いいね機能
    public function test_like_toggle()
    {
        $user = User::factory()->create();
        $item = Item::create([
            'user_id' => User::factory()->create()->id,
            'name' => '商品',
            'price' => 1000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'img.jpg',
            'brand_name' => 'brand',
        ]);

        // 1. いいね登録
        $response = $this->actingAs($user)->post("/item/{$item->id}/like");

        // DBに増えているか確認
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 2. いいね解除 (もう一度同じURLを叩く)
        $response = $this->actingAs($user)->post("/item/{$item->id}/like");

        // DBから消えているか確認
        $this->assertSoftDeleted('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    //ID 9: コメント送信機能
    public function test_comment_can_be_posted()
    {
        $user = User::factory()->create();
        $item = Item::create([
            'user_id' => User::factory()->create()->id,
            'name' => '商品',
            'price' => 1000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'img.jpg',
            'brand_name' => 'brand',
        ]);

        // 1. 投稿
        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment' => 'これはテストコメントです',
        ]);

        // 画面リロード等のリダイレクト
        $response->assertStatus(302);

        // DBに保存されたか
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => 'これはテストコメントです',
        ]);
    }


    //ID 9: コメントバリデーション
    public function test_comment_validation()
    {
        $user = User::factory()->create();
        $item = Item::create([
            'user_id' => User::factory()->create()->id,
            'name' => '商品',
            'price' => 1000,
            'description' => 'desc',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'img.jpg',
            'brand_name' => 'brand',
        ]);

        // 空送信
        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment' => '',
        ]);
        $response->assertSessionHasErrors('comment');

        // 256文字以上送信 (255文字制限)
        $longComment = str_repeat('a', 256);
        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment' => $longComment,
        ]);
        $response->assertSessionHasErrors('comment');
    }
}