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

        $info = $this->lxd->instance->info($instance);

        if ($info['type'] === 'virtual-machine' && $info['status'] === 'Running') {
            return $this->renderError(__('Virtual machines need to be stopped before you can change port forwarding configuration.'), 400);
        }
      
        $this->lxd->device->remove($instance, $device);

        /**
         * Unable to check without waiting for 1 second after removing
         */
        sleep(1);
        $info = $this->lxd->instance->info($instance);
       
        if (empty($info['devices'][$device])) {
            $this->Flash->success(__('The port forwarding configuration has been deleted.'));

            return $this->renderJson(['data' => []]);
        }

        return $this->renderError(__('The port forwarding configuration could not be deleted.'), 500);
    }

    private function renderError(string $message, int $code = 500)
    {
        return $this->renderJson([
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ], $code);
    }
}
