<!-- templates/Tasks/add.php -->
<h1>タスク作成</h1>
<?= $this->Form->create($task) ?>
<?= $this->Form->control('title') ?>
<?= $this->Form->control('due_date', ['type' => 'date']) ?>
<?= $this->Form->control('priority', ['type' => 'number', 'min' => 0, 'max' => 10]) ?>
<?= $this->Form->control('is_done', ['type' => 'checkbox', 'label' => '完了']) ?>
<?= $this->Form->button('保存') ?>
<?= $this->Form->end() ?>