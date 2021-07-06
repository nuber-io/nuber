<?php
namespace App\Test\TestCase\Http\Controller;

use App\Lxd\Lxd;
use App\Lxd\LxdClient;
use App\Service\Lxd\LxdCreateSnapshotBackup;

class InstancesControllerTest extends NuberTestCase
{
    const INSTANCES = 13;

    public static function setUpBeforeClass(): void
    {
        // Create some containers
        $client = Lxd::client();

        for ($i = 1;$i < self::INSTANCES;$i++) {
            $client->instance->copy('ubuntu-test', 'c' . $i);
        }

        $client->volume->create('v1');
        $client->volume->create('v2');

        /**
         * Getting all kinds of errors during testing
         * - Failed to run: zfs mount lxdpool/containers/c11: cannot mount 'lxdpool/containers/c11': no mountpoint set
         * - ZFS dataset is busy
         *
         * Don't think this helps
         */
        sleep(3);
    }

    /**
     * @internal the Throttle Middleware should be disabled if you are going to be testing since it will block requests
     * without IP addresses.
     */
    public function testIndex()
    {
        $this->login();
    
        $this->get('/instances');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2> Instances &nbsp; <small class="text-muted">demo1.lxd</small> </h2>');
    }

    public function testIndexWizard()
    {
        $this->login();

        $this->get('/instances/wizard');
        $this->assertResponseOk();
        $this->assertResponseContains('Focal Fossa 20.04');
    }

    public function testNewInstance()
    {
        $this->login();

        $this->get('/instances/create?image=ubuntu/focal/'  . ARCH);
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>New Instance</h2>');
    }

    /**
     * @internal if this tests fails, it could be because image is missing or for some reason there is zombine
     * instance still there.
     *
     * @return void
     */
    public function testNewInstancePost()
    {
        $this->login();

        // Test test will fail if future failures leave the instance there

        /**
         *  [name] => ubuntu
         *  [memory] => 1GB
         *  [disk] => 5GB
         *  [cpu] => 1
         *  [image] => ubuntu/focal/arm64
         */

        $this->post('/instances/create?image=ubuntu/focal/'  . ARCH, [
            'name' => 'create-test',
            'memory' => '1GB',
            'disk' => '5GB', // TODO: increased from 1GB due to issue with BTRFS which is being checked out
            'cpu' => '1',
            'image' => 'ubuntu/focal/'  . ARCH,
            'eth0' => 'vnet0',
            'type' => 'container'
        ]);
      
        // if you get different url this means its download the image first.
        $this->assertRedirect(['controller' => 'Instances','action' => 'index','?' => ['create' => 'create-test']]);
        $this->assertSessionHasKey('instanceCreate');

        return $this->controller->request()->session()->toArray();
    }

    /**
     * @depends testNewInstancePost
     */
    public function testNewInstanceCreating(array $session)
    {
        $this->session($session);

        $this->post('/instances/init/create-test');
        
        $this->assertResponseOk();
        $this->assertResponseRegExp('/{"data":{"name":"create-test","ip_address":"10.0.0.[\d]+"}}/');
    }

    /**
     * @depends testNewInstanceCreating
     */
    public function testCreated()
    {
        $this->login();

        $this->get('/instances');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2> Instances &nbsp; <small class="text-muted">demo1.lxd</small> </h2>');
        $instances = $this->viewVariable('instances');

        $this->assertContains('create-test', collection($instances)->extract('name')->toList());
    }

    /**
     * @return void
     */
    public function testNewInstanceUsingFingerPrintPost()
    {
        $this->login();

        $images = json_decode(file_get_contents(config_path('images.json')), true);

        $key = array_search('ubuntu/focal/' . ARCH . '/default', array_column($images, 'alias'));
        $this->assertNotFalse($key);

        $fingerprint = $images[$key]['containerFingerprint'];

        $this->post('/instances/create?image=Ubuntu+focal&type=container&store=yes&fingerprint='  . $fingerprint, [
            'name' => 'create-test2',
            'memory' => '1GB',
            'disk' => '5GB',
            'cpu' => '1',
            'image' => $fingerprint, // this test uses the fingerprint for
            'eth0' => 'vnet0',
            'type' => 'container'
        ]);
      
        // if you get different url this means its download the image first.
        $this->assertRedirect(['controller' => 'Instances','action' => 'index','?' => ['create' => 'create-test2']]);
        $this->assertSessionHasKey('instanceCreate');

        return $this->controller->request()->session()->toArray();
    }

    /**
     * @depends testNewInstanceUsingFingerPrintPost
     */
    public function testNewInstanceCreatingFromFingerprint(array $session)
    {
        $this->session($session);

        $this->post('/instances/init/create-test2');
    
        $this->assertResponseOk();
        $this->assertResponseRegExp('/{"data":{"name":"create-test2","ip_address":"10.0.0.[\d]+"}}/');
    }

    public function testStart()
    {
        $this->login();
        $this->post('/instances/start/c1');
        $this->assertResponseOk();
    }

    public function testStop()
    {
        $this->login();
        $this->post('/instances/stop/c1');
        $this->assertResponseOk();
    }

    public function testRestart()
    {
        $this->login();
        $this->post('/instances/restart/ubuntu-test');
        $this->assertResponseOk();
    }

    public function testRow()
    {
        $this->login();

        $this->get('/instances/row/c1');
        $this->assertResponseOk();
        $this->assertResponseContains('<a href="/instances/details/c1">c1</a>');
    }

    public function testConsole()
    {
        $this->login();
        $this->get('/instances/console/ubuntu-test');
        $this->assertResponseOk();
    }

    public function testDestroyPost()
    {
        $this->login();
        $this->post('/instances/destroy/c2');
        $this->assertRedirect('/instances');
        $this->assertFlashMessage('The instance was destroyed.');
    }

    public function testNotFound()
    {
        $this->login();

        $this->get('/instances/details/c2');
        $this->assertResponseNotFound();
    }

    public function testClone()
    {
        $this->login();
        $this->get('/instances/clone/ubuntu-test');
        $this->assertResponseOk();
        $this->assertResponseContains('Clone ubuntu-test');
    }

    public function testClonePost()
    {
        $this->login();
        $this->post('/instances/clone/ubuntu-test', [
            'name' => 'clone-test'
        ]);
        $this->assertRedirect('/instances/details/clone-test');
        $this->assertFlashMessage('The instance was cloned.');
    }

    public function testRename()
    {
        $this->login();
        $this->get('/instances/rename/c3');
        $this->assertResponseOk();
    }

    public function testRenamePost()
    {
        $this->login();
        $this->post('/instances/rename/c3', [
            'name' => 'rename-test'
        ]);

        $this->assertRedirect('/instances/rename/rename-test');
        $this->assertResponseNotContains('An error occurred.');
    }

    /**
     * @return void
     */
    public function testResize()
    {
        $this->login();
        $this->get('/instances/resize/ubuntu-test');
        $this->assertResponseOk();

        // Checks things are disabled
        $this->assertResponseContains('<button type="submit" class="btn btn-primary" disabled>Resize</button>');
        $this->assertResponseContains('<button type="submit" class="btn btn-primary" disabled>Resize Disk Space</button>');
    }
    /**
     * @todo work on virtual machine tests, setup test servers on dedicated server.
     */

    public function testResizeMemPost()
    {
        $this->login();
        $this->post('/instances/resize/c4', [
            'memory' => '2GB',
            'cpu' => '2',
            'form' => 'memory'
        ]);
        $this->assertRedirect('/instances/resize/c4');
        $this->assertFlashMessage('The instance has been resized.');
    }

    public function testResizeDiskPost()
    {
        $this->login();
        $this->post('/instances/resize/c4', [
            'form' => 'disk',
            'disk' => '7GB'
            
        ]);
        $this->assertRedirect('/instances/resize/c4');
        $this->assertFlashMessage('The instance has been resized.');
    }

    public function testPorts()
    {
        $this->login();
        $this->get('/instances/ports/c5');
        $this->assertResponseOk();
    }

    public function testPortsPost()
    {
        $this->login();
        $this->post('/instances/ports/c5', [
            'listen' => '1234',
            'connect' => '5678',
            'protocol' => 'tcp'
        ]);
        $this->assertRedirect('/instances/ports/c5');
        $this->assertFlashMessage('TCP traffic from port 1234 will be forwarded to port 5678.');
    }

    public function testPortsPostError()
    {
        $this->login();
        $this->post('/instances/ports/c5', [
            'listen' => '1234',
            'connect' => '5678',
            'protocol' => 'tcp'
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Port is already configured');
        $this->assertResponseContains('Traffic fowarding could not be setup.');
    }

    public function testNetworking()
    {
        $this->login();
        $this->get('/instances/networking/c6');
        $this->assertResponseOk();
    }

    public function testNetworkingIpAddressPost()
    {
        $this->login();
        $this->post('/instances/ipSettings/c7', [
            'ip4_address' => '10.0.0.254'
        ]);

        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('The IP address was set.');
    }

    public function testNetworkingIpAddressPostEmpty()
    {
        $this->login();
        $this->post('/instances/ipSettings/c7', [
            'ip4_address' => ''
        ]);

        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('The IP address was removed.');
    }

    public function testNetworkingIpAddressPostInvalid()
    {
        $this->login();
        $this->post('/instances/ipSettings/c7', [
            'ip4_address' => 'a-b-c-d'
        ]);

        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('The IP address settings could not be changed.');
    }

    public function testNetworkingNetworkSettingsPostInvalid()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'nuber-nat',
            'mac0' => null,
            'eth1' => null,
            'mac1' => null
        ]);
        //
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Invalid Network Settings.');
    }

    public function testNetworkingNetworkSettingsPostInvalidEth1()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'vnet0',
            'mac0' => null,
            'eth1' => 'dont-exist',
            'mac1' => null
        ]);
        //
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Invalid Network Settings.');
    }

    public function testNetworkingNetworkSettingsPost()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'vnet0',
            'eth1' => null,
        ]);
        //Invalid Network Settings.
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Networking settings have been updated.');
    }

    public function testNetworkingMacAddress0Post()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'vnet0',
            'mac0' => '00:16:3e:dd:17:16',
            'eth1' => null,
        ]);
        //Invalid Network Settings.
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Networking settings have been updated.');
    }

    public function testNetworkingMacAddress1Post()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'vnet0',
            'mac0' => null,
            'eth1' => 'nuber-macvlan',
            'mac1' => '00:16:3e:dd:12:12'
        ]);
        //Invalid Network Settings.
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Networking settings have been updated.');
    }

    public function testNetworkingMacAddress0PostInvalidMacAddress()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'vnet0',
            'mac0' => 'abc',
            'eth1' => null,
        ]);
        //Invalid Network Settings.
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Invalid Network Settings.');
    }

    public function testNetworkingMacAddress1PostInvalidMacAddress()
    {
        $this->login();
        $this->post('/instances/networkSettings/c7', [
            'eth0' => 'vnet0',
            'mac0' => '00:16:3e:dd:17:16',
            'eth1' => 'nuber-macvlan',
            'mac1' => 'abcd'
        ]);
        //Invalid Network Settings.
        $this->assertRedirect('/instances/networking/c7');
        $this->assertFlashMessage('Invalid Network Settings.');
    }

    public function testVolumes()
    {
        $this->login();
        $this->get('/instances/volumes/c8');
        $this->assertResponseOk();
        $this->assertResponseContains('<option value="v1">v1</option>');
    }

    public function testVolumesPost()
    {
        $this->login();
        $this->post('/instances/volumes/c8', [
            'path' => '/mnt/test',
            'name' => 'v1'
        ]);
        $this->assertRedirect();
        $this->assertFlashMessage('The volume v1 was attached.');
    }

    public function testVolumesDetach()
    {
        $this->login();
        $this->post('/volumes/detach/c8/bsv0');
        $this->assertResponseOk();
        $this->assertFlashMessage('The volume was detached.');
    }

    public function testSnapshots()
    {
        $this->login();
        $this->get('/instances/snapshots/c9');
        $this->assertResponseOk();
    }

    public function testSnapshotsPost()
    {
        $this->login();
        $this->post('/instances/snapshots/c9', [
            'name' => 'snapshot-test'
        ]);
          
        $this->assertRedirect();
        $this->assertFlashMessage('The snapshot has been created.');
    }

    public function testSnapshotsRestore()
    {
        $this->login();
        $this->post('/snapshots/restore/c9/snapshot-test');

        $this->assertResponseOk();
        $this->assertFlashMessage('Your instance has been restored.');
    }

    public function testSnapshotsDelete()
    {
        $this->login();
        $this->delete('/snapshots/delete/c9/snapshot-test');

        $this->assertResponseOk();
        $this->assertFlashMessage('The snapshot was deleted.');
    }

    public function testBackups()
    {
        $this->login();
        $this->get('/instances/backups/c10');
        $this->assertResponseOk();
    }

    public function testBackupsPost()
    {
        $this->login();
        $this->post('/instances/backups/c10', [
            'frequency' => 'monthly',
            'retain' => '12'
        ]);
     
        $this->assertFlashMessage('Your backup schedule has been created.');
    }

    public function testBackupsDeleteScheduled()
    {
        $this->login();

        $this->delete('/automated_backups/delete/1000');

        $this->assertResponseOk();
        $this->assertFlashMessage('The scheduled backup was deleted.');
    }

    public function testBackupsRestore()
    {
        // Create a backup to work with
        $result = (new LxdCreateSnapshotBackup(Lxd::client()))->dispatch(
            'c10',
            'hourly',
            1
        );
     
        $this->login();
        $this->post('/snapshots/restore/c10/' . $result->data('name')) ;

        $this->assertResponseOk();
        $this->assertFlashMessage('Your instance has been restored.');

        return $result->data('name');
    }

    /**
     * @depends testBackupsRestore
     */
    public function testBackupsDelete(string $name)
    {
        $this->login();
        $this->delete('/snapshots/delete/c10/' . $name);

        $this->assertResponseOk();
        $this->assertFlashMessage('The backup was deleted.');
    }

    public function testMigrate()
    {
        $this->login();
        $this->get('/instances/migrate/c11');
        $this->assertResponseOk();
    }

    /**
     * This test will fail if something went wrong and the instance already
     * exists on the second host
     */
    public function testMigrateCopy()
    {
        $this->login();
        $this->post('/instances/migrate/c11', [
            'host' => env('LXD_HOST_2'),
            'clone' => 1
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('{"data":[]}');

        // Check exists on remote host
        $client = new LxdClient(env('LXD_HOST_2'));
        $this->assertContains('c11', $client->instance->list(['recursive' => 0]));
    }
    
    /**
     * This should fail because there is already
     *
     * @return void
     */
    public function testMigrateFail()
    {
        $this->login();
        $this->post('/instances/migrate/c11', [
            'host' => env('LXD_HOST_2')
        ]);
        $this->assertResponseError();
        $this->assertResponseContains('{"error":{"message":"An instance already exists on the remote host with this name","code":400}}');
    }

    public function testMigrateMove()
    {
        //   $this->disableMiddleware();
        //   $this->disableErrorHandler();

        $this->login();

        $client = Lxd::client();
        $client->operation->wait(
            $client->instance->start('c12')
        );

        $this->post('/instances/migrate/c12', [
            'host' => env('LXD_HOST_2'),
            'clone' => 0
        ]);
        $this->assertResponseOk(); //
        $this->assertResponseContains('{"data":[]}');

        $client = new LxdClient(env('LXD_HOST_2'));
        $list = $client->instance->list(['recursive' => 0]);
        $this->assertContains('c12', $list);
        $client->operation->wait($client->instance->stop('c12'));
        $client->instance->delete('c12');
    }

    public static function tearDownAfterClass(): void
    {
        for ($i = 1;$i < self::INSTANCES;$i++) {
            static::deleteInstance('c' . $i);
        }
    
        static::deleteInstance('instance-test');
        static::deleteInstance('create-test');
        static::deleteInstance('create-test2');
        static::deleteInstance('clone-test');
        static::deleteInstance('rename-test');

        // Delete volumes
        $client = Lxd::client();
        $client->volume->delete('v1');
        $client->volume->delete('v2');

        // Clean up on host #2 from migrate test
        $client = new LxdClient(env('LXD_HOST_2'));
        if (in_array('c11', $client->instance->list(['recursive' => 0]))) {
            $client->instance->delete('c11');
        }
    }

    public static function deleteInstance(string $instance)
    {
        $client = Lxd::client();
        $instances = $client->instance->list(['recursive' => 0]);
        
        if (in_array($instance, $instances)) {
            $info = $client->instance->info($instance);

            if ($info['status'] === 'Running') {
                $client->operation->wait($client->instance->stop($instance));
            }
           
            $client->instance->delete($instance);
        }
    }
}
