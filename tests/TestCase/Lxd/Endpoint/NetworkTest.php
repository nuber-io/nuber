<?php

namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Network;
use InvalidArgumentException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class NetworkTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testList()
    {
        $list = (new Network())->list(['recursive' => 0]);
        $this->assertIsArray($list);
        $this->assertContains('eth0', $list);
    }

    public function testGet()
    {
        $network = (new Network())->get('eth0');
        $this->assertIsArray($network);
        $this->assertArrayHasKey('type', $network);
        $this->AssertEquals('physical', $network['type']);
    }

    public function testCreateInvalidName()
    {
        $this->expectException(InvalidArgumentException::class);
        (new Network())->create('my-network-to-long', [
            'description' => 'my network',
        ]);
    }

    public function testCreate()
    {
        $result = (new Network())->create('my-network', [
            'description' => 'my network',
            'config' => [
                'ipv4.address' => 'none',
                'ipv6.address' => '2001:470:b368:4242::1/64',
                'ipv6.nat' => 'true'
            ]
        ]);
        $this->assertNull($result);
        $this->assertContains('my-network', (new  Network())->list(['recursive' => 0]));
    }

    public function testEdit()
    {
        $result = (new Network())->edit('my-network', [
            'description' => 'This is my network'
        ]);
        $network = (new Network())->get('my-network');
        $this->assertEquals('This is my network', $network['description']);
    }

    public function testRenameTooLong()
    {
        $this->expectException(InvalidArgumentException::class);
        (new Network())->rename('my-network', 'my-network-rename');
    }

    public function testRename()
    {
        $result = (new Network())->rename('my-network', 'my-network-2');
        $this->assertNull($result);
    }

    public function testDelete()
    {
        $result = (new Network())->delete('my-network-2');
        $this->assertNull($result);
    }
}
