<?php
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

    <!-- Bootstrap CSS -->

    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/default.css">
    <script src="/js/jquery.min.js" ></script>

    <link rel="stylesheet" href="/css/form.css">
    <title><?= $this->title(); ?></title>
  </head>
  <body>

    <div class="container-fluid">
    <?= $this->Flash->messages() ?>
      <?= $this->content() ?>
    </div>

    <?= $this->renderShared('debug-bar') ?>
    
    <script src="/js/application.js"></script>
    <script src="/js/login.js"></script>
  </body>
</html>
