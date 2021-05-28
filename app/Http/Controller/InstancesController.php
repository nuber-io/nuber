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
namespace App\Http\Controller;

use Exception;
use App\Lxd\Lxd;
use Origin\Log\Log;
use App\Lxd\LxdMeta;
use Origin\Text\Text;
use Origin\Core\Config;
use App\Form\ResizeForm;
use App\Form\VolumeForm;
use Origin\Core\PhpFile;
use Origin\Http\Response;
use App\Form\InstanceForm;
use App\Form\SnapshotForm;
use App\Form\IpAddressForm;
use App\Lxd\Endpoint\Alias;
use App\Form\NetworkingForm;
use App\Service\Lxd\LxdMigrate;
use App\Form\ForwardTrafficForm;
use App\Service\Lxd\LxdDiskUsage;
use Origin\Collection\Collection;
use App\Service\Lxd\LxdPreMigrate;
use App\Service\Lxd\LxdArchitecture;
use App\Service\Lxd\LxdCloneInstance;

use App\Service\Lxd\LxdImageDownload;
use App\Service\Lxd\LxdStartInstance;
use App\Service\Lxd\LxdCreateInstance;
use App\Service\Lxd\LxdDestroyInstance;
use App\Service\Lxd\LxdConfigureIpAddress;
use Origin\Http\Exception\NotFoundException;
use App\Service\Lxd\LxdChangeNetworkSettings;
use Origin\Http\Exception\BadRequestException;
use App\Service\Lxd\LxdDetectNetworkInterfaces;
use Origin\HttpClient\Exception\ConnectionException;

/**
 * @property \App\Model\Host $Host
 * @property \App\Model\AutomatedBackup $AutomatedBackup
 */
class InstancesController extends ApplicationController
{
    protected function initialize(): void
    {
        parent::initialize();
        // disable internal instances
        if ($this->request->params('args')) {
            foreach ($this->request->params('args') as $arg) {
                if (Text::startsWith('nuber-', $arg)) {
                    throw new NotFoundException('Not Found');
                }
            }
        }

        $this->loadModel('AutomatedBackup');
    }

    /**
     * Virtual machines not supported at this momment, some differences includes attaching volumes or changing CPU limit and disk resizing with
     * VM running breaks. Also trying to reduce the CPU limit i got an error as well. So this needs to be looked into, if
     * VMs will be supported in the future.
     *
     * @return array
     */
    protected function instances(): array
    {
        $instances = [];
        try {
            $instances = $this->lxd->instance->list();
            // remove internal instances
            $instances = (new Collection($instances))->reject(function ($instance) {
                return Text::startsWith('nuber-', $instance['name']) || $instance['type'] === 'virtual-machine';
            })->toArray();
        } catch (ConnectionException $exception) {
            Log::error($exception->getMessage());
            $this->Flash->error(__('Could not connect to host.'));
        }

        return $instances;
    }

    public function index()
    {
        $instances = $this->instances();

        $this->set('instances', LxdMeta::add($instances));
    }

    /**
     * Undocumented function
     *
     * @param string $instance
     * @return void
     */
    public function clone(string $instance)
    {
        $cloneForm = InstanceForm::new();
        
        if ($this->request->is('post')) {
            $cloneForm = InstanceForm::patch($cloneForm, $this->request->data());
            $cloneForm->addExisting($this->lxd->instance->list(['recursive' => 0]));

            // check if JS validation bypased
            if ($cloneForm->validates()) {
                $result = (new LxdCloneInstance($this->lxd))->dispatch($instance, $cloneForm->name);

                if ($result->success()) {
                    $this->Flash->success(__('The instance was cloned.'));

                    return $this->redirect(['action' => 'details',  $cloneForm->name]);
                }
            }
            $this->Flash->error(__('The instance could not be cloned.'));
        }

        $this->set('name', $instance);
        $this->set('cloneForm', $cloneForm);
    }

    /**
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function rename(string $instance)
    {
        $renameForm = InstanceForm::new();

        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        if ($this->request->is('post')) {
            $renameForm = InstanceForm::patch($renameForm, $this->request->data());

            $renameForm->addExisting(
                $this->lxd->instance->list(['recursive' => 0]) + [$instance]
            );
    
            if ($renameForm->validates()) {
                $response = $this->lxd->operation->wait(
                    $this->lxd->instance->rename($instance, $renameForm->name)
                );

                if (empty($response['err'])) {
                    // Update the automated backups
                    $this->AutomatedBackup->renameInstance($instance, $renameForm->name, $this->lxd->hostName());

                    $this->Flash->success(__('The instance has been renamed.'));
                    
                    return $this->redirect(['action' => 'rename', $renameForm->name]);
                }
                $this->Flash->error(__('An error occured.')); // internal error
            } else {
                $this->Flash->error(__('The instance could not be renamed.'));
            }
        } else {
            $renameForm->set([
                'name' => $instance
            ]);
        }
        $this->set(compact('renameForm'));
    }

    /**
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function resize(string $instance)
    {
        $instanceMeta = $this->loadInstanceMeta($instance);
        if ($instanceMeta instanceof Response) {
            return $instanceMeta;
        }

        $resizeForm = ResizeForm::new();

        if ($this->request->is('post')) {
            $resizeForm = ResizeForm::patch($resizeForm, $this->request->data());
         
            if ($resizeForm->validates()) {
                $hardDisk = $instanceMeta['expanded_devices']['root'];
                $response = $this->lxd->instance->edit($instance, [
                    'config' => [
                        'limits.memory' => $resizeForm->memory,
                        'limits.cpu' => (string) $resizeForm->cpu
                    ],
                    'devices' => [
                        'root' => [
                            'path' => $hardDisk['path'],
                            'size' => $resizeForm->disk,
                            'type' => $hardDisk['type'],
                            'pool' => $hardDisk['pool']
                        ]
                    ]
                ]);

                if (empty($response['err'])) {
                    $this->Flash->success(__('The instance has been resized.'));

                    return $this->redirect(['action' => 'resize',$instance]);
                }
                $this->Flash->error(__('An error occured.')); // internal error
            } else {
                $this->Flash->error(__('The instance could not be resized.'));
            }
        } else {
            $resizeForm->set([
                'memory' => $instanceMeta['meta']['memory'],
                'disk' => $instanceMeta['meta']['storage'],
                'cpu' => $instanceMeta['meta']['cpu'],
                'disk_usage' => $instanceMeta['state']['disk']['root']['usage']
            ]);
        }

        $this->set(compact('resizeForm'));
    }

    public function console(string $instance)
    {
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        /**
         * This method, need to detach properly if not in runs into problems, it also requires
         * setting up passwords
         */

        /*
        $response = (new Console())->attach($instance);
        $path = "/1.0/operations/{$response['id']}/websocket?secret={$response['metadata']['fds'][0]}";
        $this->set('path', $path);
        */
    
        if ($info['status'] === 'Running') {
            /**
             * Detect the shell for the root user by parsing the password file
             * e.g. root:*:0:0:System Administrator:/var/root:/bin/sh
             * @internal out of space errors will break this for some reason
             */
            $output = $this->lxd->instance->execCommand($instance, 'grep ^root /etc/passwd');
            
            $shell = explode(':', $output)[6]; // ?? '/bin/sh';
           
            $response = $this->lxd->instance->execInteractive($instance, $shell, [
                'environment' => [
                    'HOME' => '/root',
                    'TERM' => 'xterm-256color',
                    'USER' => 'root'
                ]
            ]);

            $this->set(
                'path',
                "/1.0/operations/{$response['id']}/websocket?secret={$response['metadata']['fds'][0]}"
            );
        }
        
        $this->set('status', $info['status']);
        $this->set('node', Lxd::host());
    }

    /**
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function ports(string $instance)
    {
        $forwardTrafficForm = ForwardTrafficForm::new();
        
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }
      
        if ($this->request->is('post')) {
            $forwardTrafficForm = ForwardTrafficForm::patch($forwardTrafficForm, $this->request->data());
            $forwardTrafficForm->checkPortsInUse($info['devices']);

            /**
             * When cloning a machine with static IP and proxies the container
             * won't start/work. Need to change the IP address.
             *
             * @internal ipv6 addresses are square notation e.g connect=tcp:[2001:db8::1]:80
             * @see https://lxd.readthedocs.io/en/latest/instances/#type-proxy
             *
             * Also for IPV6 you have to use [::] not 0.0.0.0
             */
            if ($forwardTrafficForm->validates()) {
                $hostIp = Lxd::host();
         
                if (isset($info['devices']['eth0']['ipv4.address'])) {
                    // uses iptables/nftables requires a static IP address to be set
                    $deviceConfig = [
                        'connect' => "tcp:0.0.0.0:{$forwardTrafficForm->connect}",
                        'listen' => "tcp:{$hostIp}:{$forwardTrafficForm->listen}",
                        'type' => 'proxy',
                        'nat' => 'true'
                    ];
                } else {
                    // Uses a seperate process for each proxy device
                    $deviceConfig = [
                        'connect' => "tcp:127.0.0.1:{$forwardTrafficForm->connect}",
                        'listen' => "tcp:0.0.0.0:{$forwardTrafficForm->listen}",
                        'type' => 'proxy'
                    ];
                }
              
                try {
                    $this->lxd->device->add($instance, 'proxy-' . $forwardTrafficForm->connect . $forwardTrafficForm->listen, $deviceConfig);

                    $this->Flash->success(__('Traffic from port {listen} will be forwarded to port {connect}.', [
                        'listen' => $forwardTrafficForm->listen,
                        'connect' => $forwardTrafficForm->connect
                    ]));

                    // IMPORTANT: Data needs to be reloaded, I prefer this
                    return $this->redirect(['action' => 'ports',$instance]);
                } catch (Exception $exception) {
                    $this->Flash->error(__('An error occured.')); // internal error
                    Log::error($exception->getMessage());
                }
            } else {
                $this->Flash->error(__('Traffic fowarding could not be setup.'));
            }
        }
    
        $this->set(compact('forwardTrafficForm'));
    }

    /**
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function volumes(string $instance)
    {
        $attachVolumeForm = VolumeForm::new();
 
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        $list = $this->lxd->volume->list();

        if ($this->request->is('post')) {
            $attachVolumeForm = VolumeForm::patch($attachVolumeForm, $this->request->data());
            $attachVolumeForm->addVolumes(
                $attachVolumeForm->extractList($list)
            );
            $attachVolumeForm->addPaths($info['devices']);

            if ($attachVolumeForm->validates()) {
                $deviceName = $attachVolumeForm->nextDeviceName($info['devices']);
        
                $response = $this->lxd->operation->wait(
                    $this->lxd->volume->attach($attachVolumeForm->name, $instance, $deviceName, $attachVolumeForm->path)
                );
               
                if (empty($response['err'])) {
                    $this->Flash->success(__('The volume {volume} was attached.', ['volume' => $attachVolumeForm->name]));

                    return $this->redirect(['action' => 'volumes',$instance]); // reload page
                }
                
                $this->Flash->error(__('An error occured.')); // internal error
            } else {
                $this->Flash->error(__('The volume could not be attached.'));
            }
        }
       
        $sizes = $volumes = [];
        foreach ($list as $index => $volume) {
            $sizes[$volume['name']] = $volume['config']['size'] ?? null;
   
            if (empty($volume['used_by'])) {
                $volumes[$volume['name']] = $volume['name'];
            }
        }
       
        $this->set(compact('list', 'attachVolumeForm', 'sizes', 'volumes'));
    }

    /**
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function snapshots(string $instance)
    {
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }
        
        $snapshotForm = SnapshotForm::new();

        if ($this->request->is(['post'])) {
            $snapshotForm = SnapshotForm::patch($snapshotForm, $this->request->data());
            $snapshotForm->addExisting($this->lxd->snapshot->list($instance, ['recursive' => 0]));
            if ($snapshotForm->validates()) {
                $response = $this->lxd->operation->wait(
                    $this->lxd->snapshot->create($instance, $snapshotForm->name)
                );
                if (empty($response['err'])) {
                    $this->Flash->success(__('The snapshot has been created.'));

                    return $this->redirect(['action' => 'snapshots',$instance]);
                } else {
                    $this->Flash->error(__('An error occured.')); // internal error
                }
            } else {
                $this->Flash->error(__('The snapshot could not be created.'));
            }
        }
      
        $snapshots = $this->lxd->snapshot->list($instance);

        // remove backups
        $snapshots = collection($snapshots)->reject(function ($snapshot) {
            return Text::startsWith('backup-', $snapshot['name']);
        });
        
        /*   $prefix = 'snapshot-' . date('Ymd') . '-';
           $list = collection($snapshots)
               ->extract('name')
               ->filter(function ($snapshot) use ($prefix) {
                   return Text::startsWith($prefix, $snapshot);
               })
               ->map(function ($snapshot) use ($prefix) {
                   return (int) Text::right($prefix, $snapshot);
               })->toList();

           $max = $list ? max($list) + 1 : 1;

           $snapshot = $prefix .  sprintf('%02d', $max);
           */
        $snapshot = 'snapshot-' . time();
 
        $this->set(compact('snapshots', 'snapshot', 'snapshotForm'));
    }
 
    /**
     * TODO: move to backups controller
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function backups(string $instance)
    {
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        $host = $this->Host->where(['address' => $this->Session->read('Lxd.host')])->first();

        $automatedBackup = $this->AutomatedBackup->new([
            'host_id' => $host->id,
            'instance' => $instance,
            'frequency' => 'weekly',
            'at' => '00:00',
            'retain' => 4
        ]);

        if ($this->request->is(['post'])) {
            $automatedBackup = $this->AutomatedBackup->patch($automatedBackup, $this->request->data());
            if ($this->AutomatedBackup->save($automatedBackup)) {
                $this->Flash->success(__('Your backup schedule has been created.'));
                /* @see https://en.wikipedia.org/wiki/Post/Redirect/Get Disabling this will cause for post to be rebusmmited*/
                return $this->redirect(['action' => 'backups',$instance]);
            }
            $this->Flash->error(__('Could not schedule your backup.'));
        }

        $scheduledBackups = $this->AutomatedBackup->where(['host_id' => $host->id,'instance' => $instance])->all();

        $backups = $this->lxd->snapshot->list($instance);
     
        $backups = collection($backups)->reject(function ($backup) {
            return Text::startsWith('backup-', $backup['name']) === false;
        })->sortBy('created_at', SORT_ASC, SORT_STRING);

        $this->set(compact('scheduledBackups', 'automatedBackup', 'backups'));
    }

    /**
     * Action for changing the IP address part of the networking
     *
     * @param string $instance
     * @return \Origin\Http\Response
     */
    public function ipSettings(string $instance)
    {
        $this->request->allowMethod('post');
        $ipAddressForm = IpAddressForm::new($this->request->data());

        if ($ipAddressForm->validates()) {
            $result = (new LxdConfigureIpAddress($this->lxd))->dispatch($instance, $ipAddressForm->ip4_address, $ipAddressForm->ip6_address);
        }
        if (isset($result) && $result->success()) {
            if ($ipAddressForm->ip4_address) {
                $this->Flash->success(__('The IP address was set.'));
            } else {
                $this->Flash->success(__('The IP address was removed.'));
            }
        } else {
            $this->Flash->error(__('The IP address settings could not be changed.'));
        }

        return $this->redirect(['action' => 'networking',$instance]);
    }

    /**
     * Action for changing the Network Settings part of the networking
     *
     * @param string $instance
     * @return \Origin\Http\Response
     */
    public function networkSettings(string $instance)
    {
        $this->request->allowMethod('post');
        $networkingForm = NetworkingForm::new($this->request->data());

        $networkingForm->setNetworks($this->lxd->network->list(['recursive' => 0]));
        
        if ($networkingForm->validates()) {
            // Change network settings
            $result = (new LxdChangeNetworkSettings($this->lxd))->dispatch(
                $instance,
                $networkingForm->eth0,
                $networkingForm->eth1
            );
            if ($result->success()) {
                $this->Flash->success(__('Networking settings have been updated.'));
            } else {
                $this->Flash->error($result->error('message'));
                $this->Flash->Error(__('The network settings could not be updated.'));
            }
        } else {
            $this->Flash->error(__('Invalid Network Settings.'));
        }

        return $this->redirect(['action' => 'networking',$instance]);
    }

    /**
     *
     * To the user we present as network, but we use profiles to setup the network and device.
     *
     * @internal Possible issues with networking and DCHP leases if changing networks whilst
     * the instance is running, therefore to prevent any unexpected issues the instance
     * must be stopped first.
     *
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function networking(string $instance)
    {
        $networkingForm = NetworkingForm::new();
        $ipAddressForm = IpAddressForm::new();

        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        $hasPorts = collection($info['devices'])->filter(function ($device) {
            return $device['type'] === 'proxy';
        })->isEmpty() === false;

        $ipAddressForm->set([
            'ip4_address' => $info['devices']['eth0']['ipv4.address'] ?? null,
            'ip6_address' => $info['devices']['eth0']['ipv6.address'] ?? null
        ]);

        $result = (new LxdDetectNetworkInterfaces($this->lxd))->dispatch($instance);

        $networkingForm->set([
            'eth0' => $result->data('eth0'),
            'eth1' => $result->data('eth1'),
        ]);
        
        $networks = $this->getNetworkList();

        /**
         * This assumes the first network card is being used, if not it wont work.
         * $ lxc profile device add nuber-macvlan eth0 nic nictype=macvlan parent=wlx28ee52172bcc
         * $ lxc profile assign apache nuber-default,nuber-macvlan
         * $ lxc profile create nuber-private
         * $ lxc profile device add nuber-private eth1 nic network=lxdbr0
         * $ lxc profile assign c2 nuber-default,nuber-macvlan,nuber-private
         */

        $this->set(compact('networks', 'ipAddressForm', 'networkingForm', 'hasPorts'));
    }

    private function getNetworkList() : array
    {
        $networks = $this->lxd->network->list();
   
        $networks = collection($networks)->filter(function ($network) {
            return $network['type'] === 'bridge' && $network['description'] === NUBER_VIRTUAL_NETWORK;
        })->map(function ($network) {
            $network['description'] = __('Virtual Network') . ': ' . $network['name'];

            return $network;
        })->combine('name', 'description')->toArray();

        $networks['nuber-macvlan'] = __('Macvlan Network');

        //  Only display if the bridge was setup during install
        if ($this->bridgedNetworkEnabled()) {
            $networks['nuber-bridged'] = __('Bridged Network');
        }

        return $networks;
    }

    /**
     * @internal nuberbr1 renamed to nuber-bridged in 0.2.0, so need to add some backwards
     * compatibility

     * @return boolean
     */
    private function bridgedNetworkEnabled() : bool
    {
        $enabled = false;
        $networks = $this->lxd->network->list(['recursive' => 0]);
        if (in_array('nuber-bridged', $networks)) {
            $enabled = true;
        } elseif (in_array('nuberbr1', $networks)) {
            /**  @deprecated This code needs to be removed in 1.0.0 */
            $network = $this->lxd->network->get('nuberbr1');
            $parent = $network['config']['parent'] ?? null;
            $enabled = $parent === 'nuberbr1';
        }

        return $enabled;
    }

    /**
     * Loads the Information for the view, incase there is an issue with connecting to the server then
     * user is redirect to index.
     *
     * @param string $name
     * @return \Origin\Http\Response|array
     */
    private function loadInstanceMeta(string $name)
    {
 
        // This is actually faster getting instance info and state each time
        try {
            $info = $this->lxd->instance->list();
            /**
             * Virtual machines are not supported at this time, prevent users from getting round this
             * as it may cause some unexpected results or errors when resizing or trying to attach a volume etc.
             */
            $info = collection($info)->filter(function ($instance) use ($name) {
                return $instance['name'] === $name && $instance['type'] === 'container';
            });
        } catch (ConnectionException $exception) {
            return $this->redirect(['action' => 'index']);
        }
      
        if ($info->isEmpty()) {
            throw new NotFoundException(sprintf('Instance %s not found', $name));
        }

        $info = LxdMeta::add($info->first());
       
        $this->set('meta', $info);

        return $info;
    }

    public function wizard()
    {
        $this->Session->write('instanceWizard', []);
       
        $distributions = (new PhpFile)->read(CONFIG . '/distributions.php');

        $this->set('distributions', $distributions);

        $images = [];

        foreach ($this->lxd->image->list() as $image) {
            $default = substr($image['fingerprint'], 0, 12);
            if (! empty($image['properties'])) {
                $default = $image['properties']['os'] . ' ' . $image['properties']['release'] . ' ' . $image['properties']['architecture'];
            }

            $images[$image['fingerprint']] = $image['aliases'][0]['name'] ?? $default;
        }

        $result = (new LxdArchitecture($this->lxd))->dispatch();

        $this->set('architecture', $result->data('architecture'));
        $this->set('images', $images);
    }

    /**
     * @todo refactor
     */
    public function create()
    {
        $instanceForm = InstanceForm::new();

        if ($this->request->is('post')) {
            $instanceForm = InstanceForm::patch($instanceForm, $this->request->data());
            $instanceForm->validateConfig();
            $instanceForm->addExisting($this->lxd->instance->list(['recursive' => 0]));

            if ($instanceForm->validates()) {
                $this->Session->write('instanceCreate', $instanceForm->toArray());

                $fingerprint = $instanceForm->image;
                # Get fingerprint, if no fingerprint then download
                if (! $this->isFingerprint($fingerprint)) {
                    $fingerprint = $this->getFingerprint($fingerprint);
                    if (! $fingerprint) {
                        return $this->redirect(['action' => 'index','?' => ['download' => $instanceForm->image,'instance' => $instanceForm->name]]);
                    }
                }
             
                $instanceForm->fingerprint = $fingerprint;
                $this->Session->write('instanceCreate', $instanceForm->toArray());
    
                return $this->redirect(['action' => 'index','?' => ['create' => $instanceForm->name]]);
            }
  
            $this->Flash->error(__('The instance could not be created.'));
        } else {
            if (! $this->request->query('image')) {
                throw new BadRequestException('Bad Request');
            }
            $instanceForm->image = $this->request->query('image');
            $instanceForm->name = $instanceForm->defaultName($instanceForm->image);

            // replace image with fingerprint
            if ($this->request->query('fingerprint')) {
                $instanceForm->image = $this->request->query('fingerprint');
            }
        }

        $networks = $this->getNetworkList();
        $this->set(compact('instanceForm', 'networks'));
    }

    /**
     * Downloads the image the for an instance, this part of the instance
     * creating. It is called when an instance is created but the image is not
     * available, so it needs to be downloaded and the data needs to be adjusted
     * to include the fingerprint.
     *
     * @param string $instance
     */
    public function download(string $instance)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('post');

        $data = $this->Session->read('instanceCreate');

        $result = (new LxdImageDownload($this->lxd))->dispatch(
            $data['image'],
            Config::read('App.imageDownloadTimeout')
        );
        
        if ($result->success()) {
            $this->Flash->success(__('The image has been downloaded.'));

            $data['fingerprint'] = $result->data['fingerprint'];

            $this->Session->write('instanceCreate', $data);

            return $this->renderJson(['data' => $result->data]);
        }

        Log::error($result->error('message'));
    
        return $this->renderJson([
            'error' => [
                'message' => $result->error('message'),
                'code' => 400,
            ]
        ], 400);
    }

    /**
     * Creates and launches the instance which was setup in Instances::create()
     *
     * @param string $uuid
     * @return void
     */
    public function init(string $name)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('post');

        $instance = $this->Session->read('instanceCreate');

        if (! $instance || $instance['name'] !== $name) {
            throw new BadRequestException();
        }

        $result = (new LxdCreateInstance($this->lxd))->dispatch(
            $instance['name'],
            $instance['fingerprint'], # fingerprint
            $instance['memory'],
            $instance['disk'],
            $instance['cpu'],
            $instance['eth0'],
        );

        if ($result->success()) {
            $this->Flash->success(__('The instance has been created.'));

            return $this->renderJson(['data' => $result->data()]);
        }

        Log::error($result->error('message'));
    
        return $this->renderJson([
            'error' => [
                'message' => $result->error('message'),
                'code' => 400,
            ]
        ], 400);
    }

    /**
     * If the container is running it will be stopped
     *
     * @internal this will migrate with the same IP address if a static IP address set.
     *
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function migrate(string $instance)
    {
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        if ($this->request->is('post')) {
            $this->request->header('accept', 'application/json');
            
            // Carry out pre checks
            $result = (new LxdPreMigrate($this->lxd))->dispatch($instance, $this->request->data('host'));

            if (! $result->success()) {
                $this->Flash->error(__($result->error('message')));

                return $this->renderJson($result->toArray(), $result->error('code'));
            }
            
            $result = (new LxdMigrate($this->AutomatedBackup))->dispatch($instance, Lxd::host(), $this->request->data('host'), (bool) $this->request->data('clone'));
       
            if ($result->success()) {
                $this->Flash->success(__('The instance has been migrated.'));

                return $this->renderJson([
                    'data' => $result->data()
                ]);
            }

            $this->Flash->error(__('The instance could not be migrated.'));
            Log::error($result->error('message'));
           
            return $this->renderJson([
                'error' => [
                    'message' => $result->error('message'),
                    'code' => 500
                ]
            ], 500);
        }

        $host = $this->Session->read('Lxd.host');
        // rename and remove current host
        $collection = collection(Lxd::hosts())->map(function ($name, $ip) {
            return "{$name} ({$ip})";
        })->filter(function ($name, $ip) use ($host) {
            return $ip !== $host;
        });

        $this->set('usingBridgedNetwork', in_array('nuber-bridged', $info['profiles'] ?? []));

        $this->set('hasVolumes', $this->hasVolumes($info));
        $this->set('hosts', $collection->toArray());
    }

    /**
     * Checks the info for volume, this is used by migrate to check if a volume is attached
     * because if so, it wont start on the remote host.
     *
     * @param array $info
     * @return boolean
     */
    private function hasVolumes(array $info) : bool
    {
        foreach ($info['devices'] ?? [] as $device => $config) {
            if (Text::startsWith('bsv', $device)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This is used when changing state on index page
     *
     * @param string $instance
     */
    public function row($instance)
    {
        $this->layout = false;
        $info = $this->lxd->instance->info($instance);
        $info['state'] = $this->lxd->instance->state($instance);
        $this->set('instance', LxdMeta::add($info));
    }

    /**
     * Instances cannot be deleted if they have snapshots
     *
     * @param string $instance
     * @return \Origin\Http\Response|null
     */
    public function destroy($instance)
    {
        $info = $this->loadInstanceMeta($instance);
        if ($info instanceof Response) {
            return $info;
        }

        if ($this->request->is('post')) {
            $result = (new LxdDestroyInstance($this->lxd, $this->AutomatedBackup))->dispatch($instance);

            if ($result->success()) {
                $this->Flash->success(__('The instance was destroyed.'));

                return $this->redirect('/instances');
            }

            $this->Flash->error(__('An error occured.')); // internal error
        }
    }

    /**
     * @internal failure to start could be a configuration issue, for example if two networks have the same IPv4 address
     *
     * @param string $instance
     * @return void
     */
    public function start($instance)
    {
        $this->request->header('accept', 'application/json');

        $result = (new LxdStartInstance($this->lxd))->dispatch($instance);

        $this->renderJson($result, $result->error('code') ?: 200);
    }

    public function stop($instance)
    {
        $this->request->header('Accept', 'application/json');

        $response = $this->lxd->operation->wait(
            $this->lxd->instance->stop($instance)
        );

        if ($response['err']) {
            return $this->renderJsonFromError($response);
        }

        $this->renderJson(['data' => []]);
    }

    public function restart($instance)
    {
        $this->request->header('Accept', 'application/json');

        $response = $this->lxd->operation->wait(
            $this->lxd->instance->restart($instance)
        );

        if ($response['err']) {
            return $this->renderJsonFromError($response);
        }

        sleep(2); // wait for IP address

        $this->renderJson(['data' => []]);
    }

    /**
     * Gets the real disk usage
     *
     * @param string $name
     * @return void
     */
    public function disk_usage(string $name)
    {
        $this->request->header('Accept', 'application/json');

        $result = (new LxdDiskUsage($this->lxd))->dispatch($name);
        
        $used = '0%';
        if ($result->success()) {
            $used = $result->data('percent') . '%';
        }

        return $this->renderJson([
            'usage' => $used
        ]);
    }

    /**
     * Gets the fingerprint for an alias, if the alias does not
     * exist it will download the image
     *
     * @param string $name
     * @return string|null
     */
    private function getFingerprint(string $name): ?string
    {
        $alias = new Alias();

        return in_array($name, $alias->list(['recursive' => 0])) ? $alias->get($name)['target'] : null;
    }

    private function isFingerprint(string $string): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/', $string);
    }
}
