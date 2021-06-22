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
    .fl {
        font-size: 5rem;
    }

    .card div {
        vertical-align: middle;
    }

    .distro-logo {
        height: 65px;
    }
    .distro-logo  img {
        display: block;
        margin: 0 auto;
    }
</style>

<div class="header">
    <div class="float-right">
        <a href="/instances" class="btn btn-secondary"><?= __('Back') ?></a>
    </div>
    <h2><?= __('Select Operating System') ?></h2>
    <hr>
    </hr>
</div>


<!--nav class="nav nav-pills mb-3">
    <a class="nav-link active" href="#" data-toggle="tab" href="#distributions" role="tab" aria-controls="home" aria-selected="true"><?= __('Distributions') ?></a>
    <a class="nav-link" href="#" data-toggle="tab" href="#applications" role="tab" aria-controls="home" aria-selected="false"><?= __('Applications') ?></a>
    <a class="nav-link" href="#" data-toggle="tab" href="#images" role="tab" aria-controls="home" aria-selected="false"><?= __('Images') ?></a>
</nav-->

<div class="distribution-select">
    <div class="row mt-4 mb-4">

        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img style="width: 220px; height:54px" src="/img/alpine-linux-logo.svg"></div>
    
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['alpine'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                
        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img src="/img/archlinux-logo.png" ></div>

                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['archlinux'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img style="width: 193px; height:65px" src="/img/centos-logo.svg"></div>
            
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['centos'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img src="/img/debian-logo.png" ></div>
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['debian'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        

    </div><!-- row -->

    <div class="row mb-4">

        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img style="width: 65px; height:65px" src="/img/fedora-logo.svg" ></div>
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['fedora'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img style="width: 96px; height:60px" src="/img/opensuse-logo.svg"></div>
        
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['opensuse'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

     
        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img style="width: 220px; height:48px" src="/img/rocky.svg"></div>
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['rocky'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-3 mb-2">
            <div class="card text-center">
                <div class="card-body">
                <div class="distro-logo"><img src="/img/ubuntu-logo.gif" ></div>
                    <p class="card-text"></p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?= __('Select Version') ?>
                        </button>
                        <div class="dropdown-menu">
                            <?php foreach ($distributions['ubuntu'] as $image => $description) : ?>
                                <a class="dropdown-item" href="/instances/create?image=<?= $image ?>/<?= $architecture ?>"><?= $description ?></a>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- row -->



    <div class="card">
        <div class="card-body">
            <span class="card-text"><?= __('You can also select an existing image from the local image store') ?>&nbsp;</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= __('Select Image') ?>
                    </button>
                    <div class="dropdown-menu">
                        <?php foreach ($images as $image) : ?>
                            <a class="dropdown-item" href="/instances/create?image=<?= $image['alias'] ?>&fingerprint=<?= $image['fingerprint'] ?>&type=<?= $image['type'] ?>&store=yes"><?= $image['alias'] ?>&nbsp;(<?= $image['properties']['type'] === 'squashfs' ? __('Container') : ('Virtual machine')  ?>)</a>
                        <?php endforeach ?>
                    </div>
                </div>  
        </div>
    </div>    
</div>

<div class="mt-4">
    <small class=" text-muted text-center"><?= __('* All logos and registered trademarks are the property of their respective owners. All operating system logos used in this application are for identification purposes only.') ?></small>
</div>