<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Storage;
use App\Lxd\Endpoint\Exception\NotFoundException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use Origin\HttpClient\Exception\ServerErrorException;

class StorageTest extends EndpointTestCase
{
    public function testList()
    {
        $list = (new Storage())->list(['recursive' => 0]);
        $this->assertContains('default', $list);
        $this->assertNotContains('test', $list);
    }

    public function testCreate()
    {
        $result = (new Storage())->create('test', ['size' => '500MB']);
        $this->assertNull($result);
    }

    /**
     * Some tests might be node specific, such as size.
     */
    public function testCreateFail()
    {
        $this->expectException(ServerErrorException::class);
        (new Storage())->create('test', ['size' => '500MB']);
    }

    public function testInfo()
    {
        $info = (new Storage())->get('test');
        $this->assertIsArray($info);
        $this->assertNotEmpty($info);
        $this->assertEquals('test', $info['name']);
    }

    /**
     * @depends testCreate
     *
     * @return void
     */
    public function testDelete()
    {
        (new Storage())->delete('test');
        $this->assertNull(null);
    }

    /**
     * @depends testCreate
     *
     * @return void
     */
    public function testDeleteNonExistant()
    {
        $this->expectException(NotFoundException::class);
        (new Storage())->delete('test');
    }
}
