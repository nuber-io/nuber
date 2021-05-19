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
use App\Model\AutomatedBackup;
use App\Service\ApplicationService;

/**
 * Eventhough LXD supports live migration, their docs states "Migration is still in experimental
 * stages and may not work for all workloads". Therefore not going to use it.
 *
 * - Cannot freeze and migrate without CRIU.
 *
 * @method Result dispatch(string $instance, string $from, string $to, bool $deleteSource = true)
 */
class LxdMigrate extends ApplicationService
{
    use LxdTrait;

    private LxdClient $local;
    private LxdClient $remote;

    // flags
    private bool $instanceWasRunning = false;
    private bool $clone = false;

    private AutomatedBackup $AutomatedBackup;

    protected function initialize(AutomatedBackup $automatedBackup): void
    {
        $this->AutomatedBackup = $automatedBackup;
    }

    /**
     * @param string $instance
     * @param string $from
     * @param string $to
     * @param boolean $clone
     * @return Result
     */
    protected function execute(string $instance, string $from, string $to, bool $clone = true): Result
    {
        $this->local = new LxdClient($from);
        $this->remote = new LxdClient($to);
        $this->clone = $clone;

        try {
            $this->stopIfRunning($instance);
            
            $result = (new LxdDetectNetworkInterfaces($this->local))->dispatch($instance);
            
            $this->migrateInstance($instance, $to);

            // Reapply network settings for macvlan setups
            if ($this->isMacvlan($result)) {
                $this->applyNetworkSettings($instance, $result->data('eth0'), $result->data('eth1'));
            }
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }

        if ($clone === false) {
            $this->updateAutomatedBackups($instance, $from, $to);
        }

        // start the instance if it was running the migration started
        try {
            $this->startIfNeeded($instance);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }

        // deletes the local instance
        try {
            $this->deleteIfRequried($instance);
        } catch (Exception $exception) {
            return new Result(
                $this->transformException($exception)
            );
        }

        return new Result(['data' => []]);
    }

    private function isMacvlan(Result $result) : bool
    {
        return $result->data('eth0') === 'nuber-macvlan' || $result->data('eth1') === 'nuber-macvlan';
    }

    /**
     * Checks the state and stops the instance, returns true if the
     * instance was running
     *
     * @param string $instance
     * @return void
     */
    private function stopIfRunning(string $instance): void
    {
        $state = $this->local->instance->state($instance) ;

        $this->instanceWasRunning = $state['status'] === 'Running';

        if ($this->instanceWasRunning) {
            $this->wait($this->local->instance->stop($instance));
        }
    }

    /**
     *
     * @param string $instance
     * @param string $to
     * @return void
     */
    private function migrateInstance(string $instance, string $to): void
    {
        // Not sure if this is needed
        set_time_limit(0);
            
        $this->waitOnServer(
            $this->local->instance->migrate($instance, $to, $this->clone)
        );
    }

    /**
     * Macvlan settings can vary between hosts because the network connection is used.
     *
     * @param string $instance
     * @return void
     */
    private function applyNetworkSettings(string $instance, string $eth0, string $eth1 = null) : void
    {
        $result = (new LxdChangeNetworkSettings($this->remote))->dispatch($instance, $eth0, $eth1);

        if ($result->error()) {
            throw new Exception($result->error('message'), $result->error('code'));
        }
    }

    /**
     * When migrating to a different server the database records need to be updated
     *
     * @param string $instance
     * @param string $from
     * @param string $to
     * @return void
     */
    private function updateAutomatedBackups(string $instance, string $from, string $to) : void
    {
        $this->AutomatedBackup->changeHost($instance, $from, $to);
    }

    /**
     * Starts the instance, if its a clone, then start the local one if its a full
     * migration then start on remote. (not both). A clone can have a copy of the IP
     * address so this would need to removed first before starting, and this service
     * will not do that since the clone could be a remote backup.
     *
     * @param string $instance
     * @return void
     */
    private function startIfNeeded(string $instance): void
    {
        if ($this->instanceWasRunning) {
            if ($this->clone) {
                $this->wait($this->local->instance->start($instance));
            } else {
                $this->waitOnServer($this->remote->instance->start($instance));
            }
        }
    }

    /**
     * Before deleting, a last minute check to make sure the container exists on
     * the other server.
     *
     * @param string $instance
     * @return void
     */
    private function deleteIfRequried(string $instance): void
    {
        if ($this->clone === false && $this->remote->instance->info($instance)) {
            $this->wait($this->local->instance->delete($instance));
        }
    }

    /**
     * Uses the local client/from
     *
     * @param string $uuid
     * @return array
     */
    private function wait(string $uuid): array
    {
        return $this->backgroundOperation($this->local, $uuid);
    }

    /**
     * Waits for an operation being run on the remote server
     *
     * @param string $uuid
     * @return array
     */
    private function waitOnServer(string $uuid): array
    {
        return $this->backgroundOperation($this->remote, $uuid);
    }
}
