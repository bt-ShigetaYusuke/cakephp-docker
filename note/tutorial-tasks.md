# URL

http://localhost:1024/users
http://localhost:1024/tasks

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
****
tasks
  id, user_id(FK), title, due_date, priority, is_done, created, modified
```

# 作成手順

1. マイグレーション
2. モデル&CRUD を Bake
3. 認証・認可を導入
4. ミドルウェアに追加
5. ルーティング設定
6. Users ログイン・ログアウト・登録機能追加
7. Tasks の認証設定
8. ビューの最小改修

# sql

```
SHOW CREATE TABLE :table名
```

# セッション

# 画面遷移図

# テーブル定義書

# 変数定義書

# フローチャート

# テストケース
