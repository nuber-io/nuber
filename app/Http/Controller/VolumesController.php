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

use App\Form\VolumeForm;
use Origin\HttpClient\Exception\ConnectionException;

class VolumesController extends ApplicationController
{
    public function index()
    {
        $volumes = [];
        
        try {
            $volumes = $this->lxd->volume->list();
        } catch (ConnectionException $exception) {
            $this->Flash->error(__('Unable to connect to the host.'));
        }

        $this->set(compact('volumes'));
    }

    public function create()
    {
        $volumeForm = VolumeForm::new();

        if ($this->request->is(['post'])) {
            $volumeForm = VolumeForm::patch($volumeForm, $this->request->data());
            $volumeForm->validateCreate($this->lxd->volume->list(['recursive' => 0]));
           
            if ($volumeForm->validates()) {
                $driver = $this->lxd->host->info()['environment']['storage'];
                $this->lxd->volume->create($volumeForm->name, [
                    'size' => $volumeForm->size,
                    'driver' => $driver
                ]);
                $this->Flash->success(__('The volume was created.'));
                $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The volume could not be created.'));
            }
        }

        $this->set(compact('volumeForm'));
    }

    public function rename(string $volume)
    {
        $volumeForm = VolumeForm::new();
      
        // check exists or trigger 404
        $info = $this->lxd->volume->get($volume);

        if ($this->request->is(['post'])) {
            $volumeForm = VolumeForm::patch($volumeForm, $this->request->data());
            $volumeForm->validateRename($this->lxd->volume->list(['recursive' => 0]));
            
            if ($volumeForm->validates()) {
                $this->lxd->volume->rename($volume, $volumeForm->name);
                $this->Flash->success(__('The volume has been renamed.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to rename the volume.'));
            }
        }
        $this->set('name', $volume);
        $this->set(compact('volumeForm'));
    }

    public function detach(string $instance, string $volume)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('post');

        $info = $this->lxd->instance->info($instance);

        if ($info['type'] === 'virtual-machine' && $info['status'] === 'Running') {
            return $this->renderJson([
                'error' => [
                    'message' => __('Virtual machines need to be stopped before you can change port forwarding configuration.'),
                    'code' => 400
                ]
            ], 400);
        }
       
        $volumeForm = VolumeForm::new([
            'name' => $volume,
            'instance' => $instance
        ]);

        if ($volumeForm->validates()) {
            $response = $this->lxd->operation->wait(
                $this->lxd->volume->detach($volumeForm->instance, $volumeForm->name)
            );

            if ($response['err']) {
                return $this->renderJsonFromError($response);
            }

            $this->Flash->success(__('The volume was detached.'));

            return $this->renderJson([
                'data' => [
                    'instance' => $instance,
                    'volume' => $volume
                ]
            ]);
        }

        $this->renderJson(['error' => ['message' => 'Bad Request','code' => 400]], 400);
    }

    public function delete($name)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('delete');

        $info = $this->lxd->volume->get($name);
        if (! empty($info['used_by'])) {
            return $this->renderJson([
                'error' => [
                    'message' => __('Volume is in use'),
                    'code' => 500
                ]
            ], 500);
        }

        $this->lxd->volume->delete($name);

        $this->Flash->success(__('The volume was deleted')); // display alert after redirect

        $this->renderJson(['data' => []]);
    }
}
