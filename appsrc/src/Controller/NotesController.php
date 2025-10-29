<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Notes Controller
 *
 * @property \App\Model\Table\NotesTable $Notes
 */
class NotesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /**
         * 1. notesテーブルのデータを取得
         * 
         * クエリオブジェクトを作成
         * 
         * クエリ...
         * データベースに対する問い合わせ のこと
         * なんのデータを取りたいか を伝える命令文
         * 今回の場合は、
         * SELECT * FROM notes; 的なことをしてる
         * 
         * オブジェクト...
         * データと処理をひとまとめにしたもの
         * 
         * クエリオブジェクト...
         * SQLのようなデータベースクエリをオブジェクトとして扱えるようにしたもの
         * 
         * $queryは...
         * - どのデータベースを対象にするか
         * - どんな条件で検索するか
         * - どう並び替えるか
         * などの情報をすべてプロパティとして持っていて、
         * それをメソッドで操作できるようにしている。
         * 
         */
        $query = $this->Notes->find();

        /**
         * 2. paginate()メソッドでfind()の結果をページ分割する
         * 
         * デフォルトは20件
         */

        /**
         * paginateを制御したい場合
         */
        $this->paginate = [
            'limit' => 10,
            'order' => [
                'Notes.modified' => 'desc'
            ]
        ];

        $notes = $this->paginate($query);

        // 3. Viewに変数を渡す
        $this->set(compact('notes'));
    }

    /**
     * View method
     *
     * @param string|null $id Note id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $note = $this->Notes->get($id, contain: []);
        $this->set(compact('note'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        // 1. 新しい空の入れものを用意する
        $note = $this->Notes->newEmptyEntity();

        // 2. もしフォームからデータ（POST）が送られてきたら、
        if ($this->request->is('post')) {

            /**
             * 3. その中身を入れ物に入れる
             * $this->request->getData()...ユーザーがフォームに入力したデータ
             * patchEntity()...空っぽの$noteに、そのデータを流し込む
             */
            $note = $this->Notes->patchEntity($note, $this->request->getData());

            // 4.1. うまく保存出来たらメッセージを出して
            if ($this->Notes->save($note)) {
                $this->Flash->success(__('The note has been saved.'));

                // 5. メモ一覧に戻る
                return $this->redirect(['action' => 'index']);
            }
            // 4.2. うまくいかなかったらエラーメッセージを出す
            $this->Flash->error(__('The note could not be saved. Please, try again.'));
        }
        $this->set(compact('note'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Note id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        /**
         * 1. 指定されたメモを取り出す
         * 
         * データベースの中から、指定されたIDのノートを1つ取り出すという処理
         * SELECT * FROM notes WHERE id = 5; をやっている感じ
         */
        $note = $this->Notes->get($id, contain: []);

        // 2. Formが送信されたら
        if ($this->request->is(['patch', 'post', 'put'])) {

            // 3. 入力された内容をノートに反映する
            $note = $this->Notes->patchEntity($note, $this->request->getData());

            // 4. 保存を試みる
            if ($this->Notes->save($note)) {

                // 5.1. 保存出来たら成功メッセージを表示する
                $this->Flash->success(__('The note has been saved.'));

                // 6 メモ一覧に戻る
                return $this->redirect(['action' => 'index']);
            }

            // 5.2. 保存に失敗したら失敗メッセージを表示する
            $this->Flash->error(__('The note could not be saved. Please, try again.'));
        }
        $this->set(compact('note'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Note id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        /**
         * 1. メソッドを制限する
         * 
         * URLを直接たたいた時にはじきたいから。
         * 要はセキュリティのため。
         * 
         * HTTPメソッドがPOST, DELETEではない場合、
         * Method Not Allowed というエラーになってほしい。
         */
        $this->request->allowMethod(['post', 'delete']);

        // 2. 指定されたメモを取得する
        $note = $this->Notes->get($id);

        // 3. 削除を試みる
        if ($this->Notes->delete($note)) {

            // 4.1. 成功したら成功メッセージ
            $this->Flash->success(__('The note has been deleted.'));
        } else {
            // 4.2. 成功しなかったら失敗メッセージ
            $this->Flash->error(__('The note could not be deleted. Please, try again.'));
        }

        // 5. 一覧へ戻る
        return $this->redirect(['action' => 'index']);
    }
}
