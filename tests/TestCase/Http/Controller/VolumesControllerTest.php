<?php
namespace App\Test\TestCase\Http\Controller;

class VolumesControllerTest extends NuberTestCase
{
    public function testIndex()
    {
        $this->login();
        $this->get('/volumes');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Volumes&nbsp; <small class="text-muted">demo1.lxd</small> </h2>');
    }

    public function testCreate()
    {
        $this->login();
        $this->get('/volumes/create');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>New Volume</h2>');
    }

    public function testCreatePost()
    {
        $this->login();
        $this->post('/volumes/create', [
            'name' => 'test-volume',
            'size' => '5GB'
        ]);
        $this->assertRedirect('/volumes/index');
        $this->assertFlashMessage('The volume was created.');
    }

    public function testCreatePostValidationFail()
    {
        $this->login();
        $this->post('/volumes/create', [
            'name' => 'test-volume',
            'size' => '5GB'
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('The volume could not be created.');
    }

    public function testRename()
    {
        $this->login();
        $this->get('/volumes/rename/test-volume');
 
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Rename Volume</h2>');
    }

    public function testRenamePostValidationFail()
    {
        $this->login();
        $this->post('/volumes/rename/test-volume', [
            'name' => 'test-volume' // same name
        ]);
 
        $this->assertResponseOk();
        $this->assertResponseContains('Unable to rename the volume.');
    }

    public function testRenamePost()
    {
        $this->login();
        $this->post('/volumes/rename/test-volume', [
            'name' => 'volume-test' // same name
        ]);
 
        $this->assertRedirect('/volumes/index');
        $this->assertFlashMessage('The volume has been renamed.');
    }

    /**
     * @depends testRenamePost
     */
    public function testDelete()
    {
        $this->login();
        $this->delete('/volumes/delete/volume-test');
        $this->assertResponseOk(); //
        $this->assertResponseContains('{"data":[]}');
    }
}
