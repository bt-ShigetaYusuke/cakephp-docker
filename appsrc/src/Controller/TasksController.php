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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // 全アクション認証必須
        // $this->Authorization->skipUnauthenticated([]); // 何もスキップしない＝必須
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->authorize($this->Tasks, 'index');
        $query = $this->Authorization->applyScope($this->Tasks->find(), 'index');
        $identity = $this->request->getAttribute('identity'); // Authentication Identity
        $userId = $identity->getIdentifier();

        $query = $this->Tasks->find()
            ->where(['Tasks.user_id' => $userId])
            ->order(['due_date' => 'ASC', 'priority' => 'DESC', 'Tasks.id' => 'DESC']);

        // 検索・絞り込み
        $q = $this->request->getQueryParams();

        if (isset($q['is_done']) && $q['is_done'] !== '') {
            $query->where(['Tasks.is_done' => (int)$q['is_done']]);
        }
        if (!empty($q['overdue'])) {
            $query->where(['Tasks.due_date <' => date('Y-m-d'), 'Tasks.is_done' => 0]);
        }
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
        $this->request->allowMethod(['post']); // CSRF対策：POST限定
        $task = $this->Tasks->get($id);
        $this->Authorization->authorize($task, 'toggle');

        $task->is_done = !$task->is_done;
        if ($this->Tasks->save($task)) {
            $this->Flash->success('ステータスを切り替えました。');
        } else {
            $this->Flash->error('切り替えに失敗しました。');
        }
        return $this->redirect($this->referer(['action' => 'index'], true));
    }
}
