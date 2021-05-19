<?php
use Origin\Model\Schema;

class ApplicationSchema extends Schema
{
    const VERSION = 20200601102446;

    public $automated_backups = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => null, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'host_id' => ['type' => 'integer', 'limit' => null, 'unsigned' => false, 'null' => true, 'default' => null],
            'instance' => ['type' => 'string', 'limit' => 255, 'null' => true, 'default' => null],
            'frequency' => ['type' => 'string', 'limit' => 255, 'null' => true, 'default' => null],
            'at' => ['type' => 'time', 'null' => true, 'default' => null],
            'retain' => ['type' => 'integer', 'limit' => null, 'unsigned' => false, 'null' => true, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => true, 'default' => null]
        ],
        'constraints' => [
            'primary' => ['type' => 'unique', 'column' => 'id']
        ],
        'indexes' => [],
        'options' => ['engine' => 'InnoDB', 'autoIncrement' => 1000],
    ];

    public $migrations = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'version' => ['type' => 'bigint', 'limit' => 20, 'unsigned' => false, 'null' => false, 'default' => null],
            'rollback' => ['type' => 'text', 'limit' => 16777215, 'null' => true, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => false, 'default' => null]
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'column' => 'id']
        ],
        'indexes' => [
            'migrations_version_index' => ['type' => 'index', 'column' => 'version']
        ],
        'options' => ['engine' => 'InnoDB']
    ];

    public $hosts = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'name' => ['type' => 'string', 'limit' => 255, 'null' => true, 'default' => null],
            'address' => ['type' => 'string', 'limit' => 255, 'null' => true, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => true, 'default' => null]
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'column' => 'id']
        ],
        'indexes' => [],
        'options' => ['engine' => 'InnoDB', 'autoIncrement' => 1000],
    ];

    public $users = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'first_name' => ['type' => 'string', 'limit' => 40, 'null' => false, 'default' => null],
            'last_name' => ['type' => 'string', 'limit' => 80, 'null' => false, 'default' => null],
            'email' => ['type' => 'string', 'limit' => 255, 'null' => true, 'default' => null],
            'password' => ['type' => 'string', 'limit' => 255, 'null' => false, 'default' => null],
            'description' => ['type' => 'text', 'null' => true, 'default' => null],
            'token' => ['type' => 'text', 'null' => false, 'default' => null],
            'verified' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => false, 'default' => null]
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'column' => 'id'],
            'email' => ['type' => 'unique', 'column' => 'email']
        ],
        'indexes' => [],
        'options' => ['engine' => 'InnoDB', 'autoIncrement' => 1000],
    ];

    protected $queue = [
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
            'scheduled_index' => ['type' => 'index', 'column' => 'scheduled'],
        ],
        'options' => ['engine' => 'InnoDB', 'autoIncrement' => 1000],
    ];
}
