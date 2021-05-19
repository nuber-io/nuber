<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
?>

<?php
/**
 * Using this to remove highlight colors.
 * Note: using highlight string does not return correct class.
 */
ini_set('highlight.default', '"class="code_default');
ini_set('highlight.keyword', '"class="code_keyword');
ini_set('highlight.string', '"class="code_string');
ini_set('highlight.html', '"class="code_htmlsrc');
ini_set('highlight.comment', '"class="code_comment');
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/debug.css">
    <title><?php echo $debug['message']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inconsolata&display=swap" rel="stylesheet">
    <style> 
      code {
        font-family: 'Inconsolata', monospace;
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row debug-header">
        <div class="exception">
            <small class="exception-namespace"><?php echo $debug['namespace']; ?></small>
            <p class="exception-class"><?php echo $debug['class']; ?> <span class="exception-code"><?php echo $debug['code']; ?></span></p>
            <p class="exception-message"><?php echo $debug['message']; ?></p>
          </div>
      </div>
      <div class="row">
        <div class="col-4 stack-frames">
          <div class="list-group" id="myList" role="tablist">
            <?php
              $attr = ' active';
              foreach ($debug['stackFrames'] as $index => $stack) {
                  $file = str_replace(ROOT, '', $stack['file']); ?>
                  <a class="list-group-item list-group-item-action<?php echo $attr; ?>" data-toggle="list" href="#frame-<?php echo $index; ?>" role="tab">
                    <?php echo $stack['class']; ?>
                    <span class="function"><strong><?php echo $stack['function']; ?></strong></span>
                    <p><?php echo $file; ?> <span class="badge badge-warning"><?php echo $stack['line']; ?></span></p>
                  </a>

                  <?php
                    $attr = '';
              }
            ?>
          </div>
        </div>
        <div class="col-8">
          <div class="tab-content">
            <?php
              $attr = ' show active';
              foreach ($debug['stackFrames'] as $index => $stack) {
                  if (empty($stack['file'])) {
                      continue;
                  }
                  $lines = file($stack['file']);

                  $preview = '';
                  $i = 1;

                  foreach ($lines as $line) {
                      $line = highlight_string($line, true);
                      if (empty($line)) {
                          $line = '&nbsp;';
                      }

                      $class = 'normal';
                      if ($i === $stack['line']) {
                          $class = 'highlight';
                      }
                      $preview .=
                      "<div class=\"{$class}\">
                        <div class=\"preview-line\">{$i}</div>
                        <div class=\"preview-code\">{$line}</div>
                      </div>";

                      ++$i;
                  }

                  echo
                  "<div class=\"tab-pane fade{$attr}\" id=\"frame-{$index}\" role=\"tabpanel\">
                    <div class=\"preview\">".$preview.'</div>
                  </div>';
                  $attr = '';
              }
            ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="/js/jquery.min.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.min.js" ></script>
  </body>
</html>
