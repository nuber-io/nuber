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

use Origin\Http\Response;

class DevicesController extends ApplicationController
{
    /**
     * Remove device
     *
     * @param string $instance
     * @param string $device
     * @return \Origin\Http\Response
     */
    public function delete(string $instance, string $device) : Response
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('delete');
        
        $this->lxd->device->remove($instance, $device);

        $this->Flash->success(__('The port forwarding configuration has been deleted.'));

        return $this->renderJson(['data' => []]);
    }
}
