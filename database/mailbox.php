<?php

use Origin\Model\Schema;

class MailboxSchema extends Schema
{
    const VERSION = 20191116000000;

    /**
     * Table name
     *
     * @var array
     */
    protected $mailbox = [
        'columns' => [
            'id' => ['type' => 'integer', 'limit' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'autoIncrement' => true],
            'message_id' => ['type' => 'string', 'null' => false, 'default' => null],
            'checksum' => ['type' => 'string', 'limit' => 40, 'null' => false, 'default' => null],
            'message' => ['type' => 'text', 'limit' => 4294967295, 'null' => false, 'default' => null],
            'status' => ['type' => 'string', 'null' => false, 'default' => null],
            'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'column' => 'id'],
        ],
        'indexes' => [
            'mailbox_message_id_checksum_idx' => ['type' => 'unique', 'column' => ['message_id', 'checksum']],
        ],
        'options' => ['engine' => 'InnoDB', 'autoIncrement' => 1000],
    ];

    protected $imap = [
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
}
