<?php

/**
 * 
 * 記事一覧ページ
 * 
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<div class="articles index content">
    <?= $this->Html->link(__('New Article'), ['action' => 'add'], ['id' => 'test-newarticle', 'class' => 'button float-right']) ?>
    <?php
    /**
     * HtmlHelper を使ってリンクを生成。
     * aタグを簡単・安全に生成してくれる
     * 
     * 第一引数:
     * リンクのテキスト
     * __('')...多言語化対応関数（国際化ヘルパー）
     * 翻訳ファイルを用意すると自動で翻訳される。
     * 
     * 第二引数:
     * リンク先（ルーティング指定）
     * 
     * 第三引数:
     * HTML属性
     * 
     * ↓ 実際に生成される aタグ
     * <a href="/articles/add" class="button float-right">New Article</a>
     */
    ?>
    <h3><?= __('Articles') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id', 'ID') ?></th>
                    <th><?= $this->Paginator->sort('title', 'Title') ?></th>
                    <th><?= $this->Paginator->sort('created', 'Created') ?></th>
                    <th><?= $this->Paginator->sort('modified', 'Modified') ?></th>
                    <?php
                    /**
                     * PaginatorHelper を使って並べ替えリンクを生成。
                     * id カラムで昇順、降順に並び替えるためのリンクを自動生成している。
                     * 
                     * PaginatorHelper...一覧ページのページソーシングやソート機能を担当するヘルパー
                     * ArticlesController::index() の中でpaginate() を使っているから、自動的に使えるようになっている。
                     */
                    ?>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr>
                        <td><?= $this->Number->format($article->id) ?></td>
                        <?php
                        /**
                         * NumberHelper...CakePHP標準の数値フォーマット用ヘルパー
                         * 
                         * format()の他に、precision()とかcurrency()とかがある。
                         * 
                         * コントローラやモデルでは「生の値（int, float）」のまま扱うが、
                         * テンプレートで表示時にフォーマットしてあげる。3桁区切りになる。
                         * 
                         * 通貨として表示
                         * <?= $this->Number->currency($article->price, 'JPY') ?>
                         * 
                         * 少数2桁で表示
                         * <?= $this->Number->precision($article->rate, 2) ?>
                         * 
                         * パーセント表示
                         * <?= $this->Number->toPercentage($article->completion) ?>
                         */
                        ?>
                        <td><?= h($article->title) ?></td>
                        <td><?= h($article->created) ?></td>
                        <td><?= h($article->modified) ?></td>
                        <?php
                        /**
                         * h() は CakePHP に用意されているショートカット関数で、
                         * 内部的には htmlspecialchars() を呼ぶ。
                         * 
                         * つまりエスケープ処理。
                         * 
                         * CakePHPではテンプレートにデータベースの値を埋め込むことが多いが、
                         * もし悪意のあるスクリプト文字列が入っていた場合、そのまま出したら
                         * ブラウザが<script>を実行してしまう。
                         * 
                         * つまりXSS攻撃が起こる。
                         * 
                         * エスケープ処理しておけばスクリプト文字列は文字として表示されるだけで
                         * 実行はされない。
                         * 
                         * Viewで変数を出力する場合は必ずエスケープ処理させる。
                         * これがCakePHPのルールらしい。
                         */
                        ?>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $article->id]) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $article->id]) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $article->id],
                                [
                                    'method' => 'delete',
                                    'confirm' => __('Are you sure you want to delete # {0}?', $article->id),
                                ]
                            ) ?>
                            <?php
                            /**
                             * $this->Form->postLink()...FormHelperのメソッド。
                             * 
                             * 見た目は aタグっぽいけど、内部的には 小さなフォーム + JavaScript を生成して、
                             * クリック時に「POST/DELETEメソッドのリクエスト」を送信する。
                             * 
                             * 第1引数：リンク文字列
                             * 
                             * 第2引数：送信先
                             * /articles/delete/1 って感じ。
                             * 
                             * 第3引数：オプション
                             * 'method' => 'delete'...
                             * リクエストメソッドを DELETE に（CakePHPが対応）
                             * 
                             * 'confirm' => ...
                             * 確認ダイアログを出す（OKを押すと送信）
                             */
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>