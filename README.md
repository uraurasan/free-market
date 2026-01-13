# coachtech フリマアプリ

## 環境構築

### Docker ビルド
※ご自身の環境（ユーザーID）とDockerコンテナ内のユーザーID（PHPやMySQL）が異なることによる権限問題を回避するため、`docker-compose.yml`の設定を調整しています。
お手数ですが、以下の手順でビルドをお願いいたします。

念の為、下記方法での Docker ビルドをお願いいたします。

1.`git clone git@github.com:uraurasan/free-market.git`

2.`export UID=$(id -u)`

3.`export GID=$(id -g)`

4.`docker-compose up -d --build`

### Laravel 環境構築

1.`docker-compose exec php bash`

2.`composer install`

3.以下の内容を .env に追記・修正してください。

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

### メール認証機能動作確認（Mailhog）

以下の内容を.env に追記・修正してください。※送信されたメールは http://localhost:8025 で確認できます。

```bash
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
```

### Stripe設定

.envに、ご自身のStripeテスト環境のAPIキーを設定してください。

```bash
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx
```

4.アプリケーションキーの作成

```bash
php artisan key:generate
```

5.シンボリックリンクの作成（画像表示用）

```bash
php artisan storage:link
```

6.マイグレーションの実行

```bash
php artisan migrate
```

7.シーディングの実行（テストデータの投入）

```bash
php artisan db:seed
```

## テストの実行

本アプリケーションは、PHPUnitを使用した単体テスト・機能テストを実装しています。
テスト環境には `SQLite (:memory:)` を使用しているため、事前のDB作成等は不要です。

以下のコマンドでテストを実行できます。

```bash
php artisan test
```

## 動作確認用情報

### 開発環境URL

・商品一覧画面：http://localhost/

・会員登録画面：http://localhost/register

・ログイン画面：http://localhost/login

・商品出品画面：http://localhost/sell

・プロフィール画面：http://localhost/mypage

・phpMyAdmin：http://localhost:8080/

・MailHog: http://localhost:8025/

### テスト用アカウント (Seeding実行後)

動作確認に以下のユーザーをご利用いただけます。

・メールアドレス: `test@example.com`

・パスワード: `password123`

## 使用技術（実行環境）

・PHP:8.1.33

・Laravel:8.83.8

・mysql:8.0.26

・nginx:1.21.1

## ER 図

![ER図](images/table.svg)

## テーブル仕様書

### 1.usersテーブル（会員情報）

| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| ユーザー名 | `name` | varchar |  |  | ○ | |  ||  |
| メールアドレス | `email` | varchar |  | ○ | ○ | |  ||  |
| メール確認 | `email_verified_at` | timestamp |  |  |  | |  ||  |
| パスワード | `password` | varchar |  |  | ○ | |  ||  |
| プロフィール画像 | `profile_image` | varchar |  |  |  | |  ||  |
| 郵便番号 | `post_code` | varchar |  |  |  | |  ||  |
| 住所 | `address` | varchar |  |  |  | |  ||  |
| 建物名 | `building_name` | varchar |  |  |  | |  ||  |
| ログイン保持 | `remember_token` | varchar(100) |  |  |  | |  ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 2.itemsテーブル（出品アイテム）

| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| ユーザーID | `user_id` | unsigned bigint |  |  | ○ | users(id) |  ||  |
| 商品名 | `name` | varchar |  |  | ○ | |  ||  |
| 商品説明 | `description` | text |  |  | ○ | |  ||  |
| 価格 | `price` | integer |  |  | ○ | | 負の値は不可 ||  |
| ブランド名 | `brand_name` | varchar |  |  |  | |  ||  |
| 商品の状態 | `item_condition` | integer |  |  | ○ | | 1=良好, 2=目立った傷や汚れなし, 3=やや傷や汚れあり, 4=状態が悪い ||  |
| 商品画像の保存パス | `image_path` | varchar |  |  | ○ | |  ||  |
| 商品ステータス | `sales_status` | integer |  |  | ○ | | 1=出品中, 2=売却済, 3=決済待ち(デフォルトは1) ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 3.categoriesテーブル（アイテム種別）

| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| 商品種別 | `name` | varchar |  |  | ○ | |  ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 4.item_categoriesテーブル（中間テーブル）
| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| 商品ID | `item_id` | unsigned bigint |  |  | ○ | items(id) |  ||  |
| 商品種別ID | `category_id` | unsigned bigint |  |  | ○ | categories(id) |  ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 5.favoritesテーブル（商品評価）
| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| ユーザーID | `user_id` | unsigned bigint |  |  | ○ | users(id) |  ||  |
| 商品ID | `item_id` | unsigned bigint |  |  | ○ | items(id) |  ||  |
| 論理削除 | `deleted_at` | timestamp |  |  |  | | いいね解除時に日時が入る ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 6.commentsテーブル（商品コメント）
| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| ユーザーID | `user_id` | unsigned bigint |  |  | ○ | users(id) |  ||  |
| 商品ID | `item_id` | unsigned bigint |  |  | ○ | items(id) |  ||  |
| コメント内容 | `comment` | varchar |  |  | ○ | |  ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 7.purchasesテーブル（支払い情報）
| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| ユーザーID | `user_id` | unsigned bigint |  |  | ○ | users(id) |  ||  |
| 商品ID | `item_id` | unsigned bigint |  |  | ○ | items(id) |  ||  |
| 支払方法 | `payment_method` | integer |  |  | ○ | | 1=コンビニ払い,2=カード支払い ||  |
| 決済ステータス | `payment_status` | integer |  |  | ○ | | 1=決済完了,2=決済待ち ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |

### 8.shipping_destinationsテーブル（配送先住所）
| 論理名 | カラム名 | 型 | PRIMARY KEY | UNIQUE KEY | NOT NULL | FOREIGN KEY | 備考 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| ID | `id` | unsigned bigint | ○ |  | ○ | |  ||  |
| 支払いID | `purchase_id` | unsigned bigint |  |  | ○ | purchases(id) |  ||  |
| 郵便番号 | `post_code` | varchar |  |  | ○ | |  ||  |
| 住所 | `address` | varchar |  |  | ○ | |  ||  |
| 建物名 | `building_name` | varchar |  |  |  | |  ||  |
| 登録日時 | `created_at` | timestamp |  |  |  | |  ||  |
| 更新日時 | `updated_at` | timestamp |  |  |  | |  ||  |
