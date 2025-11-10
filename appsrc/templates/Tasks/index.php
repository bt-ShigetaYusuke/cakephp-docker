<!-- templates/Tasks/index.php -->
<h1>タスク一覧</h1>

<?php
/**
 * タスクの絞り込み検索フォーム（GETメソッド）
 * 
 * ・type => 'get'だから、フォーム送信時にはデータはURLクエリとして送信される
 * ・$this->request->getQuery()で、現在のURLに含まれているクエリパラメータを取得する
 * 
 * 1. View（フォーム）でGET送信する
 * → URLにクエリがつく
 * 
 * 2. Controller で $this->request->getQueryParams() で受け取る
 * → 送られてきた検索条件を $q に全部まとめて入れる
 * 
 * 3. $query に where() を重ねていって条件をつける
 * → Tasks テーブルから絞り込み条件！
 */
?>
<?= $this->Form->create(null, ['type' => 'get']) ?>
<?= $this->Form->control('title', ['label' => 'タイトル', 'value' => $this->request->getQuery('title')]) ?>
<?= $this->Form->control('is_done', [
    'type' => 'select',
    'options' => ['' => '全て', '0' => '未完了', '1' => '完了'],
    'label' => 'ステータス',
    'value' => $this->request->getQuery('is_done')
]) ?>
<?= $this->Form->control('overdue', ['type' => 'checkbox', 'label' => '期限切れ（未完了のみ）', 'checked' => (bool)$this->request->getQuery('overdue')]) ?>
<?= $this->Form->button('絞り込み') ?>
<?= $this->Html->link('クリア', ['action' => 'index']) ?>
<?= $this->Form->end() ?>

<p><?= $this->Html->link('新規タスク', ['action' => 'add']) ?></p>

<table>
    <thead>
        <tr>
            <th>タイトル</th>
            <th>期限</th>
            <th>優先度</th>
            <th>状態</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= h($task->title) ?></td>
                <td><?= h($task->due_date) ?></td>
                <td><?= h($task->priority) ?></td>
                <td><?= $task->is_done ? '完了' : '未完了' ?></td>
                <td>
                    <?= $this->Html->link('表示', ['action' => 'view', $task->id]) ?>
                    <?= $this->Html->link('編集', ['action' => 'edit', $task->id]) ?>
                    <?php
                    /**
                     * 完了／未完了の切り替えボタン
                     * 
                     * - 三項演算子でボタンの表示切り替え
                     * - confirm で確認ダイアログを表示
                     * 
                     * Html->link() は GETリクエスト
                     * Form->postLink() は 見た目はリンクなのに内部的にフォームを送信してPOST送信する
                     */
                    ?>
                    <?= $this->Form->postLink(
                        $task->is_done ? '未完了へ' : '完了にする',
                        ['action' => 'toggle', $task->id],
                        // ['confirm' => '切り替えますか？']
                    ) ?>
                    <?= $this->Form->postLink('削除', ['action' => 'delete', $task->id], ['confirm' => '削除しますか？']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
// $this->element('pagination') ?? ''
?>