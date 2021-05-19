<?php
namespace App\Test\TestCase\Console\Command;

use App\Job\AutomatedBackupJob;
use Origin\TestSuite\JobTestTrait;
use Origin\TestSuite\OriginTestCase;
use Origin\TestSuite\ConsoleIntegrationTestTrait;

class ScheduleBackupsCommandTest extends OriginTestCase
{
    protected $fixtures = [
        'AutomatedBackup', 'Host','User','Queue'
    ];

    use ConsoleIntegrationTestTrait;
    use JobTestTrait;

    public function testInvalidFrequency()
    {
        $this->exec('schedule:backups every-2-minutes');
        $this->assertExitError();
        $this->assertErrorContains('Invalid frequency');
    }

    public function testExecuteNoBackups()
    {
        $this->exec('schedule:backups hourly');
        $this->assertExitSuccess();
    }

    public function testExecute()
    {
        $this->exec('schedule:backups monthly');
        $this->assertExitSuccess();
        $this->assertOutputContains('<white>[</white> <green>OK</green> <white>] ubuntu-test@' . LXD_HOST . '</white>');

        $this->assertEnqueuedJobs(1, 'backups');
        $this->assertJobEnqueued(AutomatedBackupJob::class);
        $this->runEnqueuedJobs();
    }
}
