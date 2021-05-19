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
?>
<style>
  .command {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    padding: 10px;
    background: black;
    color: white;
    border-radius: .25rem;
  }
  .command p {
    margin: 0px;
  }
</style>
<!-- Modal -->
<div class="modal fade" id="hostConfig" tabindex="-1" role="dialog" aria-labelledby="hostConfigLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="hostConfigLabel"><?= __('Host Configuration Instructions') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <p class="mb-2"><?= __('To enable remote access you will need to set the LXD host to listen on port 8843, SSH into the LXD host and run the following command.') ?> </p>

          <div class="command">
            <p>$ lxc config set core.https_address "[::]:8443"</p>
          </div>



        <p class="mt-2 mb-2"><?= __('So that Nuber can communicate with the LXD host, a TLS certificate is used, during the install process a certificate was generated. So that nuber can upload the certificate to the LXD host you will need to set a password.') ?><p>

        <div class="command">
           <p>$ lxc config set core.trust_password "<?= $secret ?>"</p>
        </div>

        <p class="mt-2 mb-2"><?= __('For a production setup, it is recommended that you remove the password after the host has been added to prevent brute-force attacks.') ?><p>

        <div class="command">
          <p>$ lxc config unset core.trust_password</p>
        </div>

        <p class="mt-2 mb-2"><?= __('If you prefer to manually set the certificate without using a password then download the certificate and save this on the LXD host as nuber.crt, then run the following command. Remember to leave the password field empty if you are doing it this way.') ?><p>

        <div class="command">
          <p>$ lxc config trust add nuber.crt</p>
        </div>
       
      </div>
      <div class="modal-footer">
        <a href="/hosts/certificate" class="btn btn-warning" ><?= __('Download certificate') ?></a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Close') ?></button>
      </div>
    </div>
  </div>
</div>