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

use Origin\Log\Log;
use App\Form\LoginForm;

/**
 * @property \App\Model\User $User
 */
class UsersController extends ApplicationController
{
    public function login()
    {
        $this->set('title', __('nuber login'));
        $this->layout = 'form';

        // set a custom header to identify login screens for ajax stuff
        $this->response->header('X-Action', 'login');
        if (! debugEnabled()) {
            $this->response->header('Content-Security-Policy', "default-src 'self'");
        }
  
        $loginForm = LoginForm::new();
        
        if ($this->request->is('post')) {
            $loginForm = LoginForm::patch($loginForm, $this->request->data(), [
                'fields' => [
                    'email','password'
                ]
            ]);
            if ($loginForm->validates()) {
                $user = $this->Auth->identify();
                if ($user) {
                    $this->Auth->login($user);
                    // To prevent not found errors when using multiple hosts, send to instances
                    return $this->redirect('/instances');
                } else {
                    Log::warning('Authentication failure host={host} user={user}', [
                        'host' => $this->request->ip(),
                        'user' => $this->request->data('email')
                    ]);
                }
                $this->Flash->error(__('Incorrect username or password.'));
            } else {
                $this->Flash->error(__('Please check the form for errors.'));
            }
        }

        $this->set(compact('loginForm'));
    }

    public function change_password()
    {
        $user = $this->User->get($this->Auth->user('id'));
     
        /**
         * Add rule here as this is non existant and therefore not modified
         */
        $this->User->validate('current_password', [
            'required',
            [
                'rule' => ['passwordMatch', $user->password],
                'message' => __('Incorrect password')
            ]
        ]);

        if ($this->request->is(['post'])) {
            $user = $this->User->patch($user, $this->request->data(), [
                'fields' => [
                    'current_password',
                    'password',
                    'password_confirm'
                ]
            ]);

            if ($this->User->save($user)) {
                $this->Flash->success(__('Your password has been changed.'));

                return $this->redirect('/change-password'); // clear form
            }

            $this->Flash->error(__('Unable to change your password.'));
        } else {
            $user->password = null;
        }
       
        $this->set('user', $user);
    }

    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    public function profile()
    {
        $user = $this->User->get($this->Auth->user('id'));

        if ($this->request->is(['post'])) {
            $user = $this->User->patch($user, $this->request->data(), [
                'fields' => ['first_name','last_name','email']
            ]);
      
            if ($this->User->save($user)) {
                $this->Flash->success(__('Your profile has been updated.'));
                $this->Auth->login($user);
            } else {
                $this->Flash->error(__('Your profile could not be updated.'));
            }
        }
        $this->set('user', $user);
    }
}
