# advance-laravel
# advance-laravel

Docker環境でLaravel 8を構築し、nginx・PHP・MySQL・phpMyAdminを連携。教材STEPに沿った環境構築・動作確認・Git管理までの記録です。

---

## 🧱 使用技術

- PHP 8.2.29
- Laravel 8.*
- Docker Compose（nginx / php-fpm / mysql / phpMyAdmin）
- Composer 2.8.10
- WSL（Ubuntu）+ VS Code

---

## 🔧 環境構築手順

1. **Laravelインストール**
   ```bash
   composer create-project "laravel/laravel=8.*" . --prefer-dist

## 🚨 主なトラブルと対応ログ（advance-laravel教材STEP）

### 🐳 1. Dockerイメージのビルド失敗（apt updateが404エラー）

- **現象**：`docker-compose up -d --build` 実行時、Debian Busterリポジトリに接続できずエラー
- **原因**：使用イメージ `php:7.4.9-fpm` がDebian Busterベース → リポジトリ廃止（404）
- **対応**：
  - `Dockerfile` のベースを `php:8.2-fpm` に変更
  - 再ビルドで解決：
    ```bash
    docker-compose down
    docker-compose up -d --build
    ```

---

### 📄 2. Laravelログ書き込みエラー

- **現象**：`laravel.log` に書き込みできず `"Permission denied"` エラー発生
- **原因**：`storage/` ディレクトリの権限不足
- **対応**：
  ```bash
  chmod -R 775 storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
## 🗂️ トラブル対応：src/ ディレクトリへの書き込み権限がない

### 🐞 現象
- VS Code や `curl` コマンドで `.gitignore` や設定ファイルを `src/` に保存しようとした際に `"Permission denied"` エラーが出る。

### 🧠 原因
- Laravelを Docker コンテナ内で操作した際、`src/` ディレクトリの所有者が `www-data`（コンテナのWebサーバー）になった。
- WSL側のユーザー（shiny）がそのディレクトリに書き込めなくなった。

### 🛠 対応手順

1. 所有者の変更（WSLユーザーに戻す）：
   ```bash
   sudo chown -R shiny:shiny ~/coachtech/laravel/advance-laravel/src

## Docker構成整理メモ

### 📂 ディレクトリ構造と目的

- `./docker/php/Dockerfile`：PHP環境定義（Composerセットアップ含む）
- `docker-compose.yml`：Laravel開発環境（nginx + php + mysql）構成の統括
- `./src`：Laravelアプリケーション本体（nginx・phpともにここを `/var/www/` にマウント）

### ✅ docker-compose.ymlの重要設定

```yaml
php:
  build: ./docker/php
  volumes:
    - ./src:/var/www/

## MySQLコンテナへの接続

- コンテナ名: `advance-laravel-mysql-1`
- bashログイン: `docker exec -it advance-laravel-mysql-1 bash`
- MySQL接続: `mysql -u root -p`（パスワード: root）
注意点: 
  - 入力待ちの状態でコマンドを2回重ねないよう注意（`->`が出たら`\c`でキャンセル可能）
### トラブル対応

- `No such container` → `docker ps` でコンテナ名の確認必須
- `Access denied` → ユーザー名・パスワード設定の再確認

## STEP02: マイグレーション編（テーブル作成準備）

- 実行環境: DockerのPHPコンテナ内（advance-laravel-php-1）
- 作成コマンド:
  ```bash
  php artisan make:migration create_authors_table

## トラブル対応：マイグレーションファイルの書き込み権限エラー

- 現象: `create_authors_table.php` の編集時に VSCode で「permission denied」
- 原因: ファイル所有者が `root:root`（他は `shiny:shiny`）
- 対処: 以下のコマンドで修正
  ```bash
  sudo chown -R shiny:shiny ~/coachtech/laravel/advance-laravel/src

## STEP02: マイグレーション編（実行結果）

- 実行環境: DockerのPHPコンテナ（advance-laravel-php-1）
- コマンド: `php artisan migrate`
- 実行結果: 以下のテーブルが作成された
  - users
  - password_resets
  - failed_jobs
  - personal_access_tokens
  - authors

- authorsテーブルについて:
  - カラム: id, name, age, nationality, created_at, updated_at
  - 設計意図: 基本的な著者情報を管理する構成

- 注意点:
  - `Deprecated: mbstring.internal_encoding` 警告 → Laravel動作には影響なし

## STEP02: データを作成しよう - マイグレーション編

### 学習内容

- マイグレーションファイルの作成：
  - `php artisan make:migration create_authors_table`
- authorsテーブルの設計：
  - `name`, `age`, `nationality`, `created_at`, `updated_at`
- 権限エラー対応：
  - `sudo chown shiny:shiny create_authors_table.php` で書き込み許可修正
- マイグレーション実行：
  - `php artisan migrate`
  - 教材仕様に合わせて `php artisan migrate:fresh` を実施
- MySQL内でテーブルの存在確認

### 補足

- `.env` に正しいDB接続設定を反映済み（`laravel_user` / `laravel_db`）
- `migrate:fresh` は教材用の初期化目的で使用 → 本番環境では使用禁止
- `timestamps()` は使用せず `useCurrent()->nullable()` で明示制御
- VSCode + Docker環境におけるファイル権限の注意点も記録済み

### Git操作

- 作業ブランチ：`feature/step-02-migration`
- `develop` に統合後 PR作成予定

### PHPコンテナ内に移動するコマンド
docker exec -it advance-laravel-php-1 bash

## STEP03 環境構築トラブル - Seeder作成時の権限エラー対応

### 🐞 発生事象
`AuthorsTableSeeder.php` 作成時に以下のエラーが発生：
EACCES: permission denied, open '...AuthorsTableSeeder.php'

コンテナ内でファイルを生成したため、ホストユーザーが編集・保存できず。

### 🔍 原因
- Dockerコンテナ（`www-data`など）で作成されたファイル → ホストからアクセス拒否
- `docker-compose.yml` に `user:` 指定がないため、UID/GIDの不一致が発生
- ホストユーザーは `UID=1000` / `GID=1000`（Linux環境）

### ✅ 対処方法

#### 1. `docker-compose.yml` でユーザーを指定
```yaml
php:
  build: ./docker/php
  user: "${UID}:${GID}"
  volumes:
    - ./src:/var/www/
2. .env に UID/GID を定義（Linux環境）
env
UID=1000
GID=1000

3. ホスト側で所有権を修正（既存ファイル用）
bash
sudo chown $USER:$USER /home/shiny/coachtech/laravel/advance-laravel/src/databas

## STEP03: Seeder作成・実行手順

### ✅ 作成手順

1. `database/seeders/AuthorsTableSeeder.php` を作成  
   → `Author` モデルを使用してデータ4件を挿入
2. `DatabaseSeeder.php` に登録  
   ```php
   public function run(): void
   {
       $this->call(AuthorsTableSeeder::class);
   }

# STEP: Docker × Laravel 環境構築 - UID/GIDによるユーザー制御

## 🎯 ゴール
Laravel開発環境をホストユーザー（例: shiny）で安全に動作させ、Permission denied問題を防止する。

---

## 1️⃣ `.env` の役割分離

| ファイルパス | 用途 | 内容例 | 注意点 |
|--------------|------|--------|--------|
| `.env`（Docker用） | docker-compose.yml の `${UID}` `${GID}` 展開に使用 | `UID=1000`<br>`GID=1000` | 必ず拡張子なしで配置 |
| `src/.env`（Laravel用） | Laravelアプリの設定値を管理 | APP_KEY / DB接続 / Driverなど | UID/GIDは不要→混乱の元になるため削除推奨 |

---

## 2️⃣ Docker 起動ステップ

```bash
# `.env` の確認と配置
ls -l .env           # advance-laravel/.env に存在するか
mv env.compose .env  # 拡張子付きはNG → 拡張子なしへ修正

# 起動＆ユーザー確認
docker compose up -d
docker compose exec php id
# → uid=1000 gid=1000 なら成功！


## STEP-13：Laravelモデルとテーブルの紐づけ確認方法

### 🧠 自動紐づけ規則
- モデル名：単数形（例：Author）
- テーブル名：複数形（例：authors）
- Eloquentが慣習により自動的に紐づけ

### 🧪 動的に確認する方法（tinker使用）
```bash
XDG_CONFIG_HOME=/var/www/.config php artisan tinker
>>> App\Models\Author::query()->getModel()->getTable();
=> "authors"
なっていればOK

## Laravel×Docker：tinker起動成功と環境構成

LaravelのtinkerがDocker環境下で起動失敗する場合、以下の対策で成功：

### 対策内容

- docker-compose.ymlにて `XDG_CONFIG_HOME=/var/www/.config` をphpコンテナに指定
- `/var/www/.config/psysh` のディレクトリ権限をUID/GIDで調整（mkdir + chown）

### 起動成功時の表示例

```bash
php artisan tinker
Psy Shell v0.12.9 (PHP 8.2.29 — cli) by Justin Hileman
>
## STEP-13：Migration & Seeder結果確認

### artisanログで確認するポイント
- `Dropped all tables successfully.` → テーブル削除完了
- `Migrated:` が各テーブルで表示 → Migration正常処理
- `Seeding:` → 登録Seeder実行開始
- `Database seeding completed successfully.` → ダミーデータ投入完了

### tinker確認推奨
```bash
php artisan tinker
>>> App\Models\Author::count();  // 件数確認
>>> App\Models\Author::pluck('name'); // 内容確認
## STEP-13：最終確認とdevelopマージ

### 確認手順
- Laravel環境に入る：`docker compose exec php bash`
- 依存インストール：`composer install`
- DB初期化＆Seeder：`php artisan migrate:fresh --seed`
- ダミーデータ確認：
```bash
php artisan tinker
>>> App\Models\Author::count(); // 件数確認

## 🎯 学習目的

- Laravel MVCの基本連携（Model → Controller → View）を理解する
- DBから取得したデータをViewで表示するまでの流れを実装

---

## ⚙️ 実装構成

| 種別       | ファイル              | 主な処理                                             |
|------------|-----------------------|------------------------------------------------------|
| Route      | `web.php`             | `'/'` ルートから `AuthorController@index()` を呼び出し |
| Controller | `AuthorController.php`| `Author::all()` によるデータ取得 → `view()` で渡す     |
| Model      | `Author.php`          | `authors` テーブルと連携（Eloquent）                  |
| View       | `index.blade.php`     | `$authors` を `@foreach` で表示（テーブル形式）       |
| Layout     | `default.blade.php`   | `@extends` による共通レイアウト提供                   |

---

## 📌 技術ポイント

- **Eloquent**：`Author::all()` で全件取得
- **Blade構文**：`@extends`, `@section`, `@foreach`
- **レイアウト分離**：共通レイアウトに個別ビューをはめ込む構成
- **Controller継承**：`extends Controller` により Laravel の機能を活用可能


# 📚 Author管理アプリ（Create〜一覧表示）

## 🧭 情報処理の流れ

1. ユーザーが `/add` にアクセス
2. `add.blade.php` の入力フォームを表示
3. ユーザーが「name / age / nationality」を入力して送信（POST `/add`）
4. `AuthorController@create()` が呼び出され、フォームデータを取得
5. モデル `Author::create()` 経由でDBに登録
6. 登録後、`redirect('/')` により一覧画面に遷移
7. `AuthorController@index()` で登録済みデータを取得
8. `index.blade.php` にて一覧表示

---

## 🗂 ファイル構成と役割

| ファイル | 役割 | 詳細 |
|---------|------|------|
| `web.php` | ルート定義 | `/add` に GET（表示）と POST（保存）、 `/` に GET（一覧）を割り当て |
| `AuthorController.php` | コントローラー | `add()`でフォーム表示、`create()`で保存、`index()`で一覧取得 |
| `Author.php` | モデル | `$fillable` により安全なデータ登録が可能。EloquentでDB操作を抽象化 |
| `add.blade.php` | 入力フォーム画面 | ユーザー入力フォームを提供（`@csrf` によるセキュリティ対策） |
| `index.blade.php` | 一覧表示画面 | 登録された Author データを表として表示（※表示テンプレートは別途作成） |

---

## 🛠 技術補足ポイント

- POST処理には `@csrf` を使用 → CSRF対策済み
- モデルに `protected $fillable = [...]` を指定 → Mass assignment の安全管理
- 登録後の `redirect('/')` により、一覧画面へ遷移 → UX向上

---

## 🚀 今後の展開候補

- `create()` にバリデーション追加（`$request->validate()`）
- 一覧画面に「編集・削除」リンク追加 → CRUD化へ発展
- Copilot Pages で教材STEPを整理 → ノウハウ資産化

