<?php
declare(strict_types = 1);
namespace App\Test\TestCase\Service\Lxd;

use App\Lxd\Lxd;
use App\Lxd\LxdClient;
use Origin\TestSuite\OriginTestCase;
use App\Service\Lxd\LxdChangeNetworkSettings;

// TODO: this needs to be rewritten due to changes
class LxdChangeNetworkSettingsTest extends OriginTestCase
{
    private LxdClient $client;

    protected function startup()
    {
        $this->client = new LxdClient(Lxd::host());
        $this->client->operation->wait(
            $this->client->instance->stop('ubuntu-test')
        );
    }

    protected function shutdown()
    {
        $this->client->instance->start('ubuntu-test');
    }
    /**
     * Test with two different IPs to make its being changed. Ideally Device Should be Stopped
     *
     * $ interface=$(ip route get 8.8.8.8 | awk -- '{printf $5}')
     * $ sudo nmcli con add ifname nuberbr1 type bridge con-name nuberbr1
     * $ sudo nmcli con add type bridge-slave ifname "$interface" master nuberbr1

     */
    public function testSetBridged()
    {
        /**
         * during test we created the network, this is because if the bridged network interface was setup properly
         * it would show up in network.
         */
        $result = (new LxdChangeNetworkSettings($this->client))->dispatch('ubuntu-test', 'nuber-bridged');

        //  Failed to start device "eth0": Failed to attach interface: veth60457db8 to eth0: container is not running: "ubuntu-test"
        $this->assertTrue($result->success());
    }

    public function testSetNat()
    {
        // if this fails it will return false
        $result = (new LxdChangeNetworkSettings($this->client))->dispatch('ubuntu-test', 'vnet0');

        $this->assertTrue($result->success());
    }
}
