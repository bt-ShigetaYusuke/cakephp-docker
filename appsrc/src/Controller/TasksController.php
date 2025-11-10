<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Tasks Controller
 *
 * @property \App\Model\Table\TasksTable $Tasks
 */
class TasksController extends AppController
{

    /**
     * beforeFilter()
     *
     * @param EventInterface $event
     * @return void
     * 
     * Tasksの共通処理
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // 全アクション認証必須
        // $this->Authorization->skipUnauthenticated([]);

        /**
         * もし一覧をログインなしで見せたい場合は以下のように書く
         * 
         *     $this->Authorization->skipUnauthenticated(['index']);
         */
    }

    /**
     * index()
     * 
     * 一覧ページ
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /**
         * 1. 認証チェック
         * 
         * ここで、以下がよばれる
         * 
         * TasksPolicy::canIndex($user, TasksTable $tasks)
         */
        $this->Authorization->authorize($this->Tasks, 'index');

        /**
         * 2. ユーザーが見れる範囲を設定
         * 
         * 
         */
        $query = $this->Authorization->applyScope($this->Tasks->find(), 'index');

        // 3. ログインしているユーザーのIDを特定
        $identity = $this->request->getAttribute('identity');
        $userId = $identity->getIdentifier();

        // 4. Tasksテーブルから、ユーザーに対応するタスクを取得
        $query = $this->Tasks->find()
            ->where(['Tasks.user_id' => $userId])
            ->order(['due_date' => 'ASC', 'priority' => 'DESC', 'Tasks.id' => 'DESC']);

        // 検索ボックスから送られてきた「検索条件」を受け取る
        $q = $this->request->getQueryParams();

        // 完了タスクを絞り込み
        if (isset($q['is_done']) && $q['is_done'] !== '') {
            $query->where(['Tasks.is_done' => (int)$q['is_done']]);
        }

        // 期日が過ぎているタスクを絞り込み
        if (!empty($q['overdue'])) {
            $query->where(['Tasks.due_date <' => date('Y-m-d'), 'Tasks.is_done' => 0]);
        }

        // タイトル文字列検索
        if (!empty($q['title'])) {
            $query->where(['Tasks.title LIKE' => '%' . trim($q['title']) . '%']);
        }

        $tasks = $this->paginate($query);
        $this->set(compact('tasks'));
    }

    /**
     * View method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $task = $this->Tasks->get($id);
        $this->Authorization->authorize($task, 'view');
        $this->set(compact('task'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $task = $this->Tasks->newEmptyEntity();
        $this->Authorization->authorize($this->Tasks, 'add');

        if ($this->request->is('post')) {
            $task = $this->Tasks->patchEntity($task, $this->request->getData());
            $task->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Tasks->save($task)) {
                $this->Flash->success('タスクを作成しました。');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('作成に失敗しました。入力内容をご確認ください。');
        }
        $this->set(compact('task'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $task = $this->Tasks->get($id);
        $this->Authorization->authorize($task, 'edit');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->Tasks->patchEntity($task, $this->request->getData());
            if ($this->Tasks->save($task)) {
                $this->Flash->success('更新しました。');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('更新に失敗しました。');
        }
        $this->set(compact('task'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $task = $this->Tasks->get($id);
        $this->Authorization->authorize($task, 'delete');

        if ($this->Tasks->delete($task)) {
            $this->Flash->success('削除しました。');
        } else {
            $this->Flash->error('削除に失敗しました。');
        }
        return $this->redirect(['action' => 'index']);
    }

    public function toggle($id = null)
    {
        // 1. POSTリクエスト以外をブロック
        $this->request->allowMethod(['post']);

        // 2. 該当タスクをDBから取得
        $task = $this->Tasks->get($id);

        // 3. 権限チェック
        $this->Authorization->authorize($task, 'toggle');

        /**
         * 4. 完了状態をトグルする
         * 
         * 完了（1）なら → 未完了（0）
         * 未完了（0）なら → 完了（1）
         * 
         * !（感嘆符）は「否定演算子」＝ trueをfalseに、falseをtrueにする
         */
        $task->is_done = !$task->is_done;

        // if ($task->is_done) {
        //     $task->is_done = 0;
        // } else {
        //     $task->is_done = 1;
        // }

        // 5.1. 成功したら成功メッセージ
        if ($this->Tasks->save($task)) {
            $this->Flash->success('ステータスを切り替えました。');

            // 5.2. 失敗したら失敗メッセージ
        } else {
            $this->Flash->error('切り替えに失敗しました。');
        }

        // 6. 元のページへリダイレクト
        return $this->redirect($this->referer(['action' => 'index'], true));
    }
}
