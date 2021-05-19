<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Log;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class LogTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testList()
    {
        $list = (new Log())->list(static::$instance);
        $this->assertIsArray($list);
        $this->assertNotEmpty($list);

        return $list[0];
    }

    /**
     * @depends testList
     */
    public function testRead($log)
    {
        $contents = (new Log())->get(static::$instance, $log);
        $this->assertIsString($contents);
        $this->assertNotEmpty($contents);
        $this->assertStringContainsString('lxc.log.file', $contents);
    }
}
