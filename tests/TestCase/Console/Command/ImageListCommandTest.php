<?php
declare(strict_types = 1);
namespace App\Test\TestCase\Console\Command;

use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class ImageListCommandTest extends OriginTestCase
{
    use ConsoleIntegrationTestTrait;

    public function testExecute()
    {
        $this->exec('image:list');
        $this->assertExitSuccess();
        $this->assertOutputContains('Image list downloaded');

        $this->assertStringContains('ubuntu/hirsute/amd64/default', file_get_contents(config_path('images.json')));
    }
}
