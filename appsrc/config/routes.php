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
        // $builder->fallbacks();
    });

    /**
     * /tutorial スコープ
     */
    $routes->scope('/tutorial', function (RouteBuilder $builder) {
        // /tutorial/articles → ArticlesController::index
        $builder->connect('/articles', ['controller' => 'Articles', 'action' => 'index']);

        // /tutorial/articles/add, /tutorial/articles/edit/1, /tutorial/articles/delete/1 ... など
        $builder->connect('/articles/:action/*', ['controller' => 'Articles'])
            ->setPatterns(['action' => 'add|edit|view|delete|index']);

        // /tutorial/memo を Notes コントローラへ
        $builder->connect('/memo', ['controller' => 'Notes', 'action' => 'index']);
        $builder->connect('/memo/:action/*', ['controller' => 'Notes'])
            ->setPass(['id']);  // /edit/1 などのIDを受け取れるように

        $builder->fallbacks(DashedRoute::class);
    });
};
