<?php
namespace App\Test\Fixture;

use Origin\TestSuite\Fixture;

class QueueFixture extends Fixture
{
    protected $table = 'queue';

    protected $schema = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'queue' => ['type' => 'string', 'limit' => 80, 'null' => false, 'default' => null],
            'data' => ['type' => 'text', 'null' => false,'limit' => 16777215, 'default' => null],
            'status' => ['type' => 'string', 'limit' => 40, 'null' => false, 'default' => null],
            'locked' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'scheduled' => ['type' => 'datetime', 'null' => false, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'column' => 'id'],
        ],
        'indexes' => [
            'queue_index' => ['type' => 'index', 'column' => 'queue'],
        ],
        'options' => ['engine' => 'InnoDB', 'collation' => 'utf8mb4_0900_ai_ci','autoIncrement' => 1000],
     
    ];
}
