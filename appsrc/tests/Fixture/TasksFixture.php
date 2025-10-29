<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TasksFixture
 */
class TasksFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Lorem ipsum dolor sit amet',
                'due_date' => '2025-10-29',
                'priority' => 1,
                'is_done' => 1,
                'created' => '2025-10-29 06:43:40',
                'modified' => '2025-10-29 06:43:40',
            ],
        ];
        parent::init();
    }
}
