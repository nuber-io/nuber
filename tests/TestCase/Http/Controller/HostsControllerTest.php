<?php
namespace App\Test\TestCase\Http\Controller;

class HostsControllerTest extends NuberTestCase
{
    public function testIndex()
    {
        $this->login();
        $this->get('/hosts/index');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Hosts</h2>');
        $this->assertResponseContains('<td>' . LXD_HOST . '</td>');
    }

    public function testAdd()
    {
        $this->login();
        $this->get('/hosts/add');
        $this->assertResponseOk();
    }

    public function testAddPost()
    {
        $host = $this->Host->get(1000);
        $this->Host->delete($host);

        $this->login();
        $this->post('/hosts/add', [
            'name' => $host->name,
            'address' => $host->address,
            'password' => '00000000-0000-0000-0000-000000000000'
        ]);
        $this->assertRedirect('/hosts/index');
        $this->assertFlashMessage('The host was added.');
    }

    public function testAddPostFailure()
    {
        $this->login();
        $this->post('/hosts/add', [
            'name' => '',
            'address' => ''
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('The host could not be saved.');
    }

    public function testEdit()
    {
        $this->login();
        $this->get('/hosts/edit/1000');
        $this->assertResponseOk();
    }

    public function testEditPost()
    {
        $this->login();
        $this->post('/hosts/edit/1000', [
            'name' => 'foo'
        ]);
        $this->assertRedirect('/hosts/index');
        $this->assertFlashMessage('The host has been updated.');
    }

    public function testEditPostFailure()
    {
        $this->login();
        $this->post('/hosts/edit/1000', [
            'name' => ''
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('The host could not be saved.');
    }

    public function testDelete()
    {
        $this->login();
        $this->post('/hosts/delete/1000');
        $this->assertRedirect('/hosts/index');
        $this->assertFlashMessage('The host was deleted.');
    }

    public function testDeleteOnlyOne()
    {
        $host = $this->Host->get(1000);
        $this->Host->delete($host);

        $this->login();
        $this->post('/hosts/delete/1001');
        $this->assertRedirect('/hosts/index');
        $this->assertFlashMessage('The host could not be deleted.');
    }

    public function testCertificate()
    {
        $this->login();
        $this->get('/hosts/certificate');
        $this->assertResponseOk();
        $this->assertFileSent(ROOT . '/config/certs/certificate');
    }

    public function testSwitch()
    {
        $this->login();
        $this->get('/hosts/switch?host=' . LXD_HOST_2);
        $this->assertRedirect('/instances/index');
        $this->assertSession('Lxd.host', LXD_HOST_2);
    }

    public function testSwitchNotFound()
    {
        $this->login();
        $this->get('/hosts/switch?host=1.2.3.4');
        $this->assertResponseNotFound();
    }
}
