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
use App\Form\ImageForm;
use Origin\Cache\Cache;
use Origin\Collection\Collection;
use App\Service\Lxd\LxdRemoteImageList;
use App\Lxd\Endpoint\Image as ImageEndpoint;
use Origin\Http\Exception\BadRequestException;
use App\Lxd\Endpoint\Operation as OperationEndpoint;
use Origin\HttpClient\Exception\ConnectionException;

class ImagesController extends ApplicationController
{
    public function index()
    {
        $images = $runningOperations = [];

        try {
            $operations = (new OperationEndpoint())->list();

            foreach ($operations['running'] ?? [] as $operation) {
                if (in_array($operation['description'], ['Deleting image','Downloading image'])) {
                    $runningOperations[] = $operation;
                }
            }
            $images = (new ImageEndpoint())->list();
        } catch (ConnectionException $exception) {
            $this->Flash->error(__('Unable to connect to the host'));
        }

        $this->set(compact('runningOperations', 'images'));
    }

    public function create()
    {
        $instances = $this->lxd->instance->list(['recursive' => 0,'filter' => 'status ne Running']);

        $instances = (new Collection($instances))->reject(function ($instance) {
            return Text::startsWith('nuber-', $instance);
        })->toArray();

        $imageForm = ImageForm::new();

        if ($this->request->is(['post'])) {
            $imageForm = ImageForm::patch($imageForm, $this->request->data());
            
            //$cloneForm->addExisting($this->lxd->instance->list(['recursive' => 0]));

            if (! in_array($imageForm->instance, $instances)) {
                throw new BadRequestException('Bad Request');
            }

            $errorMessage = __('Unable to create the image.');
            if ($imageForm->validates()) {
                $response = $this->lxd->operation->wait(
                    $this->lxd->instance->publish($imageForm->instance, [
                        'alias' => $imageForm->name,
                    ])
                );
              
                if (empty($response['err'])) {
                    $this->Flash->success(__('Your Image has been created.'));

                    return $this->redirect(['action' => 'index']);
                }
                if (Text::contains('The image already exists', $response['err'])) {
                    $errorMessage = __('The image already exists.');
                }
                // e.g. Cannot export a running instance as an image
            }
            
            $this->Flash->error($errorMessage);
        }

        $this->set(compact('imageForm'));
        $this->set('instances', array_combine($instances, $instances));
    }

    /**
     * Creates an image localy from remote
     *
     */
    public function download()
    {
        if ($this->request->is(['post'])) {
            $this->lxd->image->fetch($this->request->data('image'), [
                'alias' => $this->request->data('image')
            ]);

            return $this->redirect(['action' => 'index']);
        }

        if (! Cache::exists('remoteImages')) {
            $result = (new LxdRemoteImageList())->dispatch();
            $images = $result->success() ? $result->data() : [];
            Cache::write('remoteImages', $images, [
                'duration' => '1 day'
            ]);
        }

        $this->set('remoteImages', $images ?? Cache::read('remoteImages'));
    }

    public function progress($uuid)
    {
        $this->request->header('Accept', 'application/json');

        $operation = $this->lxd->operation->get($uuid);
    
        $percent = 0;
        if (preg_match('/(?<percent>[0-9]{1,3}+)%/i', $operation['metadata']['download_progress'] ?? '', $matches)) {
            $percent = $matches['percent'];
        }
        $operation['percent'] = $operation['status_code'] === 200 ? 100 : $percent;
  
        $this->renderJson($operation);
    }

    public function delete(string $image)
    {
        $this->request->header('Accept', 'application/json');
        $this->request->allowMethod('delete');

        $response = $this->lxd->operation->wait(
            $this->lxd->image->delete($image)
        );

        if ($response['err']) {
            return $this->renderJsonFromError($response);
        }

        $this->Flash->success(__('The image was deleted')); // TODO: check if this is still required.
        $this->renderJson(['data' => []]);
    }
}
