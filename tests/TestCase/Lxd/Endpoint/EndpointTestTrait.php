<?php
namespace App\Test\TestCase\Lxd\Endpoint;

use RuntimeException;
use App\Lxd\Endpoint\Image;
use App\Lxd\Endpoint\Instance;
use App\Lxd\Endpoint\Operation;

/**
 * A quick class to create an instance at start of test and then destory
 */
trait EndpointTestTrait
{
    protected static $instance = 'instance-endpoint-test';

    public static function setUpBeforeClass(): void
    {
        fwrite(STDOUT, '#');
        $instance = new Instance();
        $operation = new Operation();
        
        $uuid = $instance->create('ubuntu', self::$instance);

        $response = $operation->wait($uuid);
        if ($response['err']) {
            throw new RuntimeException($response['err']);
        }
        $uuid = $instance->start(self::$instance);
        $operation->wait($uuid);
    }

    public static function tearDownAfterClass(): void
    {
        fwrite(STDOUT, '#');

        $instance = new Instance();
        $operation = new Operation();
        $image = new Image();

        # Stop instance
        $uuid = $instance->stop(self::$instance);
        $operation->wait($uuid);

        # Delete instance
        $uuid = $instance->delete(self::$instance);
        $operation->wait($uuid);
    }
}
