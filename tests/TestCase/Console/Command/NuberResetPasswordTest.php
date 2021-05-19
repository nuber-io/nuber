<?php
namespace App\Test\TestCase\Console\Command;

use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class NuberResetPasswordTest extends OriginTestCase
{
    protected $fixtures = [
        'AutomatedBackup', 'Host','User','Queue'
    ];

    use ConsoleIntegrationTestTrait;

    public function testChangePassword()
    {
        $this->exec('nuber:reset-password', ['james@nuber.io','secret']);
        $this->assertExitSuccess();
        $this->assertOutputContains('Password has been changed');
    }

    public function testUserDoesNotExist()
    {
        $this->exec('nuber:reset-password', ['tony@nuber.io']);
        $this->assertExitSuccess();
        $this->assertErrorContains('User does not exist');
    }
}
