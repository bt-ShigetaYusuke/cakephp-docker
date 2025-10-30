<!-- templates/Users/register.php -->
<h1>新規登録</h1>
<?= $this->Form->create($user) ?>
<?= $this->Form->control('email') ?>
<?= $this->Form->control('password') ?>
<?= $this->Form->button('登録') ?>
<?= $this->Form->end() ?>

<p><?= $this->Html->link('ログインへ', ['action' => 'login']) ?></p>