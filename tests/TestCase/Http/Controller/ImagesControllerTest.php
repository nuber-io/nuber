<?php
namespace App\Test\TestCase\Http\Controller;

use App\Lxd\Lxd;
use Origin\Cache\Cache;

class ImagesControllerTest extends NuberTestCase
{
    public static function setUpBeforeClass(): void
    {
        $client = Lxd::client();
        $client->instance->copy('ubuntu-test', 'c1');
        sleep(3);

        // Remove this
        if (Cache::exists('remoteImages')) {
            Cache::delete('remoteImages');
        }
    }

    public static function tearDownAfterClass(): void
    {
        $client = Lxd::client();

        if (in_array('c1', $client->instance->list(['recursive' => 0]))) {
            $client->operation->wait($client->instance->delete('c1'));
        }
    }

    public function testIndex()
    {
        $this->login();
        $this->get('/images');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Images &nbsp; <small class="text-muted">demo1.lxd</small> </h2>');
    }

    public function testCreate()
    {
        $this->login();
        $this->get('/images/create');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Create from Instance</h2>');
        // This should not show up because it is running
        $this->assertResponseNotContains('<option value="ubuntu-test">ubuntu-test</option>');
    }

    public function testCreatePost()
    {
        $this->login();
        $this->post('/images/create', [
            'name' => 'image-create-test',
            'instance' => 'c1'
        ]);
        $this->assertRedirect('/images/index');
        $this->assertFlashMessage('Your Image has been created.');
    }

    public function testDelete()
    {
        $info = Lxd::client()->image->alias('image-create-test');

        $this->login();
        $this->delete('/images/delete/' . $info['target']);
        $this->assertResponseOk();
        $this->assertResponseContains('{"data":[]}');
    }

    public function testDeleteNotFound()
    {
        $this->login();
        $this->delete('/images/fake-fingerprint');
        $this->assertResponseNotFound();
    }

    public function testDownloadFromRemote()
    {
        $this->login();
        $this->get('/images/download');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Download Image</h2>');
        // Check that images have been loaded properly
        $this->assertResponseContains('"alpine\/3.11\/amd64\/default"');
        //alpine/3.10/amd64
    }

    public function testDownloadFromRemotePost()
    {
        $this->login();
        $this->post('/images/download', [
            'image' => 'alpine/3.10/amd64'
        ]);
        $this->assertRedirect('/images/index');
    }
}
