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

## 1-16データの更新
A[ユーザーが編集画面へアクセス] --> B[AuthorController@edit で対象データ取得]
B --> C[edit.blade.php に表示]
C --> D[ユーザーがフォームで編集＆送信]
D --> E[AuthorController@update が受け取る]
E --> F[リクエストデータを整形・更新処理]
F --> G[DB に保存され、トップ画面へリダイレクト]

📌 各ステップのポイント
画面表示（GET）

/edit?id=◯◯ にアクセス

AuthorController@edit が該当 ID のデータを取得

edit.blade.php に $form として表示

フォーム送信（POST）

入力された値が Request オブジェクトに格納される

AuthorController@update で $request->id をもとに Author::find()->update() を実行

更新後、redirect('/') で一覧ページへ

🧭 Laravel教材STEP：削除処理の基本実装（CRUD "Delete"）
🚀 処理概要フロー
/delete?id={id} にアクセス → 該当Authorのデータ取得

delete.blade.php にて削除対象データの表示＆確認

ユーザーが「送信」ボタンをクリック → POST リクエスト送信

Controller側で削除処理 → TOPページにリダイレクト

🧩 使用ファイルと役割
ファイル名	役割・機能
AuthorController.php	削除画面表示（delete()）＋削除処理（remove()）
delete.blade.php	対象Authorデータの表示＋削除フォーム
web.php	/delete の GET/POST ルート定義

🧠 Controller詳細
php
// 削除画面の表示（GET）
public function delete(Request $request)
{
    $author = Author::find($request->id);
    return view('delete', ['author' => $author]);
}

// 実際の削除処理（POST）
public function remove(Request $request)
{
    Author::find($request->id)->delete();
    return redirect('/');
}
✅ delete() は対象データの表示 ✅ remove() はデータの削除とリダイレクト処理 🚫 delete($request->id) のような直接削除はNG（deleteはインスタンスメソッド）

🖥️ Bladeテンプレート（delete.blade.php）
blade
<form action="/delete?id={{ $author->id }}" method="POST">
    @csrf
    <button>送信</button>
</form>
UIで削除対象情報を確認

明示的に「送信」操作で削除を許可

🔍 理解のポイント
find($request->id)：ID指定でモデル取得

->delete()：取得したインスタンスに対して削除命令（引数なし）

##STEP-18-find-function
##name属性を利用した検索処理の流れ
flowchart TD
    A[ユーザーが /find に GETアクセス] --> B[AuthorController@find がフォーム画面を表示]
    B --> C[検索語を入力してフォーム送信（POST /find）]
    C --> D[AuthorController@search が検索処理を実行]
    D --> E[Author モデルの name カラムを LIKE検索]
    E --> F[一致する最初の1件を取得 → $item に格納]
    F --> G[検索語 $input と結果 $item をビュー find.blade.php へ渡す]
    G --> H[Bladeで @isset($item) を用いて結果をテーブル表示]

##モデル結合ルートの処理概要
flowchart TD
    A[GET /author/{id}] --> B[ルーティングで Author $author をバインド]
    B --> C[AuthorController@bind($author) が実行]
    C --> D[該当Authorデータをビューへ渡す]
    D --> E[binds.blade.php で表示処理]

##💡 技術的ポイント
要素	意図・設計のポイント
Route::get('/author/{author}', ...)	{author} の部分を自動で Author モデルにバインド（暗黙的モデル結合）
public function bind(Author $author)	URLのIDに対応するレコードが $author として自動で取得される
$item = $author → Bladeへ渡す	$item->name などでデータを表示可能に
@extends, @section('title') など	レイアウトを継承しつつ、ページタイトルや装飾を追加

##モデルにメソッドを追加
🔧 処理内容の概要
要素	内容
追加メソッド	モデル Author に getDetail() を定義
目的	Authorの属性をまとめて整形し、画面表示用に活用
ビュー側	{{$author->getDetail()}} によって1行で表示処理完了
効果	Bladeの記述量削減、可読性向上、ロジックの集約により再利用性アップ

📘 getDetail() メソッドの設計意図
php
public function getDetail()
{
    $txt = 'ID:' . $this->id . ' ' . $this->name . '(' . $this->age .  '才' . ') ' . $this->nationality;
    return $txt;
}
ID・名前・年齢・国籍 を1行に整形し、テキストとして返却

将来的に Author モデルの表示方法を変更したいときは、このメソッドだけ修正すればOK

ビューやコントローラーがロジックを持たず、単なる表示テンプレートとして整理される

##2-19デバッグ
🧐 解説：dd($authors) の動作と意味
dd() は Dump and Die の略： → 中身をダンプ（出力）し、そこで処理を終了します。

$authors = Author::all(); で取得した 全著者データのCollection をブラウザで表示します。

return view() の処理は実行されないため、ビュー画面には遷移せず、取得結果だけが表示されます。

✅ このddの有効活用ポイント
シーン	内容
データの取得チェック	モデルから期待通りのデータが来ているか確認
リレーションが効いているか	$author->books などの結合確認にも便利
nullや空Collectionの確認	意図した検索結果になっているかを即座に判断
配列/オブジェクト構造の把握	Bladeでのループ処理や条件分岐の設計の参考に

##🛠 Tinkerで試したことの意義
試した構文	解説
Author::all()	モデル全件取得 → Collectionとして返される構造を目視確認
Author::find(1)	単体取得 → モデルインスタンスが返る（個別レコードの構造確認）
エラーなく Author モデルが読み込まれていること（エイリアスも効いてる）

id, name, age, nationality に加えて timestampも含む構造が確認できる

nationalityの表記ゆれ（American / american / 日本）などもこの時点で把握可能

# 2-20 バリデーション（FormRequest活用）

## 🎯 目的
- 入力値の妥当性チェックを通じたUX向上
- バリデーション責務のControllerからの分離
- エラーメッセージの日本語化・表示整形

---

## 🛤 処理概要（新規追加 `/add` の流れ）

1. ブラウザフォームで name / age / nationality を入力・送信（POST /add）
2. `AuthorController::create()` 実行 → `AuthorRequest` で自動バリデーション
3. バリデーション
   - 成功 → `Author::create()` で保存 → `/` にリダイレクト
   - 失敗 → `/verror` に遷移 → `verror.blade.php` にエラー表示

---

## 🧩 技術ポイント

### `AuthorRequest.php`
- `rules()` によるバリデーションルール定義
- `messages()` による日本語エラーメッセージ
- `getRedirectUrl()` でエラー時遷移先を `/verror` に指定

### `AuthorController.php`
- `create()`/`update()` 内で `AuthorRequest` を型指定 → 自動バリデーション実行
- 成功時：DB保存＋リダイレクト  
  失敗時：`verror.blade.php` 表示

### Bladeファイル（`add.blade.php` / `edit.blade.php`）
- `@error('フィールド名')` で個別エラーメッセージ表示
- `count($errors)` によるエラーフラグ判定
- inputフォームと送信ボタンの基本構成

---

## 👀 UX設計・教材化観点

| 項目 | 内容 |
|------|------|
| 責務分離 | Controllerからバリデーション処理を分離 |
| エラー可視化 | `@error` による明示的なフィードバック |
| 多言語対応 | `messages()` によるメッセージのローカライズ |
| 画面遷移設計 | `getRedirectUrl()` で失敗時遷移制御 |

---
##2-5で修正した箇所
###add.blade.php 39行目
#### ❌ 誤り
`<input type="id">` → HTMLに存在しない型

#### ✅ 修正
`<input type="text" name="author_id">` → テキスト入力として正しく動作

#### 💡 学び
- HTMLの `type` 属性は仕様に沿って定義する必要がある
- 存在しない型を指定すると、ブラウザが予期せぬ挙動をする可能性がある

### ネストされたテーブルの背景色継承問題と解決策

#### 問題
- 外側テーブルの行背景色（白／グレー）が、内側テーブルの `<td>` に反映されず、常にグレーになっていた

#### 原因
- CSSで `td table tbody tr td` に `background-color: #EEEEEE !important;` が指定されていたため、外側のスタイルが上書きされていた

#### 解決策
- `background-color: inherit !important;` に変更し、親の背景色を継承させることで、意図通りの表示に修正(教材の進行上、最終的には元に戻した)

#### 学び
- `inherit` は親要素のスタイルを引き継ぐ便利な指定
- `!important` を使う場合は、継承や優先順位に注意
- ネスト構造では、親子関係のスタイル伝播を意識した設計が重要

1. モデルにリレーションメソッドを定義
   - Author: hasMany(Book::class)
   - Book: belongsTo(Author::class)

2. マイグレーションで外部キーを設定
   - booksテーブルに author_id を追加

3. コントローラーでリレーション付き取得
   - Author::with('books')->get()

4. ビューで展開
   - @foreach ($author->books as $book)

5. ルートで表示ページを設定
   - Route::get('/relation', [AuthorController::class, 'relate'])

TEP2-6：Eager LoadingによるN+1問題の回避
❓ なぜこのSTEPが必要か
Eloquentでリレーションを扱う際、$books->author のようにアクセスすると、各BookごとにAuthorを個別に取得するため、N+1問題が発生します。 これは、BookがN件あると、Author取得のためにN件の追加クエリが発生するという非効率な状態です。

#＃アクセス回数を確認するために行ったこと
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
##追記　use Illuminate\Support\Facades\DB; 
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
##追記      DB::enableQueryLog(); 
        $items = Book::with('author')->get();
##追記        dd(DB::getQueryLog());
        return view('book.index', ['items' => $items]);
    }
}

# STEP3-3：セッションについて学ぼう

## 🎯 目的
- ユーザー入力をセッションに保存し、ビューで表示する
- セッションドライバを `database` に設定し、永続化を確認
- セッションテーブルの構造と保存形式を理解する

---

## 🔁 処理の流れ

1. `/session` にアクセス（GET）
   - `SessionController@getSes` が呼ばれる
   - `$request->session()->get('txt')` でセッション値を取得
   - `session.blade.php` に渡して表示

2. フォームに値を入力 → POST送信
   - `SessionController@postSes` が呼ばれる
   - `$request->input('txt')` で入力値を取得
   - `$request->session()->put('txt', $txt)` でセッションに保存
   - `redirect('/session')` で再表示

3. セッションデータは `sessions` テーブルに保存される
   - `payload` カラムにシリアライズ＋Base64形式で格納
   - `user_agent` や `ip_address` も記録される

---

## 🧠 覚えておきたいメソッド・コード

### ✅ セッション取得

```php
$request->session()->get('キー名');

## STEP3-4：ページネーションの導入（simplePaginate）

- `AuthorController@index` にて `simplePaginate(4)` を使用
- `index.blade.php` にて `{{ $authors->links() }}` でページリンク表示
- `simplePaginate()` は「前へ・次へ」のみ表示される簡易版
- 今後 `paginate()` との違いや、検索との併用時の挙動も検証予定
## ページネーションの理解と検証ログ

### 1. `simplePaginate` メソッド
- **概要**: Laravelのページネーションメソッドの一つ。前後のページリンクのみを表示。
- **用途**: シンプルなUIを求める場面で有効。ページ数の表示が不要な場合に適している。
- **例**:
  ```php
  $posts = Post::simplePaginate(10);
svg.w-5.h-5 {
  /* paginateメソッドの矢印の大きさ調整のために追加 */
  width: 30px;
  height: 30px;
  fill: #289ADC; /* 任意の色に変更可能 */
  transition: fill 0.3s ease;
}

svg.w-5.h-5:hover {
  fill: #FF5722; /* ホバー時の色変更 */
}
