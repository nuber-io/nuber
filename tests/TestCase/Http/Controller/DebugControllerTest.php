<?php
namespace App\Test\TestCase\Http\Controller;

class DebugControllerTest extends NuberTestCase
{
    public function testIndex()
    {
        $this->login();
        $this->get('/debug');

        $this->assertResponseOk();
        $this->assertResponseContains('<h2>LXD API Log</h2>');
    }

    public function testDownload()
    {
        $this->login();
        $this->get('/debug/download');
        
        $this->assertFileSent(LOGS . '/lxd.log');
    }
}
