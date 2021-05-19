<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Alias;
use App\Lxd\Endpoint\Image;
use App\Lxd\Endpoint\Exception\NotFoundException;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;
use Origin\HttpClient\Exception\ClientErrorException;

class AliasTest extends EndpointTestCase
{
    /**
     * Gets an image fingerprint
     *
     * @return string
     */
    private function getFingerprint(): string
    {
        $images = (new Image())->list(['recursive' => 0]);

        return array_pop($images);
    }

    public function testCreate()
    {
        $result = (new Alias())->create('alias-test', $this->getFingerprint(), 'Description for alias');
        $this->assertNull($result);
    }

    public function testList()
    {
        $list = (new Alias())->list(['recursive' => 0]);
        $this->assertIsArray($list);
        $this->assertContains('alias-test', $list);
    }

    public function testInfo()
    {
        $info = (new Alias())->get('alias-test');
        $this->assertSame('Description for alias', $info['description']);
        $this->assertSame($this->getFingerprint(), $info['target']);
    }

    /**
     * Alias 'alias-test' already exists
     *
     * @return void
     */
    public function testCreateFailureDuplicateName()
    {
        $this->expectException(ClientErrorException::class);
        (new Alias())->create('alias-test', '1234');
    }

    public function testCreateFailureUnkownFingerprint()
    {
        $this->expectException(NotFoundException::class);
        (new Alias())->create('alias-test-ni', '1234');
    }

    public function testRename()
    {
        $result = (new Alias())->rename('alias-test', 'alias-test-renamed');
        $this->assertNull($result);
    }

    public function testRenameFailure()
    {
        $this->expectException(NotFoundException::class);
        (new Alias())->rename('alias-test', 'does-not-matter');
    }

    public function testEdit()
    {
        $alias = new Alias();
        $result = $alias->edit('alias-test-renamed', [
            'description' => 'description changed',
            'target' => $this->getFingerprint()
        ]);

        $this->assertSame(
            'description changed',
            $alias->get('alias-test-renamed')['description']
        );
    }

    public function testEditNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new Alias)->edit('alias-does-not-exist', [
            'description' => 'a quick brown fox...'
        ]);
    }

    public function testDelete()
    {
        $result = (new Alias())->delete('alias-test-renamed');
        $this->assertNull($result);
    }
}
