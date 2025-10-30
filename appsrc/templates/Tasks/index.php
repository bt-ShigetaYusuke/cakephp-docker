<!-- templates/Tasks/index.php -->
<h1>タスク一覧</h1>

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
                    <?= $this->Form->postLink(
                        $task->is_done ? '未完了へ' : '完了にする',
                        ['action' => 'toggle', $task->id],
                        ['confirm' => '切り替えますか？']
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