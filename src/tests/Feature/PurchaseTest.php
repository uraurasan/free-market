<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\ShippingDestination;
use Illuminate\Support\Facades\Session;
use Stripe\Checkout\Session as StripeSession;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 10: 商品購入画面表示 (正常系)
     */
    public function test_purchase_page_can_be_rendered()
    {
        // 1. 出品者と購入者を用意
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        // 2. 商品を作成 (出品中)
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => 'テスト商品',
            'price' => 1000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test.jpg',
            'brand_name' => 'テストブランド',
        ]);

        // 3. 購入者がアクセス
        $response = $this->actingAs($buyer)->get("/purchase/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト商品');
    }

    /**
     * ID 10: 自分の商品は購入できない (異常系)
     */
    public function test_seller_cannot_purchase_own_item()
    {
        $seller = User::factory()->create();
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => '自分の商品',
            'price' => 1000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test.jpg',
            'brand_name' => 'テストブランド',
        ]);

        // 出品者自身がアクセス -> 403 Forbidden
        $response = $this->actingAs($seller)->get("/purchase/{$item->id}");
        $response->assertStatus(403);
    }

    /**
     * ID 10: 売り切れの商品は購入できない (異常系)
     */
    public function test_cannot_purchase_sold_out_item()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => '売り切れ商品',
            'price' => 1000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 2, // 売り切れ
            'image_path' => 'test.jpg',
            'brand_name' => 'テストブランド',
        ]);

        $response = $this->actingAs($buyer)->get("/purchase/{$item->id}");
        $response->assertStatus(403);
    }

    /**
     * ID 11: 決済処理 (Checkout)
     * Stripeへのリダイレクトを確認したいが、外部APIはモック(模倣)する必要がある
     * ここでは「セッションへの保存」と「リダイレクト」だけ簡易チェックする
     */
    public function test_checkout_redirects_to_stripe()
    {
        // StripeのAPIキー設定がないとエラーになる場合があるため、テスト用のダミーをセット
        config(['services.stripe.secret' => 'sk_test_dummy']);

        // StripeのSession::createをモック化 (実際にStripeに通信しないようにする)
        $this->mock('alias:Stripe\Checkout\Session', function ($mock) {
            $mock->shouldReceive('create')->andReturn((object)['url' => 'https://checkout.stripe.com/test']);
        });

        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => '購入商品',
            'price' => 1000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 1,
            'image_path' => 'test.jpg',
            'brand_name' => 'テストブランド',
        ]);

        $postData = [
            'payment_method' => 1, // コンビニ払い
            'post_code' => '123-4567',
            'address' => '東京都テスト区',
            'building_name' => 'テストビル',
        ];

        $response = $this->actingAs($buyer)->post("/purchase/checkout/{$item->id}", $postData);

        // セッションにデータが保存されたか
        $response->assertSessionHas('purchase_data');
        
        // StripeのURLへリダイレクトされたか (モックで指定したURL)
        $response->assertRedirect('https://checkout.stripe.com/test');
    }

    /**
     * ID 11: 決済成功後の処理 (Success)
     * DB更新処理をテスト
     */
    public function test_purchase_success_updates_database()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::create([
            'user_id' => $seller->id,
            'name' => '購入商品',
            'price' => 1000,
            'description' => '説明',
            'item_condition' => 1,
            'sales_status' => 1, // まだ出品中
            'image_path' => 'test.jpg',
            'brand_name' => 'テストブランド',
        ]);

        // セッションに購入データを仕込む (Checkoutを通ったことにする)
        $purchaseData = [
            'item_id' => $item->id,
            'payment_method' => 2, // カード払い
            'post_code' => '111-2222',
            'address' => '大阪府テスト市',
            'building_name' => 'マンション',
        ];
        
        // セッションをセットして success ルートへアクセス
        $response = $this->actingAs($buyer)
            ->withSession(['purchase_data' => $purchaseData])
            ->get("/purchase/success/{$item->id}");

        // 1. リダイレクト確認
        $response->assertRedirect(route('root'));

        // 2. DB確認: itemsテーブル (売り切れになったか)
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'sales_status' => 2, // Sold out
        ]);

        // 3. DB確認: purchasesテーブル
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => 2,
        ]);

        // 4. DB確認: shipping_destinationsテーブル
        // ※ IDが不明なので、最新のレコードを取得して確認
        $purchase = Purchase::where('item_id', $item->id)->first();
        $this->assertDatabaseHas('shipping_destinations', [
            'purchase_id' => $purchase->id,
            'post_code' => '111-2222',
            'address' => '大阪府テスト市',
        ]);
    }
}