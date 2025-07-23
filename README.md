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
