# コンテナ

```
docker compose ps

cakephp-docker-app-1       cakephp-docker-app       "docker-php-entrypoi…"   app                 5 days ago          Up 3 hours          9000/tcp
cakephp-docker-db-1        mysql:8.0                "docker-entrypoint.s…"   db                  5 days ago          Up 3 hours          33060/tcp, 0.0.0.0:2024->3306/tcp
cakephp-docker-mailhog-1   mailhog/mailhog:v1.0.1   "MailHog"                mailhog             5 days ago          Up 3 hours          1025/tcp, 0.0.0.0:3034->8025/tcp
cakephp-docker-web-1       nginx:1.27-alpine        "/docker-entrypoint.…"   web                 5 days ago          Up 3 hours          0.0.0.0:1024->80/tcp
```

# メモ帳アプリ

1. マイグレーションで notes テーブルを作成

```
# コンテナに入る（cakephp-docker-app-1）
docker compose exec app sh

# composerがあるディレクトリに移動する
cd appsrc

# コンテナ内で以下を実行
bin/cake bake migration CreateNotes title:string[120] content:text created modified
bin/cake migrations migrate
```

2. CRUD を自動生成（Bake）

```
bin/cake bake all Notes
```

- これで Model / Controller / Templates が一式を作成

3. 動作確認

```
bin/cake server
```

4. ルーティング設定

```
/tutorial/memo にしたい。

構成:
/tutorial/memo        → NotesController::index()
/tutorial/memo/add    → NotesController::add()
/tutorial/memo/edit/1 → NotesController::edit(1)
```

```php
// ▼ ここを追加！ /tutorial スコープ ▼
$builder->scope('/tutorial', function (\Cake\Routing\RouteBuilder $builder) {
    $builder->setRouteClass(DashedRoute::class);

    // /tutorial/memo を Notes コントローラへ
    $builder->connect('/memo', ['controller' => 'Notes', 'action' => 'index']);
    $builder->connect('/memo/:action/*', ['controller' => 'Notes'])
        ->setPass(['id']);  // /edit/1 などのIDを受け取れるように
});
```

5. バリデーション設定

- src/Model/Table/NotesTable.php の validationDefault() を調整する。

```php
// src/Model/Table/NotesTable.php

$validator
    ->scalar('title')
    ->maxLength('title', 120)
    ->requirePresence('title', 'create')
    ->notEmptyString('title', 'タイトルは必須です');

$validator
    ->scalar('content')
    ->requirePresence('content', 'create')
    ->notEmptyString('content', '内容は必須です');
```

6. 検索機能を一覧に追加

```php
// src/Controller/NotesController.php

public function index()
{
    $q = $this->request->getQuery('q');
    $query = $this->Notes->find()->order(['Notes.created' => 'DESC']);

    if (!empty($q)) {
        $query->where(['Notes.title LIKE' => "%{$q}%"]);
    }

    $notes = $this->paginate($query);
    $this->set(compact('notes', 'q'));
}
```

```php
// templates/Notes/index.php

<?= $this->Form->create(null, ['type' => 'get']) ?>
<div style="display:flex; gap:.5rem; align-items:center;">
    <?= $this->Form->control('q', ['label' => false, 'value' => $q ?? '', 'placeholder' => 'タイトルで検索']) ?>
    <?= $this->Form->button('検索') ?>
    <?= $this->Html->link('新規メモ', ['action' => 'add'], ['class' => 'button']) ?>
</div>
<?= $this->Form->end() ?>
<hr>
```

# ORM ってなに？

## 概要

ORM（Object Relational Mapper）

「データベースのテーブルと PHP のオブジェクトを対応させて扱う仕組み」のこと。

つまり

SQL 文を書かなくても、オブジェクト操作するだけで DB とやり取りができる。

```
1.

普通のやり方:

sql
SELECT * FROM notes WHERE id = 1;

CakePHPのやり方:

php
$note = $this->Notes->get(1);
```

```
2.

普通のやり方:

sql
INSERT INTO notes (title, content) VALUE ('A', 'B');

CakePHPのやり方:

php
$note = $this->Notes->newEmptyEntity();>
$note->title = 'A';
$note->content = 'B';
$this->Notes->save($note);
```

CakePHP が裏で SQL を組み立ててくれるから、
開発者は「PHP のオブジェクト」をいじるだけで OK らしい。

## ORM の仕組み

- クラス: Table クラス
- 役割: テーブル全体を表す
- 例: NotesTable -> notes テーブルの設定、検索、保存の処理

- クラス: Entity クラス
- 役割: テーブル内の 1 行（レコード）を表す
- 例: Note → id=1 の 1 件のメモデータ

```
例えば、

1. 1件取得したい場合

$note = $this->Notes->get(1);

2. 1件追加したい場合

$note = $this->Notes->newEmptyEntity();
$note->title = 'CakePHPメモ';
$note->content = 'ORMすごい便利！';
$this->Notes->save($note);

するとCakePHPが自動的に、

INSERT INTO notes (title, content, created, modified) VALUES (...);

という SQL を発行してくれる。

3. 更新

$note = $this->Notes->get(1);
$note->title = 'タイトル更新';
$this->Notes->save($note);

裏で自動的に

UPDATE notes SET title ='タイトル更新' WHERE id = 1;

4. 削除

$note = $this->Notes->get(1);
$this->Notes->delete($note);

DELETE FROM notes WHERE id=1;

が裏で実行される
```

## 特徴

- SQL を書かなくて OK
- バリデーション関連も自動処理
- セキュリティ対策込み
- Query オブジェクトで柔軟な検索も可能

# コードを読む順番

1. ルート定義
   - 見るファイル: `config/routes.php`
   - 目的: URL がどの Controller に対応しているか確認
2. コントローラ
   - 見るファイル: `src/Controller/NotesController.php`
   - 目的: どんな処理（アクション）があるか把握
3. モデル（Table）クラス
   - 見るファイル: `src/Model/Table/NotesTable.php`
   - 目的: DB とのつながり・バリデーションを理解
4. エンティティ
   - 見るファイル: `src/Model/Entity/Note.php`
   - 目的: 1 レコード（Notes）がどんな属性を持つか見る
5. ビュー（テンプレート）
   - 見るファイル: `templates/Notes/*.php`
   - 目的: 実際の画面に何を表示しているか確認

# DB の見方

- CLI
- GUI

## CLI（コマンド）操作する方法

```
docker compose exec db sh
mysql -uroot -p
root

or

docker compose exec db mysql -u root -p
root
```

## GUI アプリで見る方法

| container             | service | 役割      | port                       |
| --------------------- | ------- | --------- | -------------------------- |
| `cakephp-docker-db-1` | db      | MySQL 8.0 | **3306/tcp → ホスト 2024** |

| 設定項目              | 入力内容                                           |
| --------------------- | -------------------------------------------------- |
| **Connection Name**   | `CakePHP Docker DB`（任意）                        |
| **Connection Method** | Standard (TCP/IP)                                  |
| **Hostname**          | `127.0.0.1`                                        |
| **Port**              | `2024`                                             |
| **Username**          | `root`（または `docker-compose.yml` の設定値）     |
| **Password**          | 🔒 「Store in Vault...」ボタンで登録（例: `root`） |

```
Workbenchに接続したい

まずは安定に接続失敗

Failed to Connect to MySQL at 127.0.0.1:2024 with user root
SSL connection error: unknown error number
```

```
接続ミスってみるみたいだから調整してみる

app_local.phpを変更

docker再起動
docker compose down
docker compose up -d --build

動作確認
docker compose exec db mysql -u app -psecret -e "SELECT 1;"
1が返ればok

CakePHPから接続テスト
docker compose exec app php -r "new PDO('mysql:host=db;port=3306;dbname=app;charset=utf8mb4','app','secret'); echo 'ok\n';"
ok が出ればCakeからも繋がる。

Workbench側
Connection Method: Standard (TCP/IP)
Hostname: 127.0.0.1（または localhost）
Port: 2024（composeで 2024:3306 と公開しているため）
Username: app
Password: secret
Test Connection → OK なら保存
```

```
Workbench側で以下のエラー。

We are sorry for the inconvenience but an unexpected exception has been raised by one of the MySQL Workbench modules. In order to fix this issue we would kindly ask you to file a bug report. You can do that by pressing the [Report Bug] button below.
Please make sure to include a detailed description of your actions that lead to this problem.
Thanks a lot for taking the time to help us improve MySQL Workbench!
The MySQL Workbench Team

Workbenchじゃないサービス使ってみるか。
```

```
TablePlus 使ってみる

IP: 127.0.0.1
Port: 2024
User: root
Pass: root
Database: cake_dev
SSL: DISABLE

で接続できたわ。とりまこれで。
```

# CLI でテーブル操作できるようにもしておきたい

## テーブル操作の種類

大きく分けて 4 種類

- CRUD
  - Create
  - Read
  - Update
  - Delete

| 種類       | 意味     | SQL のキーワード    | 目的                       |
| ---------- | -------- | ------------------- | -------------------------- |
| **C**reate | 作成     | `CREATE` / `INSERT` | テーブルやデータを作る     |
| **R**ead   | 読み取り | `SELECT`            | データを取得する           |
| **U**pdate | 更新     | `UPDATE` / `ALTER`  | データや構造を変更する     |
| **D**elete | 削除     | `DELETE` / `DROP`   | データやテーブルを削除する |

## sql 文を学ぶ

```
# sqlに入る

docker compose exec db mysql -u root -p
root
```

### Create

```
CREATE

INSERT

# 単一行を追加
INSERT INTO {テーブル名} ({カラム名1}, {カラム名2})
values('値1', '値2');

# 複数行を追加
INSERT INTO {テーブル名} ({カラム名1}, {カラム名2}) VALUES
('値1', '値2'),
('値1', '値2'),
('値1', '値2');
```

### Read

```
# データベース一覧を取得
SHOW DATABASES;

# 対象データベースを選択
USE {db名};

# テーブル一覧を取得
SHOW TABLES;

# テーブルの構造を確認
DESCRIBE {テーブル名}; or
DESC {テーブル名};

# テーブル定義（CREATE文）をそのまま見る
SHOW CREATE TABLE {テーブル名}\G;

# テーブルの中身をざっくり確認
SELECT * FROM {テーブル名} LIMIT 10;

# 行数を取得
SELECT COUNT(*) FROM {テーブル名};
```

### Update

### Delete
