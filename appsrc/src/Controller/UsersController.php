<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;


/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * beforeFilter()
     * 
     * CakePHPのコントローラーがリクエストを処理する前に必ず呼ばれる「前処理フック」。
     * 共通処理でもあるわね。
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        /**
         * 1. 未ログインでもアクセス可能なアクションを指定。
         * 
         * 認証ミドルウェアは通常、「ログインしていないと全部リダイレクト」する。
         */
        $this->Authentication->addUnauthenticatedActions(['login', 'register']);

        /**
         * 2. このコントローラでは、認可チェック（authorize）をスキップさせる。
         * 
         * CakePHPでは通常、全てのリクエストで、
         * $this->Authorization->authorize($resource);
         * を使って、このユーザーが操作していいかをチェックする。
         * 
         * 「1.」で設定したアクションの認証はスキップ、
         * それ以外は認証実行ってこと。
         */
        $this->Authorization->skipAuthorization();
    }

    /**
     * login()
     */
    public function login()
    {
        // 1. GET, POST だけ許可
        $this->request->allowMethod(['get', 'post']);

        /**
         * 2. 認証プラグインがログイン処理を実行してくれている
         * 
         * AuthenticationMiddlewareがすでにPOSTデータを見てログイン試行していて、
         * ここでその結果を取得する
         * 
         * null: 未判定
         * $result->isValid() === true: ログイン成功
         * $result->isValid() === false: ログイン失敗
         */
        $result = $this->Authentication->getResult();

        /**
         * 3. 成功した場合の処理
         * 
         * getLoginRedirect()
         * → ログイン前に見ようとしていたURLを取得
         *   もし見ようとしていたページがなかったらタスク一覧に飛ばす
         */
        if ($result && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect()
                ?? ['controller' => 'Tasks', 'action' => 'index'];
            return $this->redirect($target);
        }

        // 4. POSTされたけど失敗された場合の処理
        if ($this->request->is('post')) {
            $this->Flash->error('メールまたはパスワードが違います。');
        }
    }

    /**
     * logout()
     * 
     * ログイン状態（セッション情報）を削除して、ログイン画面へ戻す。
     * GETでも呼びだし可。
     */
    public function logout()
    {
        // 1. セッション中のユーザー情報を削除
        $this->Authentication->logout();

        // 2. login画面に飛ばす
        return $this->redirect(['action' => 'login']);
    }

    /**
     * register()
     * 
     * 新しいユーザーをDBに保存する処理
     */
    public function register()
    {
        // 1. 新しいユーザーエンティティを作成
        $user = $this->Users->newEmptyEntity();

        // 2. フォーム送信されたら
        if ($this->request->is('post')) {

            // 3. 送信されたユーザー情報を$userエンティティに流し込む
            $user = $this->Users->patchEntity($user, $this->request->getData());

            // 4. 保存を試みる
            if ($this->Users->save($user)) {

                // 5.1. 成功したら成功メッセージ & ログイン画面に飛ばす
                $this->Flash->success('登録しました。ログインしてください。');
                return $this->redirect(['action' => 'login']);
            }

            // 5.2. 失敗したら失敗メッセージ
            $this->Flash->error('登録に失敗しました。入力内容をご確認ください。');
        }
        $this->set(compact('user'));
    }

    /**
     * Index()
     * 
     * ユーザー一覧画面
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Users->find();
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View()
     * 
     * ユーザー1件の詳細画面
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, contain: ['Tasks']);
        $this->set(compact('user'));
    }

    /**
     * add()
     * 
     * ユーザー追加画面
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * edit()
     * 
     * ユーザー情報編集画面
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * delete()
     * 
     * ユーザー情報削除機能
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
