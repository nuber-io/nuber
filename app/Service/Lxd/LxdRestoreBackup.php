<?php
/**
 * Nuber.io
 * Copyright 2020 - 2021 Jamiel Sharief.
 *
 * SPDX-License-Identifier: AGPL-3.0
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.nuber.io
 * @license     https://opensource.org/licenses/AGPL-3.0 AGPL-3.0 License
 */
declare(strict_types = 1);
namespace App\Service\Lxd;

use Exception;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use Origin\Service\Service;

use function Origin\Defer\defer;

/**
 *
 * @method Result dispatch(string $instance,string $backup)
 */
class LxdRestoreBackup extends Service
{
    use LxdTrait;

    private LxdClient $client;

    // flags
    private bool $instanceWasRunning = false;
    private ?string $renamedTo = null;

    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    /**
     * Renames to tmp name first and put to actual current container name this
     * is because it restores to the name of the container when backup was done.
     * Newer version of LXD has this feature, but for now this is fine.
     *
     * @param string $instance
     * @param string $backup
     * @return Result
     */
    protected function execute(string $instance, string $backup): Result
    {
      
        // stops the instance if it is running and rename to a temporary name
        try {
            $this->prepareInstance($instance);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }

        // restoration logic
        try {
            $this->restoreBackup($instance, $backup);
        } catch (Exception $exception) {
  
            /**
             * RESTORE FAILURE RECOVERY
             * ------------------------
             *
             * This has been tested with a 1GB instance, installing apache2, mysql-server and PHP creating a
             * backup then trying to restore it. This exception is caught here and gives the following error:
             *
             * Post hook: Failed to run: zfs set quota=1000000000 lxdpool/containers/ubuntu: cannot set property
             * for 'lxdpool/containers/ubuntu': size is less than current used or reserved space
             */

            $this->wait(
                $this->client->instance->rename($this->renamedTo, $instance)
            );

            return new Result(
                $this->transformException($exception)
            );
        }

        // delete and rename instances
        try {
            $this->cleanup($instance);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }

        return new Result([
            'data' => []
        ]);
    }

    /**
     * This restores the instance and if the backup was under a different name
     * it will be renamed. There is a feature in new LXD which does this, but
     * it is not in the stable branch.
     *
     * @param string $backup
     * @param string $name
     * @return void
     */
    private function restoreBackup(string $instance, string $backup): void
    {
        $tarball = $this->client->backup->export($this->renamedTo, $backup);
        
        defer($void, 'unlink', $tarball); // cleanup after
        
        $response = $this->wait(
            $this->client->backup->import($tarball)
        );

        if (! empty($response['err'])) {
            throw new Exception($response['err'], $response['status_code']);
        }
       
        $restoredName = substr($response['resources']['instances'][0], 15);

        if ($restoredName !== $instance) {
            $this->wait(
                $this->client->instance->rename($restoredName, $instance)
            );
        }
    }

    /**
    * Checks the state and stops the instance, returns true if the
    * instance was running
    *
    * @param string $instance
    * @return void
    */
    private function prepareInstance(string $instance): void
    {
        $state = $this->client->instance->state($instance) ;

        $this->instanceWasRunning = $state['status'] === 'Running';

        if ($this->instanceWasRunning) {
            $this->wait(
                $this->client->instance->stop($instance)
            );
        }

        /**
         * TODO: In the event of error this causes ghost containers, so logic before return after catching exception
         * needs to undo rename or delete depending what stage this was caught.
         */
        $tmp = 'nuber-' . time();
 
        $this->wait(
            $this->client->instance->rename($instance, $tmp)
        );

        $this->renamedTo = $tmp;
    }

    /**
     * Cleans up after restoring, start the container and then deletes
     * the old one
     *
     * @param string $instance
     * @return void
     */
    private function cleanup(string $instance): void
    {
        if ($this->instanceWasRunning) {
            // Use custom start layer
            $result = (new LxdStartInstance($this->client))->dispatch($instance, true);
            if (! $result->success()) {
                throw new Exception('Error starting instance', 500);
            }
        }

        $this->wait(
            $this->client->instance->delete($this->renamedTo)
        );
    }

    /**
     * Uses the local client/from
     *
     * @param string $uuid
     * @return array
     */
    private function wait(string $uuid): array
    {
        return $this->backgroundOperation($this->client, $uuid);
    }
}
