<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Backup;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestTrait;

class BackupTest extends EndpointTestCase
{
    use EndpointTestTrait;

    public function testCreate()
    {
        /**
         * If test is run indiviual this fine, when run in group its an isssue
         */
        $this->assertBackgroundOperationSuccess(
            (new Backup())->create(self::$instance, 'backup-test'),
            60
        );
    }

    public function testList()
    {
        $list = (new Backup())->list(self::$instance, ['recursive' => 0]);
        $this->assertContains('backup-test', $list);
    }

    public function testExport()
    {
        $file = (new Backup())->export(self::$instance, 'backup-test');
        $this->assertFileExists($file);
    }

    public function testRename()
    {
        $result = (new Backup())->rename(self::$instance, 'backup-test', 'backup-test-renamed');
        $this->assertNull($result);
    }

    public function testGet()
    {
        $backup = (new Backup())->get(self::$instance, 'backup-test-renamed');
        $this->assertIsArray($backup);
        $this->assertArrayHasKey('name', $backup);
        $this->assertEquals('backup-test-renamed', $backup['name']);
    }

    public function testDelete()
    {
        $this->assertBackgroundOperationSuccess(
            (new Backup())->delete(self::$instance, 'backup-test-renamed')
        );
    }
}
