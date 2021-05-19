<?php
namespace App\Test\Fixture;

use Origin\TestSuite\Fixture;

class UserFixture extends Fixture
{
    protected $records = [
        [
            'id' => 1000,
            'first_name' => 'James',
            'last_name' => 'Brown',
            'email' => 'james@nuber.io',
            'password' => '$2y$10$nCMxYLvcvbXFnsBDFP5WpOky3bz.EDgo54VR0Tg9cpave3ZETT/di', // 123456
            'token' => '3905604a-b14d-4fe8-906e-7867b39289b3',
            'description' => null,
            'verified' => '2021-01-19 12:00:00',
            'created' => '2021-01-19 12:00:00',
            'modified' => '2021-01-19 12:00:00'
        ]
    ];
}
