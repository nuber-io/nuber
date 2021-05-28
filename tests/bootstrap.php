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
/**
 * Custom bootstrap to setup testing on a LXD server
 */

use App\Lxd\Lxd;
use App\Lxd\LxdClient;

use App\Service\Lxd\LxdCreateInstance;

require dirname(__DIR__, 1) . '/config/bootstrap.php';

// Set the test host from phpunit.xml
define('LXD_HOST', env('LXD_HOST'));
define('LXD_HOST_2', env('LXD_HOST_2'));

Lxd::host(LXD_HOST);

$client = new LxdClient(LXD_HOST);

fwrite(STDOUT, "Nuber pre-test optimization\n");

if ($client->certificate->status() === 'untrusted') {
    fwrite(STDOUT, "> adding certificate\n");
    // lxc config set core.trust_password "xxx"
    $client->certificate->add(env('LXD_PASSWORD'));
}

DEFINE('ARCH', (bool) preg_match('/aarch64/', php_uname()) === true ? 'arm64' : 'amd64');

// Create a fake bridged network interface for testing, this is a bridged connection to fake it.
if (! in_array('nuber-bridged', $client->network->list(['recursive' => 0]))) {
    $client->network->create('nuber-bridged', [
        'description' => NUBER_VIRTUAL_NETWORK,
        'config' => [
            'ipv4.address' => '10.254.254.1/24',
            'ipv4.nat' => 'true',
            'ipv6.address' => 'none',
            'ipv6.nat' => 'true'
        ]
    ]);
}

// Prefetch image if it does not exist
if (! in_array('ubuntu', $client->alias->list(['recursive' => 0]))) {
    fwrite(STDOUT, "> downloading ubuntu image\n");

    $uuid = $client->image->fetch('ubuntu/focal/' . ARCH, [
        'alias' => 'ubuntu'
    ]);

    $response = $client->operation->wait($uuid);

    if (! empty($response['err'])) {
        throw new Exception($response['err']);
    }
}

$cleanup = function () use ($client) {
    $info = $client->instance->info('ubuntu-test');

    if ($info['status'] === 'Running') {
        fwrite(STDOUT, "> stopping test container\n");
        $client->operation->wait(
            $client->instance->stop('ubuntu-test')
        );
    }
    fwrite(STDOUT, "> destroying test container\n");
    $client->operation->wait(
        $client->instance->delete('ubuntu-test')
    );
};

  // Clean up last test container if script if CTRL-C was used
  if (in_array('ubuntu-test', $client->instance->list(['recursive' => 0]))) {
      $cleanup();
  }

// Create new container
fwrite(STDOUT, "> creating test container\n");
$fingerprint = $client->alias->get('ubuntu')['target'];
$result = (new LxdCreateInstance($client))->dispatch('ubuntu-test', $fingerprint, '1GB', '5GB', (string) 1, 'vnet0');

if (! $result->success()) {
    throw new RuntimeException('Error creating test instance');
}

fwrite(STDOUT, "\n");

register_shutdown_function(function () use ($cleanup, $client) {
    // Clean up last test container
    if (in_array('ubuntu-test', $client->instance->list(['recursive' => 0]))) {
        fwrite(STDOUT, "\nNuber post test cleanup\n");
        $cleanup();
    }
});
