# URL

http://localhost:1024/tutorial/todo

# Documents

- [figma-画面遷移図]()
- [figma-テーブル定義書]()
- [figma-変数定義書]()
- [figma-フローチャート]()
- [figma-テストケース]()

# 目標機能

- ユーザ登録 / ログイン
  - 自分のタスクだけ見える
- タスクの CRUD
  - タイトル, 締切, ステータス, 優先度
- 一覧の検索・絞り込み
  - 未完了のみ / 期限切れ
- 完了トグル
  - ワンクリックで完了 / 未完了 を切り替え

# データ構成

```
users
  id, email(unique), password, created, modified

tasks
  id, user_id(FK), title, due_date, priority, is_done, created, modified
```

# 作成手順

## マイグレーション

```
# config/Migrations/ に「テーブル定義のPHPファイル」が生成される（まだDBには反映されない）

bin/cake bake migration CreateUsers email:string password:string created modified
bin/cake bake migration CreateTasks user_id:integer title:string[150] due_date:date priority:integer is_done:boolean created modified

# そのPHPファイルを実行して、DB上に実際のテーブルが作られる
bin/cake migrations migrate
```

## モデル&CRUD を Bake

```
# src/Model/Table/TasksTable.php などのコードが作られ、アプリ側で使えるようになる

bin/cake bake all users
bin/cake bake all tasks
```

## 認証・認可を導入

```
cake5を想定

docker compose exec app composer require cakephp/authentication:^3.0 cakephp/authorization:^3.0
```

### ミドルウェアに追加

```php
$middlewareQueue
  ->add(new \Authentication\Middleware\AuthenticationMiddleware($this))
  ->add(new \Authorization\Middleware\AuthorizationMiddleware($this, [
      'unauthorizedHandler' => [
          'className' => 'Authorization.Redirect',
          'url' => '/users/login',
          'queryParam' => 'redirect',
      ],
  ]));
```

## ルーティング設定

```
/tutorial/todo でアクセスできるように。
```

## Users（ログイン/サインアップ）

## Tasks（自分のタスクだけ）

## ビューの最小改修

## シード（任意）

```
docker compose exec app bin/cake bake seed UsersSeed
docker compose exec app bin/cake bake seed TasksSeed
docker compose exec app bin/cake migrations seed
```
