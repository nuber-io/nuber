<?php
namespace App\Test\Fixture;

use Origin\TestSuite\Fixture;

class ImapFixture extends Fixture
{
    protected $table = 'imap';
    
    protected $schema = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'account' => ['type' => 'string', 'null' => false, 'default' => null],
            'message_id' => ['type' => 'string', 'null' => false, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'column' => 'id'],
        ],
        'indexes' => [
            'imap_account_idx' => ['type' => 'index', 'column' => 'account'],
        ],
        'options' => ['engine' => 'InnoDB', 'autoIncrement' => 1000],
    ];

    protected $records = [];
}
