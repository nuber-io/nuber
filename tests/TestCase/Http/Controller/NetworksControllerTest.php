<?php
namespace App\Test\TestCase\Http\Controller;

class NetworksControllerTest extends NuberTestCase
{
    public function testIndex()
    {
        $this->login();
        $this->get('/networks');

        $this->assertResponseOk();
        $this->assertResponseContains('<h2> Networks &nbsp; <small class="text-muted">demo1.lxd</small> </h2>');
        $this->assertResponseRegExp('/<td>vnet0<\/td> <td>\d<\/td> <td>10.0.0.1\/24<\/td> <td>fd00:0000:0000:0000::1\/48<\/td>/');
    }
    
    public function testCreate()
    {
        $this->login();
        $this->get('/networks/create');

        $this->assertResponseOk();
        $this->assertResponseContains('<h2>New Network</h2>');
    }

    public function testCreatePostEmpty()
    {
        $this->login();
        $this->post('/networks/create', [
            'name' => 'tnet0',
            'ipv4_address' => '',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Both IPv4 or IPv6 cannot be empty.');
    }

    public function testCreatePostDuplicate()
    {
        $this->login();
        $this->post('/networks/create', [
            'name' => 'vnet0',
            'ipv4_address' => '10.0.1.1',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('The network could not be created.');
    }

    public function testCreateIpv4Post()
    {
        $this->login();
        $this->post('/networks/create', [
            'name' => 'tnet0',
            'ipv4_address' => '10.255.255.1',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertRedirect('/networks/index');
        $this->assertFlashMessage('The network was created.');
    }

    public function testEdit()
    {
        // networks/edit/nuberbr0
        $this->login();
        $this->get('/networks/edit/vnet0');

        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Edit Network</h2>');
    }

    public function testEditPostEmpty()
    {
        $this->login();
        $this->post('/networks/edit/tnet0', [
            'name' => 'tnet0',
            'ipv4_address' => '',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Both IPv4 or IPv6 cannot be empty.');
    }

    public function testEditPostDuplicate()
    {
        $this->login();
        $this->post('/networks/edit/tnet0', [
            'name' => 'vnet0',
            'ipv4_address' => '10.0.1.1',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Unable to update the network settings.');
    }

    public function testEditPost()
    {
        $this->login();
        $this->post('/networks/edit/tnet0', [
            'name' => 'tnet0',
            'ipv4_address' => '10.0.1.1',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertRedirect('/networks/index');
        $this->assertFlashMessage('The network settings have been updated.');
    }

    public function testEditPostNetworkInUse()
    {
        $this->login();
        $this->post('/networks/edit/vnet0', [
            'name' => 'my-vnet',
            'ipv4_address' => '10.0.0.1',
            'ipv4_size' => '24',

            'ipv6_address' => '',
            'ipv6_size' => '64',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('This network is being used and cannot be renamed.');
    }

    public function testDelete()
    {
        $this->login();
        $this->delete('/networks/delete/tnet0');
        $this->assertResponseContains('{"data":[]}');
    }
}
