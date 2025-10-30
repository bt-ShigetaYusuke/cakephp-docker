<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;

use Authentication\Middleware\AuthenticationMiddleware;
use Authorization\Middleware\AuthorizationMiddleware;
use Authorization\Policy\OrmResolver;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationService;
use Authorization\AuthorizationService;
use Authorization\AuthorizationServiceInterface;
use Authorization\AuthorizationServiceProviderInterface;

use Psr\Http\Message\ServerRequestInterface;


/**
 * ApplicationクラスはCakePHPが起動するとき、
 * 以下の2つの役割を果たしている
 * 
 * 1. boostrap（起動時の初期化）
 *    プラグインを読み込んだり、設定ファイルを初期化する
 * 2. middleware（リクエスト処理の流れを構築）
 *    リクエストを受け取ってレスポンスを返すまでの処理を並べる
 */

/**
 * 1. extends BaseApplication
 *    CakePHPのアプリの土台を継承。
 *    全体初期化の入り口
 * 
 * 2. implements AuthenticationServiceProviderInterface
 *    「このアプリは認証サービスを提供できます」と宣言。
 * 
 * 3. implements AuthorizationServiceProviderInterface
 *    「このアプリは許可サービスを提供できます」と宣言。
 */
class Application extends BaseApplication
implements AuthenticationServiceProviderInterface, AuthorizationServiceProviderInterface
{
    /**
     * 
     * アプリ全体の準備
     * 
     * プラグイン等を読み込む
     *
     * @return void
     */
    public function bootstrap(): void
    {
        /**
         * 1. 親クラスの初期化（CakePHPコアのブート処理）
         * 
         * config/bootstrap.php を読み込む
         * DebugKit や Migrations など、composer で登録されたプラグインを自動ロード
         * エラー・ロギング・キャッシュ設定などを初期化
         * 
         * とかやってる。
         * 
         * フレームワーク標準の初期化。
         */
        parent::bootstrap();

        /**
         * 2. プラグイン追加
         * 
         * これ読み込まなくてもいけるぞ？
         * 
         * CakePHP 5 では Plugin::autoload() がデフォルトで有効になってるかららしい。
         */
        // $this->addPlugin('Authentication');
        // $this->addPlugin('Authorization');

        /**
         * 3. CLI以外（Webアクセス時）だけTableLocator設定を調整
         * 
         * FactoryLocator:
         *   CakePHP の依存解決マネージャー
         *   Model や Table のインスタンスを「クラス名から生成」する仕組みを管理。
         * 
         * TableLocator:
         *   ORM のテーブルクラスを探して作るオブジェクト。
         * 
         * ちゃんとしたテーブルの設計図がないときは、勝手に作らないでエラーにしてくれる。
         * 
         * CLIで操作してるときはテーブルがなくてもOK。
         */
        if (PHP_SAPI !== 'cli') {
            FactoryLocator::add('Table', (new TableLocator())->allowFallbackClass(false));
        }
    }

    /**
     * 「HTTPリクエストをどう処理するか」の流れを決める。
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // 1. エラーハンドリング（最初に置く：他で起きた例外をキャッチする）
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // 2. 静的ファイル（CSS, JS, 画像など）
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // 3. ルーティング（URL → コントローラへ）
            ->add(new RoutingMiddleware($this))

            // 4. ボディ解析（JSONなどのリクエストを配列にする）
            ->add(new BodyParserMiddleware())

            // 5. CSRF保護（POST前にトークンをチェック）
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))

            // 6. 認証（ログイン状態の確認・未ログインリダイレクト）
            ->add(new AuthenticationMiddleware($this))

            // 7. 認可（ログイン済みユーザーのアクセス許可判定）
            ->add(new AuthorizationMiddleware($this, [
                'requireAuthorizationCheck' => false,
                'unauthorizedHandler' => ['className' => 'Authorization.Exception'],
            ]));

        return $middlewareQueue;
    }

    /**
     * 
     * アプリの権限をチェックするルールを設定
     * 
     * AuthorizationServiceProviderInterface の中で実装必須のメソッド。
     * 
     */
    public function getAuthorizationService(
        \Psr\Http\Message\ServerRequestInterface $request
    ): AuthorizationServiceInterface {
        /**
         * ルールをどこで使うか教える
         * 
         * ex)
         * 対象: TasksTable
         * クラス名: App\Policy\TasksTablePolicy
         * ファイルパス: src/Policy/TasksTablePolicy.php
         * 
         * 対象: Task
         * クラス名: App\Policy\TaskPolicy
         * ファイルパス: src/Policy/TaskPolicy.php
         */
        $resolver = new OrmResolver(); // src/Policy 配下を自動解決
        return new AuthorizationService($resolver);
    }

    /**
     * 
     * ログインの方法（フォーム認証など）を定義
     * 
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $fields = ['username' => 'email', 'password' => 'password'];

        /**
         * 1. 認証サービスを作る
         */
        $service = new AuthenticationService([
            // 未ログインで認証が必要なページにアクセスしたとき、どこへ飛ばすかを設定
            'unauthenticatedRedirect' => Router::url('/users/login'),
            // 元のURLを保持するパラメータ。ログイン後ここに戻す。
            'queryParam' => 'redirect',
        ]);

        /**
         * 2. どうやって認証するかを登録
         */

        /**
         * 2.1. セッション認証（ログイン済か確認）
         * 
         * セッションにユーザー情報があれば、それを使って認証OKにする。
         * 毎回ログインフォームを出す必要がないのは、このおかげ。
         */
        $service->loadAuthenticator('Authentication.Session');

        /**
         * 2.2. フォーム認証（ログインフォームからのPOST）
         * 
         * ログインフォームで送られた情報を使って認証する設定。
         * field でフォームの input 名と DBカラム名を紐づける。
         * loginUrl はログインページのURL。ここでPOSTが送信されたとき認証を実行。
         */
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => $fields,
            'loginUrl' => '/users/login',
        ]);

        /**
         * 3. どうやってユーザーを特定するかを登録
         * 
         * Authentication.Password でDB上のユーザーを探して、ハッシュされたパスワードを照合する
         * field でDBカラム名を指定
         * 
         * Usersテーブルの email カラムでユーザーを探して、入力されたパスワードをハッシュで照合する
         */
        $service->loadIdentifier('Authentication.Password', [
            'fields' => $fields,
        ]);

        return $service;
    }


    /**
     * 
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void {}
}
