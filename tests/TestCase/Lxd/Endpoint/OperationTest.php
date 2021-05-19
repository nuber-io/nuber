<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Snapshot;
use App\Lxd\Endpoint\Operation;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class OperationTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testList()
    {
        $list = (new Operation())->list();
        $this->assertIsArray($list);
    }

    public function testWaitAndList()
    {
        $operation = new Operation();
        $uuid = (new Snapshot())->create(static::$instance, 'snapshot-' . time());

        $list = $operation->list(['recursive' => 0]);
        $this->assertArrayHasKey('running', $list);
        $this->assertContains($uuid, $list['running']);

        $response = (new Operation())->wait($uuid);
        $this->assertEquals(200, $response['status_code']);

        $list = $operation->list(['recursive' => 0]);
        $this->assertArrayHasKey('success', $list);
        $this->assertContains($uuid, $list['success']);
    }

    public function testGet()
    {
        $uuid = (new Snapshot())->create(static::$instance, 'snapshot-' . time());

        $info = (new Operation())->get($uuid);
        $this->assertEquals('Snapshotting instance', $info['description']);
        
        return $uuid;
    }

    /**
     * @todo Each operation i have tried to cancel i get back its not cancelable. Revisit when
     * more features developed.
     * @depends testGet
     */
    public function testCancel($uuid)
    {
        $this->markTestIncomplete('Snapshots/instances are not cancelable');
        (new Operation())->delete($uuid);
    }
}
