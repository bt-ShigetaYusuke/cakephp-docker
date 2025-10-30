<?php

/**
 * 
 * 記事追加ページ
 * 
 * 表示方法:
 * - 記事一覧ページの
 * - 「NEW ARTICLE」を押下
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Articles'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="articles form content">
            <?= $this->Form->create($article) ?>
            <?php
            /**
             * CakePHPのFormHelper
             * 
             * $article に対応するフォームを開始する formタグを作る
             * CSRFトークンやセキュリティトークンも自動で埋め込まれる。
             */
            ?>
            <fieldset>
                <legend><?= __('Add Article') ?></legend>
                <?php
                echo $this->Form->control('title');
                echo $this->Form->control('body');
                ?>
                <?php
                /**
                 * FormHelperが、$article->titleの値やバリデーション情報を元に
                 * 適切な<input>と<label>をまとめて出力する。
                 * 
                 * 裏側の処理
                 * 1. エンティティと照合
                 *    $this->Form->create($article)で渡された$articleの中から、$titleプロパティを探す
                 * 2. 初期値設定
                 *    $article->titleに値があれば、自動でvalue="..."に反映
                 * 3. ラベル生成
                 *    自動的に<label>を作成（Title）など
                 * 4. バリデーション連携
                 *    ArticlesTableのvalidationDefault()に基づいて、エラーがあればメッセージを表示
                 * 5. フィールドタイプ自動判定
                 *    データベースのカラム型を見て、text / textarea / checkbox / datetime などを自動選択
                 */
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?php
            /**
             * 出力例
             * <button type="submit">Submit</button>
             */
            ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>