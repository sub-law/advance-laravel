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
