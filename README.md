# coachtech フリマアプリ

## 環境構築

### Docer ビルド

※自身の環境（ユーザー ID）と docker コンテナのユーザー ID（PHP や mysql）が異なることによる権限問題が発生していたため、docker-compose.yml ファイルの記述を一部変更し、PHP コンテナ内の実行ユーザーの ID がホスト PC の ID と一致するように変更を加えています。

念の為、下記方法での Docer ビルドをお願いいたします。

1.`git clone git@github.com:uraurasan/free-market.git`

2.`export UID=$(id -u)`

3.`export GID=$(id -g)`

4.`docker-compose up -d --build`

### Laravel 環境構築

1.`docker-compose exec php bash`

2.`composer install`

3.「.env.example」ファイルを 「.env」ファイルに命名を変更。または、新しく.env ファイルを作成

4..env に以下の環境変数を追加

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5.メール認証機能動作確認（Mailhog）のため、.env に以下のメールアドレスを追加してください

`MAIL_FROM_ADDRESS=no-reply@coachtech-frima.com`

この設定により、送信されたメールは http://localhost:8025 で確認できます。

6.アプリケーションキーの作成

`php artisan key:generate`

7.マイグレーションの実行

`php artisan migrate`

8.シーディングの実行

`php artisan db:seed`

## 開発環境

・商品一覧画面：http://localhost/

・会員登録画面：http://localhost/register

・ログイン画面：http://localhost/login

・商品出品画面：http://localhost/sell

・プロフィール画面：http://localhost/mypage

・phpMyAdmin：http://localhost:8080/

## 使用技術（実行環境）

・PHP:8.1.33

・Laravel:8.83.8

・mysql:8.0.26

・nginx:1.21.1

## ER 図

![ER図](images/er-table.svg)
