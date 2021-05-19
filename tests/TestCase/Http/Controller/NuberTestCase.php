<?php
namespace App\Test\TestCase\Http\Controller;

use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\IntegrationTestTrait;

class NuberTestCase extends OriginTestCase
{
    use IntegrationTestTrait;

    protected $fixtures = [
        'AutomatedBackup', 'Host','User'
    ];

    /**
     * @var \App\Model\User;
     */
    protected $User;

    protected function startup()
    {
        $this->loadModel('AutomatedBackup');
        $this->loadModel('Host');
        $this->loadModel('User');
    }

    public function login()
    {
        $this->session([
            'Auth' => [
                'User' => $this->User->find('first')->toArray()
            ],
            'Lxd' => [
                'host' => env('LXD_HOST')
            ]
        ]);
    }
}
