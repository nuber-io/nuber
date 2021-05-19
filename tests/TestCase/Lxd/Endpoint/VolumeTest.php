<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Host;
use App\Lxd\Endpoint\Device;
use App\Lxd\Endpoint\Volume;
use App\Lxd\Endpoint\Exception\NotFoundException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class VolumeTest extends EndpointTestCase
{
    use EndpointTestTrait;
    
    public function testCreate()
    {
        $driver = (new Host())->info()['environment']['storage']; // work on different nodes

        $response = (new Volume())->create('volume-test', ['size' => '1GB','driver' => $driver]);
        $this->assertNull($response);
    }
    
    /**
     * @depends testCreate
     */
    public function testList()
    {
        $list = (new Volume())->list(['recursive' => 0]);
        $this->assertNotEmpty($list);
        $this->assertIsArray($list);
        $this->assertContains('volume-test', $list);
    }

    /**
     * @depends testCreate
     */
    public function testGet()
    {
        $info = (new Volume())->get('volume-test');
        $this->assertIsArray($info);
        $this->assertEquals('volume-test', $info['name']);
    }
    /**
     * @depends testCreate
     */
    public function testAttach()
    {
        $this->assertBackgroundOperationSuccess(
            (new Volume())->attach('volume-test', static::$instance, 'bsv1', '/mnt/block-storage')
        );

        $device = (new Device())->get(static::$instance, 'bsv1');
        $this->assertIsArray($device);
    }

    public function testAttachUnkownVolume()
    {
        $this->expectException(NotFoundException::class);
        (new Volume())->attach('a-volume-that-does-not-exist', static::$instance, 'bsv1', '/mnt/block-storage');
    }
    /**
     * @depends testCreate
     */
    public function testDetach()
    {
        $this->assertBackgroundOperationSuccess(
            (new Volume())->detach(static::$instance, 'bsv1')
        );
        $this->expectException(NotFoundException::class);
        (new Device())->get(static::$instance, 'bsv1');
    }

    public function testDetachNonAttached()
    {
        $this->expectException(NotFoundException::class);
        (new Volume())->detach(static::$instance, 'bsv1');
    }

    /**
     * @depends testCreate
     */
    public function testRename()
    {
        $response = (new Volume())->rename('volume-test', 'test-volume-renamed');
        $this->AssertNull($response);
    }

    /**
     * @depends testRename
     */
    public function testDelete()
    {
        $response = (new Volume())->delete('test-volume-renamed');
        $this->assertNull($response);
    }
}
