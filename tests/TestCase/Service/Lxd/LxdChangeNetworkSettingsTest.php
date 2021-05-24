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
        $this->client->instance->stop('ubuntu-test');
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
     *
     * TODO: this needs to be rewritten
     */
    public function testSetBridged()
    {
        $ip = '10.247.51.100';
        $result = (new LxdChangeNetworkSettings($this->client))->dispatch('ubuntu-test', 'nuber-bridged', $ip);
        $this->assertTrue($result->success());
        $this->assertEquals(['nuber-default','nuber-bridged'], $result->data('info')['profiles']);

        $info = $this->client->instance->info('ubuntu-test');
        $this->assertEquals($ip, $info['expanded_devices']['eth0']['ipv4.address']);
    }

    public function testSetNat()
    {
        $ip = '10.247.51.101';
        $result = (new LxdChangeNetworkSettings($this->client))->dispatch('ubuntu-test', 'nuber-nat', $ip);
        $this->assertTrue($result->success());

        $info = $this->client->instance->info('ubuntu-test');
        $this->assertEquals($ip, $info['expanded_devices']['eth0']['ipv4.address']);
        $this->assertEquals(['nuber-default','nuber-nat'], $result->data('info')['profiles']);
    }
}
