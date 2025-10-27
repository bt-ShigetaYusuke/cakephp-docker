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
