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

/**
 * @property \App\Model\AutomatedBackup $AutomatedBackup
 */
class AutomatedBackupsController extends ApplicationController
{
    public function delete(string $id)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('delete');

        $automatedBackup = $this->AutomatedBackup->get($id);

        if (! $this->AutomatedBackup->delete($automatedBackup)) {
            return $this->renderJson([
                'error' => [
                    'message' => 'Error deleting automated backup',
                    'code' => 500
                ]
            ], 500);
        }
 
        $this->Flash->success(__('The scheduled backup was deleted.'));

        $this->renderJson(['data' => []]);
    }
}
