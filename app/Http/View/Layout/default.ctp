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
 * @var \App\Http\View\ApplicationView $this
 */
use App\Lxd\Lxd;
use Origin\Core\Config;

?>
<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="refresh" content="<?= Config::read('Session.timeout') + 1 ?>">
  <meta name="csrf-token" content="<?= $this->request->params('csrfToken') ?>">
  <title><?= $this->title(); ?></title>

  <style>
     /* TODO: remove once documentation screenshots are done
      @media (min-width: 1200px) {
        .container, .container-lg, .container-md, .container-sm, .container-xl {
            max-width: 1200px;
        }
      }
      .navbar-brand small{
        display:none;
      }
      */
</style>

<?= $this->Bundle->css('bundle.css') ?>
<?= $this->Bundle->js('bundle.js') ?>

</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <!--svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-cloud">
                        <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
                    </svg-->
      <a class="navbar-brand" href="/"><i class="fas fa-cloud"></i>&nbsp;Nuber</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item <?= $this->request->params('controller') === 'Instances' ? 'active' : null ?>">
            <a class="nav-link" href="/instances"><?= __('Instances') ?><span class="sr-only">(current)</span></a>
          </li>
          <li class="nav-item <?= $this->request->params('controller') === 'Volumes' ? 'active' : null ?>">
            <a class="nav-link" href="/volumes"><?= __('Volumes') ?></a>
          </li>
          <li class="nav-item <?= $this->request->params('controller') === 'Images' ? 'active' : null ?>">
            <a class="nav-link" href="/images"><?= __('Images') ?></a>
          </li>

        </ul>
        <ul class="navbar-nav ml-md-auto">
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= $this->Session->read('Auth.User.name') ?></a>
              <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <a class="dropdown-item" href="/profile"><?= __('Profile') ?></a>  
                  <a class="dropdown-item" href="/change-password"><?= __('Change Password') ?></a>  
                  <a class="dropdown-item" href="/hosts"><?= __('Manage Hosts') ?></a>
                  <div class="dropdown-divider"></div>
                  <h6 class="dropdown-header"><?= __('Hosts') ?></h6>

                  <?php foreach (Lxd::hosts() as $address => $name) : ?> 
                    <a class="dropdown-item <?= Lxd::host() === $address ? 'active':'' ?>" href="/hosts/switch?host=<?= $address ?>"> 
                      <?= "{$name} - {$address}" ?>
                    </a>
                   <?php endforeach ?>
                  <div class="dropdown-divider"></div>
                  <h6 class="dropdown-header"><?= __('Debug') ?></h6>
                  <a class="dropdown-item" href="/debug"><?= __('LXD API Log') ?></a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="/logout"><?= __('Logout') ?></a>
              </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container">
    <?= $this->Flash->messages() ?>
    <?= $this->content() ?>
  </main>

  <?= $this->renderShared('debug-bar') ?>
  <!--div id="dialog"></div-->
<script>
  $( document ).ready(function() {
      console.log( "ready!" );
  });
</script>
<!-- Does not work with bundles, probably because of path -->
<link rel="stylesheet" href="/fontawesome/css/all.css">
<link rel="stylesheet" href="/font-logos/assets/font-logos.css">
</body>
</html>