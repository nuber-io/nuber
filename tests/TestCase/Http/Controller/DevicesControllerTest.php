<?php
namespace App\Test\TestCase\Http\Controller;

use App\Lxd\Lxd;

class DevicesControllerTest extends NuberTestCase
{
    public function testDelete()
    {
        
        // Add port
        Lxd::client()->device->add('ubuntu-test', 'test-1234', [
            'connect' => 'tcp:127.0.0.1:1234',
            'listen' => 'tcp:0.0.0.0:1234',
            'type' => 'proxy'
        ]);

        $this->login();

        $this->delete('/devices/delete/ubuntu-test/test-1234');

        $this->assertResponseOk();
        $this->assertFlashMessage('The port forwarding configuration has been deleted.');
    }
}
