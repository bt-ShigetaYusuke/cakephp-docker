<?php

/**
 * CakePHP の 「認可（Authorization）」 仕組みの中で、
 * 「Tasksテーブル全体に対するアクセスルール」 を定義するファイル
 * 
 * 「誰がどのタスクを見られる／作れるのか」を決めるルールブック。
 * しかも「タスク1件」ではなく「タスク一覧（Table全体）」に対するルール。
 * 
 * 「一覧を見ていいか」「作っていいか」「どこまで見えるか」を判定
 * 
 * 流れ:
 * 1. /tasks にアクセスする
 * 
 * 2. Controllerのindex()内で、
 *    $this->Authorization->authorize($this->Tasks, 'index');
 *    $query = $this->Authorization->applyScope($this->Tasks->find(), 'index');
 * 
 * 3. ここでCakePHPは TasksTablePolicyを探して:
 *    一覧見ていい？とか、
 *    どのレコードまで見ていい？とかの設定を適用。
 * 
 * 4. 結果、自分のタスクだけが表示される
 */

declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

class TasksTablePolicy
{
  /**
   * /tasks の一覧許可（一覧ページへ入れるかどうか）
   * 
   * コントローラーのメソッド名: index()
   * ポリシーメソッド名: canIndex()
   * 
   * can + アクション名（先頭大文字）って名前で決まってる。
   */
  public function canIndex(IdentityInterface $user, Table $table): bool
  {
    return true; // ログイン済みなら誰でも OK
  }

  // 新規作成画面への許可
  public function canAdd(IdentityInterface $user, Table $table): bool
  {
    return true; // ログイン済みなら OK
  }

  // 一覧のスコープ（＝自分のタスクだけ見える）
  public function scopeIndex(IdentityInterface $user, SelectQuery $query): SelectQuery
  {
    return $query->where(['Tasks.user_id' => $user->getIdentifier()]);
  }
}
