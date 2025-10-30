<?php

/**
 * CakePHP の Authorization（認可）システム における
 * 「1件のタスク（Task エンティティ）に対して
 * “このユーザーが操作して良いか” を判断するルール」を定義するクラス
 * 
 * 「Taskの中身（＝タスク1件）を、誰が触っていいのか」を決めてる部分。
 * 
 * CakePHP の Authorization は「リソースごと（テーブル・エンティティ）」に
 * 「何をしていいか」を明示的に判断させる設計になっている。
 * 
 *   TasksTablePolicy → テーブル全体の操作（一覧・作成・スコープ）を制御
 *   TaskPolicy → 個別レコード（1件）の操作（閲覧・編集・削除など）を制御
 * 
 * 以下のように呼ばれた時の流れ:
 * $task = $this->Tasks->get($id);
 * $this->Authorization->authorize($task, 'delete');
 * 
 * 1. $task → App\Model\Entity\Task と判断
 * 
 * 2. 対応するポリシー App\Policy\TaskPolicy を探す
 * 
 * 3. delete → canDelete() メソッドを呼ぶ
 * 
 * 4. 判定結果（true / false）を受け取る
 * 
 * 5. falseなら ForbiddenException（403）を投げる
 */

namespace App\Policy;

use App\Model\Entity\Task;
use Authorization\IdentityInterface;

class TaskPolicy
{
  // 一覧/作成/閲覧/更新/削除/トグル で使う共通判定
  public function canIndex(IdentityInterface $user, $taskClass): bool
  {
    return true;
  }
  public function canAdd(IdentityInterface $user, $taskClass): bool
  {
    return true;
  }

  public function canView(IdentityInterface $user, Task $task): bool
  {
    return $task->user_id === $user->getIdentifier();
  }
  public function canEdit(IdentityInterface $user, Task $task): bool
  {
    return $task->user_id === $user->getIdentifier();
  }
  public function canDelete(IdentityInterface $user, Task $task): bool
  {
    return $task->user_id === $user->getIdentifier();
  }
  public function canToggle(IdentityInterface $user, Task $task): bool
  {
    return $task->user_id === $user->getIdentifier();
  }
}
