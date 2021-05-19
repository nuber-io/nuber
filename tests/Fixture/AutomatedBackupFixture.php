<?php
declare(strict_types = 1);
namespace App\Test\Fixture;

use Origin\TestSuite\Fixture;

class AutomatedBackupFixture extends Fixture
{
    protected $records = [
        [
            'id' => 1000,
            'host_id' => 1000,
            'instance' => 'ubuntu-test',
            'frequency' => 'monthly',
            'at' => '00:00',
            'retain' => 12,
            'created' => '2021-03-30 14:25:07',
            'modified' => '2021-03-30 14:25:07'
        ]
    ];
}
