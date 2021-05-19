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
 * @property \App\Model\User $User
 */
class UsersController extends ApplicationController
{
    public function login()
    {
        $this->set('title', __('nuber login'));
        $this->layout = 'form';
        
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->login($user);

                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error(__('Incorrect username or password.'));
        }
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
