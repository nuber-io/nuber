<?php
declare(strict_types = 1);
namespace App\Model;

use Origin\Http\Exception\InternalErrorException;

class AutomatedBackup extends ApplicationModel
{
    const FREQUENCIES = [
        'hourly',
        'daily',
        'weekly',
        'monthly'
    ];

    /**
    * This is called when the model is constructed.
    */
    protected function initialize(array $config) : void
    {
        parent::initialize($config);

        $this->belongsTo('Host');

        $this->validate('frequency', [
            'required',
            'frequency' => [
                'rule' => ['in', self::FREQUENCIES]
            ],
            'isUnique' => [
                'rule' => [
                    'isUnique',['host_id','instance','frequency']
                ],
                'message' => 'There is already a scheduled backup for this frequency'
            ],
        
        ]);

        $this->validate('at', [
            'required',
            'time'
        ]);

        $this->validate('retain', [
            'required',
            'integer',
            [
                'rule' => ['range',1,30],
                'message' => __('Enter a number between 1 and 30')
            ]
        ]);
    }

    /**
     * Deletes all backups by instance for a host
     *
     * @param string $name
     * @param string $ipAddress
     * @return void
     */
    public function deleteInstance(string $name, string $ipAddress) : void
    {
        $instances = $this->findList($ipAddress, $name);

        $this->doOrFail(
            $this->deleteAll([
                'id' => array_keys($instances)
            ]),
            'Error deleting instance'
        );
    }

    /**
     * Handles renaming the instance names in the backups
     *
     * @param string $from
     * @param string $to
     * @param string $ipAddress
     * @return void
     */
    public function renameInstance(string $from, string $to, string $ipAddress) : void
    {
        $instances = $this->findList($ipAddress, $from);

        $this->doOrFail(
            $this->updateAll(['instance' => $to], [
                'id' => array_keys($instances)
            ]),
            'Error renaming instance'
        );
    }

    /**
     * Updates the automated backup to use the IP address of where the instance
     * was migrated too
     *
     * @param string $sourceIP
     * @param string $destinationIP
     * @return void
     */
    public function changeHost(string $instance, string $sourceIP, string $destinationIP) : void
    {
        $hosts = $this->Host->find('list', [
            'fields' => ['address','id']
        ]);

        $exists = isset($hosts[$destinationIP]) && isset($hosts[$sourceIP]);

        $this->doOrFail(
            $exists && $this->updateAll(
                ['host_id' => $hosts[$destinationIP]],
                [
                    'host_id' => $hosts[$sourceIP],
                    'instance' => $instance
                    
                ]
            ),
            'Error updating hosts'
        );
    }

    /**
     * @param boolean $result
     * @param string $message
     * @return void
     */
    private function doOrFail(bool $result, string $message) : void
    {
        if (! $result) {
            throw new InternalErrorException($message);
        }
    }

    /**
     * Finds a list of backups for a particular instance, host
     *
     * @param string $host
     * @param string $instance
     * @return array
     */
    public function findList(string $host, string $instance) : array
    {
        return $this->find('list', [
            'fields' => ['automated_backups.id','automated_backups.instance'],
            'conditions' => [
                'instance' => $instance,
                'hosts.address' => $host
            ],
            'associated' => ['Host']
        ]);
    }
}
