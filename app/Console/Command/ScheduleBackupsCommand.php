<?php
declare(strict_types = 1);
namespace App\Console\Command;

use App\Model\AutomatedBackup;
use App\Job\AutomatedBackupJob;
use Origin\Console\Command\Command;

/**
 * @property \App\Model\AutomatedBackup $AutomatedBackup
 */
class ScheduleBackupsCommand extends Command
{
    protected $name = 'schedule:backups';
    protected $description = 'Schedules backups that need to be done';

    protected AutomatedBackup $automatedBackup;

    protected function initialize(): void
    {
        $this->loadModel('AutomatedBackup');

        $this->addArgument('frequency', [
            'description' => 'The frequency this backup is for, e.g. ' . implode(', ', AutomatedBackup::FREQUENCIES),
        ]);
    }

    protected function execute(): void
    {
        if (! in_array($this->arguments('frequency'), AutomatedBackup::FREQUENCIES)) {
            $this->throwError('Invalid frequency');
        }

        $pending = $this->AutomatedBackup->where(['frequency' => $this->arguments('frequency')])
            ->with(['Host'])
            ->all();

        foreach ($pending as $automatedBackup) {
            $result = (new AutomatedBackupJob())->dispatch($automatedBackup);
            $this->io->status(
                $result ? 'ok' : 'error',
                $automatedBackup->instance . '@' . $automatedBackup->host->address
            );
        }
    }
}
