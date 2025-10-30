<!-- templates/Users/login.php -->
<h1>ログイン</h1>
<?= $this->Form->create() ?>
<?= $this->Form->control('email') ?>
<?= $this->Form->control('password') ?>
<?= $this->Form->button('ログイン') ?>
<?= $this->Form->end() ?>

<p><?= $this->Html->link('新規登録', ['action' => 'register']) ?></p>