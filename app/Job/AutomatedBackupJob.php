<?php
declare(strict_types = 1);
namespace App\Job;

use Exception;
use Origin\Job\Job;
use App\Lxd\LxdClient;
use App\Model\Entity\AutomatedBackup;
use App\Service\Lxd\LxdCreateSnapshotBackup;

/**
 * @method bool dispatch(AutomatedBackup $backup)
 */
class AutomatedBackupJob extends Job
{
    protected $queue = 'backups';
    
    protected $timeout = 0;

    protected function execute(AutomatedBackup $backup) : void
    {
        $client = new LxdClient($backup->host->address);

        $result = (new LxdCreateSnapshotBackup($client))->dispatch(
            $backup->instance,
            $backup->frequency,
            (int) $backup->retain
        );

        if (! $result->success()) {
            throw new Exception($result->error('message'));
        }
    }
}
