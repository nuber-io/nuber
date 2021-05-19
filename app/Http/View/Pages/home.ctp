
  <?php
    function warning($message)
    {
        bootstrapAlert('warning', $message);
    }

    function success($message)
    {
        bootstrapAlert('success', $message);
    }
    function error($message)
    {
        bootstrapAlert('danger', $message);
    }
    function info($message)
    {
        bootstrapAlert('info', $message);
    }
    function bootstrapAlert($type, $message)
    {
        echo "<div class=\"alert alert-{$type}\" role=\"alert\">{$message}</div>";
    }
  ?>

  <body>
    <div class="container">

      <h1>OriginPHP Framework</h1>
      <p>This is the test page to see that all is working ok. You can remove or change this by editing the <strong>config/Routes.php</strong>. This is the route that is used to show this page:</p>
      <pre>
          Router::add('/', ['controller' => 'pages', 'action' => 'display', 'home']);
      </pre>
      <h2>Status</h2>
      <?php
        $tmp = TMP;
        if (is_writeable(TMP)) {
            success("{$tmp} is writeable.");
        } else {
            warning("{$tmp} folder is NOT writeable. Run <em>chmod 0755 {$tmp}</em>");
        }
      ?>
      <?php

      $logs = LOGS;
      if (is_writeable(LOGS)) {
          success("{$logs} is writeable.");
      } else {
          warning("{$logs} is NOT writeable. Run <em>chmod 0755 {$logs}</em>");
      }
      ?>

     <?php
        $env = CONFIG.DS.'.env.php';
        if (file_exists($env)) {
            success("{$env} found");
        } else {
            warning("{$env} not found");
        }
      ?>

      <?php
        $databaseConfig = CONFIG.DS.'database.php';
        use Origin\Model\ConnectionManager;

        if (file_exists($databaseConfig)) {
            try {
                $db = ConnectionManager::get('default');
                success('Connected to database.');
            } catch (\Exception $e) {
                warning('Unable to connect to the database.');
            }
        }
      ?>
    </div>