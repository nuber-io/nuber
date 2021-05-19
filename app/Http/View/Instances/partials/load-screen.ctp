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
 * All vars need to be provided and it assumes that in the controller action the post
 * features are JSON. In the event of an error it will redirect to the URL where it posts to
 *
 * @example
 *
 *    echo $this->render('partials/load-screen', [
 *       'title' => __('Migrating Instance'),
 *       'text' => __('Please wait whilst your instance is migrated.'),
 *      ]);
 */

?>
<style>
    #overlay,
    .card-overlay {
        display: none;
    }
    /** Other spinners are hidden by default */
    .card-overlay .spinner-border {
        display:inline-block; 
    }
</style>

<div id="load-screen" class="card card-overlay">
    <div class="card-body">
        <div class="row">
            <div class="col-3">
                <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
            <div class="col">
                <h5 class="card-title"><?= $title ?></h5>
                <p class="card-text"><?= $text ?></p>

            </div>
        </div>
    </div>
</div>

<div id="overlay"></div>

<script>
function showLoadScreen(){
    $('#overlay,.card-overlay').show();
    disableTimeout();
}
function hideLoadScreen(){
    $('#overlay,.card-overlay').hide();
}
</script>