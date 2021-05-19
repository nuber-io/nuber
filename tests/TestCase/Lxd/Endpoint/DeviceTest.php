<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Device;
use App\Lxd\Endpoint\Instance;
use App\Lxd\Endpoint\Exception\NotFoundException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class DeviceTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testList()
    {
        $list = (new Device())->list(static::$instance);
        $this->assertEmpty($list);
    }

    public function testAdd()
    {
        $result = (new Device())->add(static::$instance, 'custom-port', [
            'connect' => 'tcp:127.0.0.1:1234',
            'listen' => 'tcp:0.0.0.0:1234',
            'type' => 'proxy'
        ]);
        $this->assertNull($result);
    }

    public function testGet()
    {
        $expected = [
            'connect' => 'tcp:127.0.0.1:1234',
            'listen' => 'tcp:0.0.0.0:1234',
            'type' => 'proxy'
        ];
        $actual = (new Device())->get(static::$instance, 'custom-port');
        $this->assertEquals($expected, $actual);
    }

    public function testSet()
    {
        $device = new Device();

        $device->set(static::$instance, 'custom-port', 'connect', 'tcp:127.0.0.1:123480');
        $info = $device->get(static::$instance, 'custom-port');
        $this->assertEquals('tcp:127.0.0.1:123480', $info['connect']);
    }

    public function testSetUnkown()
    {
        $this->expectException(NotFoundException::class);
        (new Device())->set(static::$instance, 'usb-storage', 'key', 'value');
    }

    public function testGetError()
    {
        $this->expectException(NotFoundException::class);
        (new Device())->get('instance-that-does-not-exist', 'foo');
    }

    public function testGetUnkown()
    {
        $this->expectException(NotFoundException::class);
        (new Device())->get(static::$instance, 'usb-storage');
    }

    public function testListNotEmpty()
    {
        $list = (new Device())->list(static::$instance, ['recursive' => 0]);
        $this->assertNotEmpty($list);
        $this->assertContains('custom-port', $list);
    }

    public function testAddError()
    {
        $this->expectException(NotFoundException::class);
        (new Device())->add('instance-that-does-not-exist', 'foo', [
            'key' => 'value'
        ]);
    }

    public function testRemove()
    {
        $device = new Device();
        
        $device->remove(static::$instance, 'custom-port');
        $this->assertNotContains('custom-port', $device->list(static::$instance));

        // test is failing when running in group of tests, unless i call this which has some delay perhaps?
        $null = $device->list(static::$instance);
    }

    public function testRemoveNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new Device())->remove(static::$instance, 'custom-port');
    }

    public function testSetExpandedDevices()
    {
        ( new Device())->set(static::$instance, 'eth0', 'name', 'eth0-renamed');

        // get expanded devices
        $info = (new Instance())->info(static::$instance);
        $this->assertSame('eth0-renamed', $info['expanded_devices']['eth0']['name']);
    }
}
