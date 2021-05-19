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

?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/default.css">
    
    <script src="/js/jquery.min.js" ></script>
    <script src="/js/application.js"></script>
    <title><?= $this->title(); ?></title>
  </head>
  <body>

    <main class="container">
      <?= $this->Flash->messages() ?>
      <?= $this->content() ?>
    </main>
 
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <?= $this->renderShared('debug-bar') ?>
  </body>
</html>
