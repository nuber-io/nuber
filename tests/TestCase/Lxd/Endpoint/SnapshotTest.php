<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\File;
use App\Lxd\Endpoint\Snapshot;
use App\Lxd\Endpoint\Exception\NotFoundException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;

use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class SnapshotTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testListBefore()
    {
        $list = (new Snapshot())->list(static::$instance);
        $this->assertEmpty($list);
    }

    public function testCreate()
    {
        $this->assertBackgroundOperationSuccess(
            (new Snapshot())->create(static::$instance, static::$instance .'-' . time())
        );
    }

    public function testListAfter()
    {
        $list = (new Snapshot())->list(static::$instance, ['recursive' => 0]);
        $this->assertNotEmpty($list);

        return $list[0];
    }

    /**
     * @depends testListAfter
     */
    public function testRestore($snapshot)
    {
        // Add a dummy file
        $filename = uid();
        $file = new File();
        $file->put(static::$instance, ROOT . '/README.md', '/home/ubuntu/' . $filename);
        $this->assertContains($filename, $file->list(static::$instance, '/home/ubuntu/'));

        $this->assertBackgroundOperationSuccess(
            (new Snapshot())->restore(static::$instance, $snapshot)
        );

        $this->assertNotContains($filename, $file->list(static::$instance, '/home/ubuntu/'));
    }

    /**
     * @depends testListAfter
     */
    public function testDelete($name)
    {
        $this->assertBackgroundOperationSuccess(
            (new Snapshot())->delete(static::$instance, $name)
        );
    }

    public function testDeleteNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new Snapshot())->delete(static::$instance, 'foo');
    }

    public function testDeleted()
    {
        $response = (new Snapshot())->list(static::$instance);
        $this->assertEmpty($response);
    }
}
