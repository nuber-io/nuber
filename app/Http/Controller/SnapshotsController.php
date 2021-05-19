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

use Origin\Text\Text;
use App\Service\Lxd\LxdRestoreSnapshot;

class SnapshotsController extends ApplicationController
{
    public function delete(string $instance, string $snapshot)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('delete');

        $response = $this->lxd->operation->wait(
            $this->lxd->snapshot->delete($instance, $snapshot)
        );

        if ($response['err']) {
            return $this->renderJsonFromError($response);
        }

        $message = Text::startsWith('backup-', $snapshot) ? 'The backup was deleted.' : 'The snapshot was deleted.';
        $this->Flash->success(__($message)); // display alert after redirect

        $this->renderJson(['data' => []]);
    }

    /**
     * @param string $instance
     * @param string $snapshot
     * @return Response
     */
    public function restore(string $instance, string $snapshot)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('post');
       
        $result = (new LxdRestoreSnapshot($this->lxd))->dispatch($instance, $snapshot);

        if ($result->success()) {
            $this->Flash->success(__('Your instance has been restored.'));

            return $this->renderJson([
                'data' => []
            ]);
        }

        return $this->renderJson($result, $result->error('code'));
    }
}
