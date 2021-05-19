<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Image;
use App\Lxd\Endpoint\Instance;
use App\Test\TestCase\Lxd\Endpoint\EndpointTestCase;

class ImageTest extends EndpointTestCase
{
    public function testListNotEmpty()
    {
        $list = (new Image())->list();
        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
    }

    /**
     * @internal this must be different to main image due to fingerprint
     */
    public function testCopy()
    {
        $uuid = (new Image())->fetch('alpine/3.11/arm64', [
            'alias' => 'image-test-01',
        ]);
     
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseSuccess($operationResponse);

        return $operationResponse['metadata']['fingerprint'];
    }

    /**
     * @depends testCopy
     * @param string $fingerprint
     */
    public function testInfo($fingerprint)
    {
        $response = (new Image())->get($fingerprint);
    
        $this->assertNotEmpty($response);
        $this->assertEquals('image-test-01', $response['aliases'][0]['name']);
    }

    public function testCreateInstanceFromLocalImage()
    {
        $uuid = (new Instance())->create('image-test-01', 'instance-test');
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseSuccess($operationResponse);
    }

    /**
     * @depends testCreateInstanceFromLocalImage
     */
    public function testCreateImageFromContainer()
    {
        $uuid = (new Instance())->publish('instance-test', [
            'alias' => 'linux image test 02'
        ]);
       
        // this takes longer than 30 seconds
        $operationResponse = $this->waitForOperationToComplete($uuid, 60);
        $this->assertOperationResponseSuccess($operationResponse);
         
        $this->deleteImage($operationResponse['metadata']['fingerprint']);
        $this->deleteInstance('instance-test');
    }

    /**
     * @depends testCopy
     * @param string $fingerprint
     */
    public function testDelete(string $fingerprint)
    {
        $uuid = (new Image())->delete($fingerprint);
      
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseSuccess($operationResponse);
    }

    public function testImport()
    {
        $uuid = (new Image())->import('http://nl.alpinelinux.org/alpine/v3.5/releases/x86_64/alpine-minirootfs-3.5.0-x86_64.tar.gz');
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseError($operationResponse);
        $this->assertEquals('Missing LXD-Image-Hash header', $operationResponse['err']);
    }

    /**
    * Use this for deleting an instance (not for testing delete an instance although the same)
    */
    private function deleteInstance(string $name)
    {
        $uuid = (new Instance())->delete($name);
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseSuccess($operationResponse);
    }

    /**
    * Use this for deleting an image (not for testing delete an image although the same)
    */
    private function deleteImage(string $fingerprint)
    {
        $uuid = (new Image())->delete($fingerprint);
        $operationResponse = $this->waitForOperationToComplete($uuid);
        $this->assertOperationResponseSuccess($operationResponse);
    }
}
