<?php

/**
 * CakePHPが自動生成した CRUD（一覧・詳細・追加・編集・削除）処理をまとめたコントローラ。
 * 
 * Controller (ArticlesController): ルート（URL）に応じて動きを決める。モデルやビューとつなぐ。
 * Model (ArticlesTable, Article): DB操作やビジネスロジックを担当。
 * View (templates/Articles/*.php): HTMLを描画する。
 */

/**
 * $this->Articles:
 * 自動的に ArticlesTable がロードされる。
 * 
 * $this->request:
 * ユーザからのリクエストを表すオブジェクト。
 * 
 * $this->Flash:
 * 1回だけ表示されるメッセージを管理。
 * 
 * $this->set():
 * ビューに変数を渡す
 */

/**
 * # 処理の流れ
 * 
 * 1. ユーザが /articles にアクセス
 * → index() が呼ばれ、記事一覧表示。
 * 
 * 2. 「NEW ARTICLE」ボタンで /articles/add にアクセス
 * → add() が呼ばれ、フォーム送信で新規保存
 * 
 * 3. 「Edit」で /articles/edit/{id}
 * → 該当レコードを読み込み、フォーム送信で更新
 * 
 * 4. 「Delete」で /articles/delete/{id}
 * → 該当レコードを削除
 */

declare(strict_types=1);

namespace App\Controller;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    /**
     * Index method
     *
     * 一覧表示
     * 表示ページ /articles
     * 
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Articles->find(); // → articles テーブルを全件検索するクエリを作成。
        $articles = $this->paginate($query); // → ページネーション付きで取得（1ページ20件）など。

        $this->set(compact('articles')); // → 取得した$articlesをviewに渡す。
        // → templates/Articles/index.php で使われる。
    }

    /**
     * View method
     *
     * 詳細表示
     * 表示ページ /articles/view/1
     * 
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $article = $this->Articles->get($id, contain: []); // → 主キー $id で1件取得（存在しなければ例外）。
        $this->set(compact('article')); // 取得した1件をviewに渡す。
    }

    /**
     * Add method
     * 
     * 新規追加
     * 表示ページ /articles/add
     * テンプレート templates/articles/add.php
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        // 1. 空のEntityを作る（フォームの雛形）
        $article = $this->Articles->newEmptyEntity();

        // 2. フォームが送信されたらデータをマージ
        if ($this->request->is('post')) {

            // 3. ここで validationDefault()が呼ばれる
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // 4. バリデーションOKなら保存
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // 4. ViewにEntitを渡してフォーム生成
        $this->set(compact('article'));
    }

    /**
     * Edit method
     * 
     * 表示ページ /article/edit/1
     * テンプレート: /templates/Article/edit.php
     * 
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        // 1. DBから既存のEntityを取得
        $article = $this->Articles->get($id, contain: []);

        // 2. フォーム送信データをマージ
        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }
        $this->set(compact('article'));
    }

    /**
     * Delete method
     * 
     * 実行ページ: /articles/delete/1
     * フォーム経由でPOSTされる
     * 
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']); // → セキュリティのため、HTTPメソッドをPOST/DELETEに限定。
        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) { // → 結果に応じてメッセージを出しわけ
            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']); // → 一覧へ戻る。
    }
}
