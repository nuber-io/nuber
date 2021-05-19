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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/default.css">
    <link rel="stylesheet" href="/css/form.css">
    <title><?= $this->title(); ?></title>
    <style>
        .container-fluid {
            padding:0px;
        }
        .alert {
            text-align: center;
        }
   
        </style>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
   

  <script>
  // Reload page to refresh CSRF token
  (function() {
      let idleTimeout;

      const resetTimeout = function() {
          if (idleTimeout) {
              clearTimeout(idleTimeout);
          }

          idleTimeout = setTimeout(() => location.href = location.href, 300 * 1000);
      };

      resetTimeout();

      document.addEventListener('click', resetTimeout, false);
      document.addEventListener('mousemove', resetTimeout, false);
      document.addEventListener('touchstar', resetTimeout, false);
     
    })();
  </script>

  </head>
  <body>
  

    <div class="container-fluid">
    <?= $this->Flash->messages() ?>
      <?= $this->content() ?>
    </div>

    <?= $this->renderShared('debug-bar') ?>
    

  </body>
</html>
