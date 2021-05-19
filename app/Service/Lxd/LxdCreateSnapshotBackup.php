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
use Origin\Text\Text;
use App\Lxd\LxdClient;
use Origin\Service\Result;
use Origin\Service\Service;
use Origin\Collection\Collection;

/**
 * @method Result dispatch(string $instance, string $frequency, int $retain)
 */
class LxdCreateSnapshotBackup extends Service
{
    use LxdTrait;

    private LxdClient $client;
    
    protected function initialize(LxdClient $client): void
    {
        $this->client = $client;
    }

    protected function execute(string $instance, string $frequency, int $retain) : Result
    {
        $backupName = $this->backupName($frequency) .'-' . time();

        try {
            $this->createBackup($instance, $backupName);
            $deleted = $this->deleteBackups($instance, $frequency, $retain);
        } catch (Exception $exception) {
            return new Result($this->transformException($exception));
        }

        return new Result([
            'data' => [
                'name' => $backupName,
                'deleted' => $deleted
            ]
        ]);
    }

    /**
     * @param string $instance
     * @param string $backupName
     * @return void
     */
    private function createBackup(string $instance, string $backupName) : void
    {
        $response = $this->client->operation->wait(
            $this->client->snapshot->create($instance, $backupName)
        );

        $this->handleResponse($response);
    }

    /**
     * @param string $instance
     * @param string $frequency
     * @param integer $retain
     * @return array
     */
    private function deleteBackups(string $instance, string $frequency, int $retain) : array
    {
        $deleted = [];

        $backups = $this->getBackups($instance, $frequency);

        if ($backups->count() > $retain) {
            $diff = $backups->count() - $retain;

            $toDelete = $backups->take($diff, 0);

            foreach ($toDelete as $snapshot) {
                $this->client->snapshot->delete($instance, $snapshot);
                $deleted[] = $snapshot;
            }
        }

        return $deleted;
    }

    /**
     * @internal this is so important since backup names are backup-{$frequency}-{$timestamp}, sorting is
     * done after removal of non relevant backups.
     *
     * @param string $instance
     * @param string $frequency
     * @return Collection
     */
    private function getBackups(string $instance, string $frequency) : Collection
    {
        $prefix = $this->backupName($frequency);
    
        return collection($this->client->snapshot->list($instance))
            ->reject(function ($backup) use ($prefix) {
                return Text::startsWith($prefix, $backup['name']) === false;
            })
            ->sortBy('created_at', SORT_ASC, SORT_STRING)->extract('name');
    }

    /**
     * @param array $response
     * @return void
     */
    private function handleResponse(array $response) : void
    {
        if (! empty($response['err'])) {
            throw new Exception($response['err'], $response['status_code']);
        }
    }

    /**
     * @param string $frequency
     * @return string
     */
    private function backupName(string $frequency) : string
    {
        return  'backup-' . $frequency;
        ;
    }
}
