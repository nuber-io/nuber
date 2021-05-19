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

use RuntimeException;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use Origin\Service\Service;
use App\Model\AutomatedBackup;

/**
 * @method Result dispatch(string $instance)
 */
class LxdDestroyInstance extends Service
{
    use LxdTrait;

    private LxdClient $lxd;
    private AutomatedBackup $AutomatedBackup;

    protected function initialize(LxdClient $client, AutomatedBackup $automatedBackup): void
    {
        $this->lxd = $client;
        $this->AutomatedBackup = $automatedBackup;
    }

    /**
     * @param string $instance
     * @return \Origin\Service\Result;Result
     */
    protected function execute(string $instance): Result
    {
        try {
            $this->removeSnapshots($instance);
            $this->destroyInstance($instance);
            $this->deleteScheduledBackups($instance);
        } catch (RuntimeException $exception) {
            return new Result($this->transformException($exception));
        }

        return new Result(['data' => []]);
    }

    /**
     * Remove existing snapshots due to bug with ZFS and snapshots.
     *
     * @param string $instance
     * @return void
     */
    private function removeSnapshots(string $instance) : void
    {
        $snapshots = $this->lxd->snapshot->list($instance, ['recursive' => 0]);

        foreach ($snapshots as $snapshot) {
            $response = $this->lxd->operation->wait(
                $this->lxd->snapshot->delete($instance, $snapshot)
            );

            $this->checkResponse($response);
        }
    }

    /**
     * @param string $instance
     * @return void
     */
    private function destroyInstance(string $instance) : void
    {
        $response = $this->lxd->operation->wait(
            $this->lxd->instance->delete($instance)
        );

        $this->checkResponse($response);
    }

    /**
     * @param string $instance
     * @return void
     */
    private function deleteScheduledBackups(string $instance) : void
    {
        $this->AutomatedBackup->deleteInstance(
            $instance,
            $this->lxd->hostName()
        );
    }

    /**
     * @param array $response
     * @return void
     */
    private function checkResponse(array $response) : void
    {
        if ($response['err']) {
            throw new RuntimeException($response['err']);
        }
    }
}
