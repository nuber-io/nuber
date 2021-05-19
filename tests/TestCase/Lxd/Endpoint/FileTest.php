<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\File;
use App\Lxd\Endpoint\Exception\NotFoundException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class FileTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testList()
    {
        $list = (new File())->list(static::$instance, '/');
        $this->assertIsArray($list);
        $this->assertContains('etc', $list);
    }

    public function testListNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new File())->list(static::$instance, '/atlantis');
    }

    public function testPut()
    {
        $result = (new File())->put(static::$instance, ROOT . '/README.md', '/home/ubuntu/readme.md');
        $this->assertNull($result);
    }

    public function testPutFound()
    {
        $this->expectException(NotFoundException::class);
        (new File())->put(static::$instance, sys_get_temp_dir() . uid(), '/home/ubuntu/readme.md');
    }

    public function testPull()
    {
        $expected = file_get_contents(ROOT . '/README.md');
        $actual = (new File())->pull(static::$instance, '/home/ubuntu/readme.md');
        $this->assertSame($expected, $actual);
    }

    public function testPullFound()
    {
        $this->expectException(NotFoundException::class);
        (new File())->pull(static::$instance, '/atlantis/passwords.txt');
    }
}
