<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // ------------------------------------------------------------
        // 1. テストユーザー作成 (ログイン用)
        // ------------------------------------------------------------
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'post_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building_name' => 'テックビル101',
            'profile_image' => null, 
        ]);

        // ▼▼ 追加：出品者ユーザー作成 (商品を持つ人) ▼▼
        $seller = User::create([
            'name' => '出品 太郎',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'post_code' => '000-0000',
            'address' => '大阪府大阪市',
            'building_name' => '商売繁盛ビル',
        ]);

        // ------------------------------------------------------------
        // 2. カテゴリー作成
        // ------------------------------------------------------------
        $categoryNames = [
            'ファッション', '家電', 'インテリア', 'レディース', 'メンズ',
            'コスメ', '本','ゲーム', 'スポーツ','キッチン',
            'ハンドメイド', 'アクセサリー','おもちゃ','ベビー・キッズ'
        ];
        
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::create(['name' => $name]);
        }

        // ------------------------------------------------------------
        // 3. 商品データ投入
        // ------------------------------------------------------------
        $itemsData = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'brand_name' => 'Rolax',
                'item_condition' => 1,
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'brand_name' => '西芝',
                'item_condition' => 2,
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'brand_name' => null, 
                'item_condition' => 3,
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'description' => 'クラシックなデザインの革靴',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'brand_name' => null, 
                'item_condition' => 4,
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'description' => '高性能なノートパソコン',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'brand_name' => null, 
                'item_condition' => 1,
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'description' => '高音質のレコーディング用マイク',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'brand_name' => null, 
                'item_condition' => 2,
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'brand_name' => null, 
                'item_condition' => 3,
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'description' => '使いやすいタンブラー',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'brand_name' => null, 
                'item_condition' => 4,
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'description' => '手動のコーヒーミル',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'brand_name' => 'Starbacks',
                'item_condition' => 1,
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'description' => '便利なメイクアップセット',
                'image_path' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'brand_name' => null, 
                'item_condition' => 2,
            ],
        ];

        foreach ($itemsData as $index => $itemData) {
            // ▼▼ 修正：商品は「出品者ユーザー」に紐付ける（じゃないと自分で買えない！） ▼▼
            $item = Item::create([
                'user_id' => $seller->id, 
                'name' => $itemData['name'],
                'price' => $itemData['price'],
                'description' => $itemData['description'],
                'image_path' => $itemData['image_path'],
                'brand_name' => $itemData['brand_name'],
                'item_condition' => $itemData['item_condition'],
                'sales_status' => 1, // 基本は出品中
            ]);

            // ▼▼ 追加：カテゴリーをランダムに1〜3個紐付ける ▼▼
            // （これがないと検索機能や詳細画面のカテゴリ表示が動かない）
            // $categories配列からランダムにキーを取得
            $randomCategories = collect($categories)->random(rand(1, 3));
            foreach ($randomCategories as $category) {
                $item->categories()->attach($category->id);
            }

            // ▼▼ 追加：動作確認用に「玉ねぎ」だけ「売り切れ」にしておく ▼▼
            if ($itemData['name'] === '玉ねぎ3束') {
                $item->update(['sales_status' => 2]); // 2:売り切れ
                // 必要なら purchases テーブルにもデータを入れる必要があるけど、
                // 表示確認だけなら sales_status を変えるだけでOKなはず！
            }
        }
    }
}