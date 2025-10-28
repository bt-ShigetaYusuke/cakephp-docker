<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Notes Model
 *
 * @method \App\Model\Entity\Note newEmptyEntity()
 * @method \App\Model\Entity\Note newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Note> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Note get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Note findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Note patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Note> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Note|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Note saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Note>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Note>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Note>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Note> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Note>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Note>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Note>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Note> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class NotesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // 1. DBのテーブル名指定
        $this->setTable('notes');

        // 2. 一覧表示用のフィールド
        $this->setDisplayField('title');

        // 3. 主キー設定
        $this->setPrimaryKey('id');

        // 4. 自動的に日時を管理
        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            // 1. 文字列（スカラー）であることを保証
            ->scalar('title')

            // 2. タイトルの最大文字数は120文字までで
            ->maxLength('title', 120)

            // 3. 新規作成時にはtitleフィールドが必要
            ->requirePresence('title', 'create')

            // 4. 空欄だったらこのメッセージでエラー表示
            ->notEmptyString('title', 'タイトルは必須でお願い');

        $validator
            // 1. 文字列（スカラー）であることを保証
            ->scalar('content')

            // 2. 新規作成時にはcontentフィールドが必要
            ->requirePresence('content', 'create')

            // 3. 空欄だったらこのメッセージでエラー表示
            ->notEmptyString('content', '内容は必須でお願い');

        return $validator;
    }
}
