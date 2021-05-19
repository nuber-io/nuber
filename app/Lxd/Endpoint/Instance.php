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
namespace App\Lxd\Endpoint;

use App\Lxd\Lxd;
use Origin\Log\Log;
use App\Lxd\Endpoint;
use Origin\Core\Config;
use InvalidArgumentException;

/**
 * LXC Command help
 *
 * [ ] alias       Manage command aliases
 * [ ] cluster     Manage cluster members
 * [ ] config      Manage container and server configuration options
 * [ ] console     Attach to container consoles
 * [X] copy        Copy containers within or in between LXD instances
 * [X] delete      Delete containers and snapshots
 * [ ] exec        Execute commands in containers
 * [ ] file        Manage files in containers
 * [ ] help        Help about any command
 * [-] image       Manage images
 * [X] info        Show container or server information
 * [ ] launch      Create and start containers from images
 * [ ] list        List containers
 * [ ] move        Move containers within or in between LXD instances
 * [ ] network     Manage and attach containers to networks
 * [ ] operation   List, show and delete background operations
 * [ ] profile     Manage profiles
 * [ ] publish     Publish containers as images
 * [ ] remote      Manage the list of remote servers
 * [X] rename      Rename containers and snapshots
 * [ ] restart     Restart containers
 * [ ] restore     Restore containers from snapshots
 * [ ] snapshot    Create container snapshots
 * [X] start       Start containers
 * [X] stop        Stop containers
 * [ ] storage     Manage storage pools and volumes
 * [ ] version     Show local and remote versions
 */
class Instance extends Endpoint
{
    /**
     * Gets a list of instances
     *
     * @param array $options
     *  - recursive: recursion level e.g. 0 or 2
     *  - filter: filter name eq "my container" , config.image.os eq ubuntu
     * @return array
     */
    public function list(array $options = []): array
    {
        $options += ['recursive' => 2, 'filter' => null];

        $response = $this->sendGetRequest('/instances', [
            'query' => [
                'recursion' => $options['recursive'],
                'filter' => $options['filter']
            ]
        ]);

        return $this->removeEndpoints(
            $response,
            '/1.0/instances/'
        );
    }

    /**
     * Gets information
     *
     * @param string $name
     * @return array
     */
    public function info(string $name): array
    {
        return $this->sendGetRequest("/instances/{$name}");
    }

    /**
     * Updates
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public function update(string $name, array $options)
    {
        $this->sendPatchRequest("/instances/{$name}", [
            'data' => $options
        ]);
    }

    /**
    * Deletes an instance.
    *
    * TODO: Possible ZFS issues if there is background backup in progress, if so need to check this
    *
    * @param string $name instance name
    * @return string
    */
    public function delete(string $name): string
    {
        $response = $this->sendDeleteRequest("/instances/{$name}");

        return $response['id'];
    }

    /**
     * Creates an instance , this is the new simplified method to only use code that
     * is needed by the webui rather that trying to implement every feature.
     *
     * @example
     * $ lxc launch images:alpine/3.11/amd64 alpine
     *
     * @param string $image alias name or fingerprint
     * @param string $name
     * @param array $options
     * @return string
     */
    public function create(string $image, string $name, array $options = []): string
    {
        if (! preg_match('/^[a-z][a-z0-9-]{1,61}$/i', $name)) {
            throw new InvalidArgumentException('Invalid instance name');
        }

        $options['name'] = $name;

        if (in_array($image, $this->alias->list(['recursive' => 0]))) {
            $info = $this->alias->get($image);
            $fingerprint = $info['target'];
        } else {
            $info = $this->image->get($image);
            $fingerprint = $info['fingerprint'];
        }
       
        $options['source'] = [
            'type' => 'image',
            'fingerprint' => $fingerprint
        ];

        $response = $this->sendPostRequest('/instances', [
            'data' => $options
        ]);

        return $response['id'];
    }

    /**
     * Updates configuration for an instance
     *
     * @param string $instance
     * @param array $options
     * @return
     */
    public function edit(string $instance, array $options)
    {
        return $this->sendPatchRequest("/instances/{$instance}", [
            'data' => $options
        ]);
    }

    /**
     * Run a remote command using `exec`, so shell vars, patterns and redirects wont work.
     *
     * @see https://lxd.readthedocs.io/en/latest/rest-api/#10instances
     *
     * @param string $name
     * @param string $command
     * @param array $options Option keys are
     *   - environment: an array on env vars e.g. HOME,LANG,PATH,USER, TERM
     *   - user: default: 0
     *   - grp: default: 0
     *   NON LXC API Option
     *   - log: default: default:false. Logs output
     * @return array
     */
    public function exec(string $name, string $command, array $options = []): array
    {
        $options += [
            // cmd line options
            'environment' => [
                'TERM' => 'xterm', // ls /lib/terminfo/x/
                'LANG' => 'C.UTF-8'
            ],
            'user' => 0,
            'grp' => 0,
            'cwd' => ''
        ];

        if ($options['user'] === 0) {
            $options['environment'] = array_merge($options['environment'], [
                'HOME' => '/root',
                'USER' => 'root'
            ]);
        }

        $options['command'] = $this->split($command);

        $response = $this->sendPostRequest("/instances/{$name}/exec", [
            'data' => $options
        ]);

        $response = $this->removeEndpoints(
            $response,
            '/1.0/instances/'
        );

        // has to return an array as this might include meta data
        return $response;
    }

    /**
     * The execute setup for a the console window
     *
     * @param string $name
     * @param string $command
     * @param array $options
     * @return array
     */
    public function execInteractive(string $name, string $command, array $options = []): array
    {
        $options['record-output'] = false;
        $options['wait-for-websocket'] = true;
        $options['interactive'] = true;

        return $this->exec($name, $command, $options);
    }

    /**
     * Executes a command and waits for it to finish then returns the output
     * @example lxc exec c1 -- ls -lah
     *
     * @param string $name
     * @param string $command
     * @param array $options
     * @return string
     */
    public function execCommand(string $name, string $command, array $options = []): string
    {
        $options['record-output'] = true;
        $options['wait-for-websocket'] = false;
        $options['interactive'] = false;

        $response = $this->exec($name, $command, $options);

        $response = $this->operation->wait($response['id']);

        $out = '';
        foreach ($response['metadata']['output'] as $log) {
            $out .= $this->log->get($name, $log);
        }

        return $out;
    }

    /**
     * Splits a command string into an array, paying attention to matching single or double quotes
     *
     * @param string $string
     * @return array
     */
    protected function split(string $string): array
    {
        $out = [];
        if (preg_match_all('/(\'.*?\'|".*?"|\S+)/m', $string, $matches)) {
            $out = $matches[0];
        }

        return $out;
    }

    /**
    * Gets the state of an instance
    *
    * @param string $name
    * @return array
    */
    public function state(string $name): array
    {
        return $this->sendGetRequest("/instances/{$name}/state");
    }

    /**
     * Renames an instance
     *
     * @param string $name
     * @param string $newname
     * @return string $uuid
     */
    public function rename(string $name, string $newname): string
    {
        $response = $this->sendPostRequest("/instances/{$name}", [
            'data' => [
                'name' => $newname
            ]
        ]);

        return $response['id'];
    }

    /**
     * Creates a local copy of the container, snapshots are not copied. However for cloning
     * a container I dont think snapshots should be cloned by default, whilst with migrations
     * I think it is the opposite.
     *
     * @internal An issue (4.0 LTS release ) with copying containers that have snapshots in ZFS, this will be fixed
     * in next release in Jan 2021. Error: Failed to run: zfs set quota=xxxxx cant run on snapshots
     *
     * @param string $instance
     * @param string $name
     * @return string
     */
    public function copy(string $instance, string $name): string
    {
        $info = $this->info($instance);
        $response = $this->sendPostRequest('/instances', [
            'data' => [
                'name' => $name,
                'architecture' => $info['architecture'],
                'type' => $info['type'],
                'profiles' => $info['profiles'],
                'config' => $this->removeVolatile($info['config']),
                'source' => [
                    'type' => 'copy',
                    'certificate' => null,
                    'base-image' => $info['config']['volatile.base_image'],
                    'source' => $instance,
                    'live' => false,
                    /**
                     * this needs to be permenent due to a bug in LXD, this wont be resolved until end
                     * of Jan 2021, and then need backwards compatability.
                     */
                    'instance_only' => true  // ignore snapshots
                ],
                'devices' => isset($info['devices']) ?  $this->removeIP($info['devices']) : null,
                'ephemeral' => $info['ephemeral'],
                'stateful' => false
               
            ]
        ]);

        return $response['id'];
    }

    /**
     * To AID copying
     *
     * @param array $devices
     * @return array|null !golang errors if return an empty array
     */
    private function removeIP(array $devices): ?array
    {
        if (isset($devices['eth0'])) {
            unset($devices['eth0']['ipv4.address'],$devices['eth0']['ipv6.address']);
        }

        return $devices ?: null;
    }

    /**
    * Migrate an instance (copy to remote)
    *
    * @internal Migration will fail if you try to move from 4.9 (latest) to 4.0 (stable) due to
    * new volailite keys that have been added. There is a current bug which means device information
    * is lost. See https://github.com/lxc/lxd/issues/8283
    *
    * By default snapshots should be copied across during the migration process.
    *
    * @example
    *
    * $ lxc remote add server2 192.168.1.100
    * $ lxc remote list
    * $ lxc copy c1 server2:c1 --debug
    *
    * @see https://lxd.readthedocs.io/en/latest/rest-api/#10instances
    * @link https://stgraber.org/2016/04/12/lxd-2-0-remote-hosts-and-container-migration-612/
    *
    * @param string $name container name or contiainer/snapshot e.g. lxc copy apache/snapshot-0
    * @param string $host IP address
    * @param boolean $clone If cloning, volatilte keys will be stripped so it gets a different UUID etc.
    * @return string
    */
    public function migrate(string $name, string $host, bool $clone = false): string
    {
        $remoteName = $name;
        $url = "/instances/{$name}";

        if (strpos($name, '/') !== false) {
            list($remoteName, $snapshot) = explode('/', $name);
            $instance = $this->snapshot->get($remoteName, $snapshot);
            $url = "/instances/{$remoteName}/snapshots/{$snapshot}";
        } else {
            $instance = $this->info($name);
        }

        // to migrate between cluster members ?target=<member> is required but debug shows target key
        $response = $this->sendPostRequest($url, [
            'data' => [
                'name' => $remoteName,
                'migration' => true,
                'live' => false, // requires CRIU,
                'instance_only' => false,
                'container_only' => false, // TODO: lxc copy uses this, but it did not do anything, had to add it below.
                'target' => null,
            ]
        ]);
     
        // Trying to move to older installations will cause volatile.uuid error
        $settings = [
            'name' => $remoteName,
            'architecture' => $instance['architecture'],
            'type' => $instance['type'] ?? null,
            'profiles' => $instance['profiles'],
            'config' => $clone ? $this->removeVolatile($instance['config']) : $instance['config'], // as we are moving keep volailte entries
            'source' => [
                'type' => 'migration',
                'mode' => 'pull',
                'operation' => 'https://' . Lxd::host() .':8443/1.0/operations/' . $response['id'],
                'certificate' => $this->host->certificate(),
                'secrets' => $response['metadata'],
                'instance_only' => false
            ],
            'devices' => $instance['devices'] ? $this->changeProxyListen($instance['devices'], $this->hostName, $host) : null,
            'ephemeral' => $instance['ephemeral'],
            'stateful' => false
        ];
       
        return (new Instance(['host' => $host]))->startMigration($remoteName, $settings);
    }

    /**
     * Changes used in the connect string e.g. tcp:{$instanceIp}:123
     *
     * @param array $devices
     * @param string $from
     * @param string $to
     * @return array
     */
    private function changeProxyListen(array $devices, string $from, string $to): array
    {
        foreach ($devices as &$device) {
            if ($device['type'] !== 'proxy') {
                continue;
            }
            $device['listen'] = str_replace($from, $to, $device['listen']); // tcp:{$instanceIp}:123
        }

        return $devices;
    }

    /**
     * Not sure if need to keep this for live migration, but been running into issues
     */
    private function removeVolatile(array $config)
    {
        foreach ($config as $key => $value) {
            if (substr($key, 0, 9) === 'volatile.' && $key !== 'volatile.base_image') {
                unset($config[$key]);
            }
        }

        return $config;
    }

    /**
     * Starts the migration on the remote machine
     *
     * @param string $name
     * @param array $settings
     * @return string
     */
    public function startMigration(string $name, array  $settings): string
    {
        $response = $this->sendPostRequest('/instances', [
            'data' => $settings,
            'timeout' => 0
        ]);

        return $response['id'];
    }

    /**
    * Publishes an image from an instance
    * Remember to run apt-get clean or similar
    *
    * @param string $instance
    * @param array $options The following option keys
    *  - alias : adds an alias to image
    *  - public: whether the image can be downloaded by untrusted users
    * @return string
    */
    public function publish(string $instance, array $options = []): string
    {
        $options += ['alias' => null, 'public' => false];

        $requestOptions = [
            'aliases' => [],
            'source' => [
                'type' => 'container',
                'name' => $instance
            ],
            'public' => $options['public']
        ];

        if ($options['alias']) {
            $requestOptions['aliases'][] = [
                'name' => $options['alias'],
                'description' => null
            ];
        }

        $response = $this->sendPostRequest('/images', [
            'data' => $requestOptions
        ]);

        return $response['id'];
    }

    /**
     * Stops an instance
     *
     * @param string $name
     * @param array $options Options keys are
     *  - timeout: default 30
     *  - force: default true
     *  - stateful: true
     * @return string uuid
     */
    public function stop(string $name, array $options = []): string
    {
        return $this->changeState('stop', $name, $options);
    }

    /**
    * Starts an instance
    *
    * @param string $name
    * @param array $options Options keys are
    *  - timeout: default 30
    *  - force: default true
    *  - stateful: true
    * @return string uuid
    */
    public function start(string $name, array $options = []): string
    {
        return $this->changeState('start', $name, $options);
    }

    /**
    * Restart an instance
    *
    * @param string $name
    * @param array $options Options keys are
    *  - timeout: default 30
    *  - force: default true
    *  - stateful: true
    * @return string uuid
    */
    public function restart(string $name, array $options = []): string
    {
        return $this->changeState('restart', $name, $options);
    }

    /**
    * Freeze an instance
    *
    * @param string $name
    * @param array $options Options keys are
    *  - timeout: default 30
    *  - force: default true
    *  - stateful: true
    * @return string uuid
    */
    public function freeze(string $name, array $options = []): string
    {
        return $this->changeState('freeze', $name, $options);
    }

    /**
    * Unfreeze an instance
    *
    * @param string $name
    * @param array $options Options keys are
    *  - timeout: default 30
    *  - force: default true
    *  - stateful: true
    * @return string uuid
    */
    public function unfreeze(string $name, array $options = []): string
    {
        return $this->changeState('unfreeze', $name, $options);
    }

    /**
     * Workhorse for the different actions
     *
     * @param string $action
     * @param string $name
     * @param array $options
     * @return string
     */
    private function changeState(string $action, string $name, array $options = []): string
    {
        $options += ['timeout' => 30, 'force' => true, 'stateful' => false];

        $response = $this->sendPutRequest("/instances/{$name}/state", [
            'data' => [
                'action' => $action,
                'force' => $options['force'],
                'stateful' => $options['stateful'],
                'timeout' => $options['timeout']
            ]
        ]);

        return $response['id'];
    }
}
