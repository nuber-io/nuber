<?php

namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Log;
use InvalidArgumentException;

use App\Lxd\Endpoint\Instance;

use App\Lxd\Endpoint\Exception\NotFoundException;
use Origin\HttpClient\Exception\ClientErrorException;

class InstanceTest extends EndpointTestCase
{
    public function testList()
    {
        $list = (new Instance())->list();
        $this->assertIsArray($list);
    }

    /**
     * Check getting from alias, and fingerprint
     *
     * @return void
     */
    public function testCreateInstanceInvalidName()
    {
        $this->expectException(InvalidArgumentException::class);
        (new Instance())->create('images:ubuntu/focal/amd64', 'should not have spaces');
    }

    public function testCreateInstanceInvalidRemoteSource()
    {
        // this is not reaching this
        $this->expectException(NotFoundException::class);
        (new Instance())->create('private:ubuntu/focal/amd64', 'this-is-fine');
    }

    public function testCreateInstanceImageNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new Instance())->create('my-container', 'some-image-that-does-not-exist');
    }

    public function testCreateInstanceLocalUnkown()
    {
        $this->expectException(ClientErrorException::class);
        (new Instance())->create('windows-3.11', 'instance-foo');
    }

    public function testCreateInstance()
    {
        # Create Operation
        $this->assertBackgroundOperationSuccess(
            (new Instance())->create('ubuntu', 'instance-test', [
                'config' => ['limits.cpu' => '2', 'limits.memory' => '512MB']
            ])
        );
    }

    public function testListWithData()
    {
        $response = (new Instance())->list(['recursive' => 0]);

        $this->assertNotEmpty($response);
        $this->assertContains('instance-test', $response);
    }

    public function testInfo()
    {
        $response = (new Instance())->info('instance-test');
        $this->assertEquals('instance-test', $response['name']);
    }

    public function testInfoNotFound()
    {
        $this->expectException(NotFoundException::class);
        $response = (new Instance())->info('instance-100');
    }

    public function testStart()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->start('instance-test')
        );
        $this->assertSame('Running', (new Instance())->state('instance-test')['status']);
    }

    // TODO: This has changed now
    public function testExecBackground()
    {
        $response = (new Instance)->exec('instance-test', 'ls -lah', ['record-output' => true]);

        $operationResponse = $this->waitForOperationToComplete($response['id']);
        $this->assertOperationResponseSuccess($operationResponse);

        $output = (new Log())->get('instance-test', $operationResponse['metadata']['output'][1]);
        $this->assertStringContainsString('-rw-r--r--  1 root root', $output);
    }
    
    public function testStartFailure()
    {
        $uuid = (new Instance())->start('instance-test');
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseError($operationResponse);
        $this->assertStringContainsString('The container is already running', $operationResponse['err']);
    }

    public function testRestart()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->restart('instance-test')
        );
    }

    public function testFreeze()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->freeze('instance-test')
        );
        $this->assertSame('Frozen', (new Instance())->state('instance-test')['status']);
    }

    public function testUnfreeze()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->unfreeze('instance-test')
        );
        $this->assertSame('Running', (new Instance())->state('instance-test')['status']);
    }

    public function testStop()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->stop('instance-test')
        );
        $this->assertSame('Stopped', (new Instance())->state('instance-test')['status']);
    }

    public function testCopy()
    {
        $instance = new Instance();
     
        $this->assertBackgroundOperationSuccess(
            $instance->copy('instance-test', 'instance-test-2')
        );

        $this->assertContains('instance-test-2', $instance->list(['recursive' => 0]));
        
        $this->assertBackgroundOperationSuccess(
            $instance->delete('instance-test-2')
        );
    }

    public function testRename()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->rename('instance-test', 'instance-test-renamed')
        );
    }

    public function testDeleteInstance()
    {
        $this->assertBackgroundOperationSuccess(
            (new Instance())->delete('instance-test-renamed')
        );
    }

    public function testDeleteInstanceNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new Instance())->delete('instance-1000');
    }
}
