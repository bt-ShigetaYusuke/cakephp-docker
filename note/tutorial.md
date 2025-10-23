# チュートリアル

## CakePHP がデータベース操作と CRUD 画面を自動生成してくれるやつ

### URL

http://localhost:1024/articles

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

### ファイル解説

```
src/Model/Table/ArticlesTable.php

# 概要

CakePHPが自動生成した「テーブル（＝モデル）」の設定クラス。
アプリが「articlesテーブルをどう扱うか」をここで定義している。

# ArticlesTableの役割

src/Model/Table/ArticlesTable.php
articles テーブル全体を扱うクラス（検索・保存・削除など）

src/Model/Entity/Article.php
**articles の1行（1件）**を表すクラス

# コード分解

## クラス宣言と継承

class ArticlesTable extends Table
  - Table は CakePHP の ORM (Object Relational Mapper) の基底クラス。
  - この継承により、find(), save(), delete() などのDB操作が使えるようになります。

## initialize()：テーブルの基本設定

public function initialize(array $config): void
{
  parent::initialize($config);

  $this->setTable('articles'); // このモデルが対応するDBテーブル名を明示。
  $this->setDisplayField('title'); // 一覧などでレコードを文字列表示する際に使うカラム。
  $this->setPrimaryKey('id'); // 主キー（プライマリキー）を指定。

  $this->addBehavior('Timestamp'); // 自動で created / modified カラムを更新する機能を追加。
}

## validationDefault()：バリデーション（入力チェック）

public function validationDefault(Validator $validator): Validator
{
  $validator
  ->scalar('title') // 文字列であること
  ->maxLength('title', 255) // 最大255文字
  ->requirePresence('title', 'create') // 新規登録時に必須
  ->notEmptyString('title'); // 空文字NG

  $validator
  ->scalar('body') // 文字列であること
  ->requirePresence('body', 'create') // 新規登録時に必須
  ->notEmptyString('body'); // 空文字NG

  return $validator;
}

title と body は必須入力。
空欄や長すぎる文字は保存されない。
エラー時にはフォーム側でメッセージが出る。

## このファイルが担うこと

DB設定:
articles テーブルと紐づける

タイムスタンプ:
created, modified 自動更新

バリデーション:
title/body が空や長すぎると保存拒否

便利メソッド:
find, save, delete などDB操作が簡単に使えるようになる
```

```
src/Model/Entity/Article.php

CakePHP の 「エンティティ（1行分のデータを表すオブジェクト）」 のクラスです。
つまり、articles テーブルの1件のレコードをオブジェクトとして扱うための定義になります。

ArticlesTable
対象: テーブル全体（集合）
役割: 検索・保存・削除などを行う

Article
対象: 1行（単体）
役割: 各カラムのデータや属性を保持する

## class Article extends Entity

class Article extends Entity

- Entity クラスを継承して、CakePHP の エンティティ機能を使えるようにしています。
- 「レコード1件」をオブジェクトとして扱うための基底クラスです。

## $_accessible：一括代入（mass assignment）の制御

protected array $_accessible = [
  'title' => true,
  'body' => true,
  'created' => true,
  'modified' => true,
];

これはセキュリティに関係する大事な設定です。
「フォームなどでまとめて受け取った値のうち、どのフィールドを上書きしていいか」 を制御します。
```

```

```
