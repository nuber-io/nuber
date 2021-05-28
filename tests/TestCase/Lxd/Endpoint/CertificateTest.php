<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Certificate;
use App\Lxd\Endpoint\Exception\NotFoundException;

class CertificateTest extends EndpointTestCase
{
    public function testStatusTrusted()
    {
        $this->assertEquals('trusted', (new Certificate())->status());
    }

    /**
     * @depends testStatusTrusted
     */
    public function testList()
    {
        $certificates = (new Certificate())->list();
        $this->assertIsArray($certificates);
        $this->assertNotEmpty($certificates);
    }
    
    /**
     * @internal on development machines this test sometimes fails, if it does, it could be because more than one
     * cert was set.
     *
     * @depends testStatusTrusted
     */
    public function testDelete()
    {
        $certificate = new Certificate();
        $certificates = $certificate->list(['recursive' => 0]);
      
        foreach ($certificates as $cert) {
            $result = $certificate->remove($cert);
            $this->assertNull($result);
        }
    }

    /**
     * @depends testDelete
     */
    public function testStatusUntrusted()
    {
        $this->assertEquals('untrusted', (new Certificate())->status());
    }

    /**
     * @depends testDelete
     */
    public function testAdd()
    {
        $result = (new Certificate())->add(env('LXD_PASSWORD'));
        $this->assertNull($result);
    }
    /**
     * @depends testDelete
     */
    public function testInfo()
    {
        $certificate = new Certificate();
        $certificates = $certificate->list(['recursive' => 0]);
        $info = $certificate->get($certificates[0]);
        $this->assertIsArray($info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('certificate', $info);
    }

    public function testDeleteNotFound()
    {
        $this->expectException(NotFoundException::class);
        (new Certificate())->remove('xxxx');
    }

    public function testGenerate()
    {
        $certificate = sys_get_temp_dir() . '/certificate';
        $key = sys_get_temp_dir() . '/privateKey';

        $result = Certificate::generate(sys_get_temp_dir(), [
            'countryName' => 'UK',
            'stateOrProvinceName' => 'London',
            'localityName' => 'London',
            'organizationName' => 'Example.com',
            'organizationalUnitName' => 'Dev',
            'commonName' => '127.0.0.1',
            'emailAddress' => 'dev@esxample.com'
        ]);

        $this->assertTrue($result);
        $this->assertFileExists($certificate);
        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', file_get_contents($certificate));
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', file_get_contents($key));
    }
}
