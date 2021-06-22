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
 *
 * @var \App\Http\View\ApplicationView $this
 */
use App\Lxd\Lxd;

?>
<style>

.fa-exclamation-triangle 
{
  color:red;
}
</style>
<div class="header">
    <div class="float-right">
        <a href="/debug/download" class="btn btn-primary"><?= __('Download Log') ?></a>
    </div>
    <h2><?= __('LXD API Log') ?></h2>
    <hr></hr>
</div>
<p><?= __('These are a log of the most recent requests and responses between Nuber and the LXD hosts through the API.') ?></p>

<div class="debug">

<?php foreach ($log as $index => $line) : ?>

  <div class="row request p-2">

  <?php
    $line = json_decode($line, true);

    $badge = '<span class="badge badge-primary">' .__('data') .'</span>';
    
    // TODO: implement
    $warning = null;
    if (is_array($line['responseBody']) && ! empty($line['responseBody']['metadata']['err'])) {
        $warning = '<span>&nbsp;<i class="fas fa-exclamation-triangle"></i></span>';
    }
    
    $badgeResponse = '<span class="badge badge-warning">' .__('response') .   '</span>';
    $hostClass = count(Lxd::hosts()) > 1 ? 'normal' : 'hidden';
  ?>

    <div class="col-sm">
      <span class="pr-2"><?= $line['date'] ?></span>
      <span class="badge badge-<?= $line['level'] === 'ERROR' ? 'danger' : 'success' ?>"><?= $line['level'] ?></span>

      
      <span class="pl-2 <?= $hostClass ?>"><?= $line['host'] ?></span>
      <span class="pl-2 message" title=""><?= str_replace('/1.0', '', $line['message']) ?></span>
        <?php
          if (! empty($line['requestBody'])) {
              echo $this->Html->link($badge, '#', ['onclick' => "showData({$index})"]);
          }
        ?>
          <?= $warning ?>
    </div>
   
    <div class="col-sm-1">
      <?= $this->Html->link($badgeResponse, '#', ['onclick' => "showResponse({$index})"]) ?>
    </div>
   
  </div>

  <div class="row collapsible post-data-<?= $index ?> hidden">
    <div class="col-md-12">

    <div class="card">
      <div class="card-body">
        <pre><?= json_encode($line['requestBody'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?> </pre>
      </div>
    </div>
    
    </div> 
  </div>
  <div class="row collapsible response-data-<?= $index ?> hidden">
    <div class="col-md-12">
        
    <div class="card">
      <div class="card-body">
        <pre> <?= json_encode($line['responseBody'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?> </pre>
      </div>
    </div>

    </div> 
  </div>

 
<?php endforeach ?>
</div>

<script> 
  function showData(index) {
    $('.post-data-' + index).toggle();
  }

  function showResponse(index) {
    $('.response-data-' + index).toggle();
  }
</script>