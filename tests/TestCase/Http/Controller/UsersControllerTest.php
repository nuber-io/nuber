<?php
namespace App\Test\TestCase\Http\Controller;

use Origin\Model\Entity;

/**
 * # IMPORTANT
 * For integration testing to work you need to load the AuthComponent in the AppController and for the Plugin to be loaded
 */
/**
 * @property \App\Model\User $User
 */
class UsersControllerTest extends NuberTestCase
{
    public function startup(): void
    {
        $this->loadModel('User');
    }

    public function testLogin()
    {
        $this->get('/login');
        $this->assertResponseOk();
        $this->assertResponseContains('<p>Login to continue</p>');
    }

    public function testLoginPost()
    {
        $this->post('/login', [
            'email' => 'james@nuber.io',
            'password' => 'Secret123456'
        ]);
        $this->assertRedirect(); // depends on config
    }

    public function testChangePassword()
    {
        $this->login();
        $this->get('/change-password');
        $this->assertResponseOk();
        $this->assertResponseContains('<input type="password" name="current_password" class="form-control" id="current-password">');
    }

    public function testChangePasswordPost()
    {
        $this->login();
        $this->post('/change-password', [
            'current_password' => 'Secret123456',
            'password' => 'Secret78',
            'password_confirm' => 'Secret78'
        ]);
 
        $this->assertRedirect('/change-password');
        $this->assertFlashMessage('Your password has been changed.');
        $this->assertFlashMessageNotSet('Unable to change your password.');
    }

    public function testChangePasswordPostError()
    {
        $this->login();

        $this->post('/change-password', [
            'current_password' => 'Secret123456',
            'password' => '1234567',
            'password_confirm' => '1234567'
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('Unable to change your password.');
    }

    public function testLogout()
    {
        $this->login();
        $this->get('/logout');
        $this->assertRedirect('/login');
    }

    public function testAssertProfile()
    {
        $this->login();
        $this->get('/profile');
        $this->assertResponseOk();
        $this->assertResponseContains('<h2>Profile</h2>');
        $this->assertInstanceOf(Entity::class, $this->viewVariable('user'));
    }

    public function testAssertProfilePost()
    {
        $this->login();
        $this->post('/profile', [
            'first_name' => 'foo',
            'last_name' => 'bar',
            'email' => 'james@nuber.io'
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Your profile has been updated.');
    }
}
