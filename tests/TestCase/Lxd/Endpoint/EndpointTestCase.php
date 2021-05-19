<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use App\Lxd\Endpoint\Operation;

abstract class EndpointTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Asserts that an operation was run successfully
     *
     * @param string $uuid
     * @param integer $timeout
     * @return void
     */
    protected function assertBackgroundOperationSuccess(string $uuid, int $timeout = null)
    {
        $this->assertOperationResponseSuccess(
            $this->waitForOperationToComplete($uuid, $timeout)
        );
    }
    
    /**
    * Asserts that an operation was run successfully
    *
    * @param string $uuid
    * @return void
    */
    protected function assertBackgroundOperationError(string $uuid, int $timeout = null)
    {
        $this->assertOperationResponseError(
            $this->waitForOperationToComplete($uuid, $timeout)
        );
    }

    /**
     * Checks that a result from a background operation was a success
     *
     * @param array $response
     * @param integer $statusCode 200
     * @return void
     */
    protected function assertOperationResponseSuccess(array $response, int $statusCode = 200)
    {
        $this->assertEmpty($response['err'], $response['err']);
        $this->assertEquals((string) $statusCode, $response['status_code']);
    }

    /**
     * Checks that a result from a background operation was a failure
     *
     * @param array $response
     * @param integer $statusCode 200
     * @return void
     */
    protected function assertOperationResponseError(array $response, int $statusCode = 400)
    {
        $this->assertNotEmpty($response['err']);
        $this->assertEquals((string) $statusCode, $response['status_code']);
    }

    /**
     * Use this to wait for a background operation to complete, it will fail if the response sent
     * is incorrect, or the wait operation did not run properly.
     *
     * @param string $uuid
     * @param integer $timeout
     * @return array
     */
    protected function waitForOperationToComplete(string $uuid, int $timeout = null): array
    {
        $response = (new Operation())->wait($uuid, $timeout);
        $this->assertNotEmpty($response, 'Operation did not complete correctly');

        return $response;
    }
}
