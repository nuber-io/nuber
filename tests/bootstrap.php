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

if ($client->certificate->status() === 'untrusted') {
    fwrite(STDOUT, "Not trusted... attempting to add certificate\n");
    // lxc config set core.trust_password "xxx"
    $client->certificate->add(env('LXD_PASSWORD'));
}

DEFINE('ARCH', (bool) preg_match('/aarch64/', php_uname()) === true ? 'arm64' : 'amd64');

// Prefetch image if it does not exist
if (! in_array('ubuntu', $client->alias->list(['recursive' => 0]))) {
    fwrite(STDOUT, "ubuntu image not found... Downloading\n");

    $uuid = $client->image->fetch('ubuntu/focal/' . ARCH, [
        'alias' => 'ubuntu'
    ]);

    $response = $client->operation->wait($uuid);

    if (! empty($response['err'])) {
        throw new Exception($response['err']);
    }
}

$fingerprint = $client->alias->get('ubuntu')['target'];

if (in_array('ubuntu-test', $client->instance->list(['recursive' => 0]))) {
    $info = $client->instance->info('ubuntu-test');

    if ($info['status'] === 'Running') {
        $client->operation->wait(
            $client->instance->stop('ubuntu-test')
        );
    }
    $client->operation->wait(
        $client->instance->delete('ubuntu-test')
    );
}

$result = (new LxdCreateInstance($client))->dispatch('ubuntu-test', $fingerprint, '1GB', '5GB', (string) 1);
