<?php
namespace App\Test\TestCase\Service\Lxd;

use App\Lxd\Lxd;
use App\Lxd\LxdClient;
use App\Lxd\Endpoint\Instance;
use Origin\Validation\Validation;
use Origin\TestSuite\OriginTestCase;
use App\Service\Lxd\LxdCreateInstance;

/**
 * Test if a final IP address is set
 */
class LxdCreateInstanceTest extends OriginTestCase
{
    private LxdClient $client;

    protected function startup()
    {
        $this->client = new LxdClient(Lxd::host());
    }

    public function testExecute()
    {
        $this->deleteIfExists('instance-create-test');
  
        $fingerprint = $this->client->alias->get('ubuntu')['target'];
        
        $result = (new LxdCreateInstance($this->client))->dispatch('instance-create-test', $fingerprint, '1GB', '1GB', (string) 1);
        
        $this->assertTrue($result->success());
        $this->assertTrue(Validation::ip($result->data('ip_address')));
        
        //$info = (new Instance())->info('instance-create-test');
    }

    protected function deleteIfExists(string $name)
    {
        if (in_array($name, $this->client->instance->list(['recursive' => 0]))) {
            $this->client->operation->wait($this->client->instance->stop($name));
            $this->client->operation->wait($this->client->instance->delete($name));
        }
    }
}
