# チュートリアル

## CakePHP がデータベース操作と CRUD 画面を自動生成してくれるやつ

### URL

http://localhost:1024/articles

### 変更内容

```
# 前提

- docker compose ps で app（PHP）と db（MySQL）が起動中
- bin/cake が見える（= CakePHPが入っている）
- .env または config/app_local.php に DB設定済み
  DATABASE_URL="mysql://root:root@db:3306/cake_dev?charset=utf8mb4"
```

```
# DB作成
docker compose exec db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS cake_dev;"

MySQL の中に cake_dev という空のデータベースを作りました。
この中に今後 articles テーブルなどが入ります。

# マイグレーションファイル生成
bin/cake bake migration CreateArticles title:string body:text created modified

CakePHP が「テーブル構造定義ファイル（マイグレーション）」を作成しました。

- 生成先: config/Migrations/20251022045420_CreateArticles.php
- 中身:
  articles テーブルを作り、
  title, body, created, modified の4カラムを持つようにする定義が入っています。

つまり、「こういう構造のテーブルを作る指示書」を作った感じです

# マイグレーション実行
bin/cake migrations migrate

今作った「指示書（マイグレーション）」をもとに、
CakePHP が MySQL に実際の articles テーブルを作りました。
結果として、

mysql> SHOW TABLES;
+-----------------+
| Tables_in_cake_dev |
+-----------------+
| articles        |
| phinxlog        | ← 管理用
+-----------------+

となってます。

# CRUD一式生成
bin/cake bake all articles

「articles」テーブルに対応するコードを自動で全部生成しました。

できたもの:

- src/Model/Table/ArticlesTable.php
- src/Model/Entity/Article.php
- src/Controller/ArticlesController.php
- templates/Articles/*.php

つまり Cake が
👉 DB操作 と 画面操作（CRUD） のコードを全部勝手に作ってくれたわけです。

# まとめ

Docker:
PHP・MySQL コンテナが動作中

DB:
`cake_dev` 内に `articles` テーブルがある

CakePHP:
`ArticlesController` + CRUD画面が自動生成されて動作中

ブラウザ:
`/articles` で一覧、`/articles/add` で追加できる
```
