<?php

/**
 * URLパターンと、呼び出すコントローラ／アクションの対応表を書くファイル
 * 
 * つまり、「どのURLを叩いたときに、どの処理を実行するか」を決める“地図”のようなもの。
 * 
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    /**
     * URLの命名ルールを決める
     * 
     * DashedRoute なら、
     * /user-profile → UserProfileController で解釈する。
     */
    $routes->setRouteClass(DashedRoute::class);

    /**
     * 「/（ルートパス）以下のURLはこの中で定義する」というグループ。
     * 
     * この中に connect() を書いて、URLとコントローラを結びつける。
     */
    $routes->scope('/', function (RouteBuilder $builder): void {
        // / にアクセスしたら、PagesController の display('home') を呼ぶ。
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

        // /pages/〇〇 にアクセスしたら PagesController::display(〇〇) を呼ぶ。
        $builder->connect('/pages/*', 'Pages::display');

        // コントローラ名とアクション名に対応するURLを自動生成するやつ。
        $builder->fallbacks();

        /**
         * /users
         * 
         * 単純な固定ページ 3 つ
         */
        $builder->scope('/users', function (RouteBuilder $b) {
            $b->connect('/login', ['controller' => 'Users', 'action' => 'login']);
            $b->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
            $b->connect('/register', ['controller' => 'Users', 'action' => 'register']);
        });

        /**
         * /tasks
         * 
         * setMethods() で許可メソッドを制御（セキュリティ高い）
         * setPatterns(['id' => '\d+']) で id を数字だけに限定（安全）
         * setPass(['id']) で URL {id} をコントローラの引数 $id に渡す
         * 
         * 認証・認可・安全設計。
         */
        $builder->scope('/tasks', function (RouteBuilder $b) {
            $b->connect('/', ['controller' => 'Tasks', 'action' => 'index'])->setMethods(['GET']);
            $b->connect('/add', ['controller' => 'Tasks', 'action' => 'add'])->setMethods(['GET', 'POST']);
            $b->connect('/view/{id}', ['controller' => 'Tasks', 'action' => 'view'])
                ->setPatterns(['id' => '\d+'])->setPass(['id'])->setMethods(['GET']);
            $b->connect('/edit/{id}', ['controller' => 'Tasks', 'action' => 'edit'])
                ->setPatterns(['id' => '\d+'])->setPass(['id'])->setMethods(['GET', 'POST', 'PUT', 'PATCH']);
            $b->connect('/delete/{id}', ['controller' => 'Tasks', 'action' => 'delete'])
                ->setPatterns(['id' => '\d+'])->setPass(['id'])->setMethods(['POST', 'DELETE']);
            $b->connect('/toggle/{id}', ['controller' => 'Tasks', 'action' => 'toggle'])
                ->setPatterns(['id' => '\d+'])->setPass(['id'])->setMethods(['POST']);
        });

        /**
         * /articles
         * 
         * 「action名をURLに含めてOK」というゆるいルーティング。
         * setPatterns(['action' => 'add|edit|view|delete|index']) で許可アクションを限定
         * でも {id} のバリデーションはない（数字以外でも通る）
         */
        $builder->scope('/articles', function (RouteBuilder $b) {
            $b->connect('/', ['controller' => 'Articles', 'action' => 'index']);
            $b->connect('/:action/*', ['controller' => 'Articles'])
                ->setPatterns(['action' => 'add|edit|view|delete|index']);
        });

        /**
         * /notes
         * 
         * 一番ざっくり。
         * /:action/* なので、action と後続の任意パラメータを全部受け取る。
         * 
         * setPatterns() もメソッド制限もなし
         * ほぼ CakePHP のデフォルト動作
         * URLも自由だけど、間違ったURLも通る（安全性・可読性やや低め）
         */
        $builder->scope('/notes', function (RouteBuilder $b) {
            $b->connect('/', ['controller' => 'Notes', 'action' => 'index']);
            $b->connect('/:action/*', ['controller' => 'Notes'])
                ->setPass(['id']);
        });
    });
};
