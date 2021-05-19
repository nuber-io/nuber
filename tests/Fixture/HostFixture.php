<?php
namespace App\Test\Fixture;

use Origin\TestSuite\Fixture;

class HostFixture extends Fixture
{
    protected $records = [
        [
            'id' => 1000,
            'name' => 'demo1.lxd',
            'address' => LXD_HOST,
            'created' => '2021-01-19 12:00:00',
            'modified' => '2021-01-19 12:00:00'
        ],
        [
            'id' => 1001,
            'name' => 'demo2.lxd',
            'address' => LXD_HOST_2,
            'created' => '2021-01-19 12:00:00',
            'modified' => '2021-01-19 12:00:00'
        ]
    ];
}
