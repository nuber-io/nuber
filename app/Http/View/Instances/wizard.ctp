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
        padding: 10px;
    }
</style>

<div class="header">
    <div class="float-right">
        <a href="/instances" class="btn btn-secondary"><?= __('Back') ?></a>
    </div>
    <h2><?= __('Select Image') ?></h2>
    <hr>
    </hr>
</div>


<!--nav class="nav nav-pills mb-3">
    <a class="nav-link active" href="#" data-toggle="tab" href="#distributions" role="tab" aria-controls="home" aria-selected="true"><?= __('Distributions') ?></a>
    <a class="nav-link" href="#" data-toggle="tab" href="#applications" role="tab" aria-controls="home" aria-selected="false"><?= __('Applications') ?></a>
    <a class="nav-link" href="#" data-toggle="tab" href="#images" role="tab" aria-controls="home" aria-selected="false"><?= __('Images') ?></a>
</nav-->

<div class="distribution-select">
    <div class="row">
        <div class="col-md-4 mb-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fl fl-ubuntu"></i></h5>
                    <p class="card-text">Ubuntu</p>
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
        <div class="col-md-4 mb-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fl fl-debian"></i></h5>
                    <p class="card-text">Debian</p>
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
        <div class="col-md-4 mb-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fl fl-centos"></i></h5>
                    <p class="card-text">Centos</p>
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

    </div>

    <div class="row">

        <div class="col-md-4 mb-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fl fl-fedora"></i></h5>
                    <p class="card-text">Fedora</p>
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

        <div class="col-md-4 mb-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fl fl-opensuse"></i></h5>
                    <p class="card-text">openSUSE</p>
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

        <div class="col-md-4 mb-2">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fl fl-alpine"></i></h5>
                    <p class="card-text">Alpine</p>
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

    </div>

    <div class="card">
        <div class="card-body">
            <span class="card-text"><?= __('or select from the local image store') ?>&nbsp;</span>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= __('Select Image') ?>
                    </button>
                    <div class="dropdown-menu">
                        <?php foreach ($images as $fingerprint => $image) : ?>
                            <a class="dropdown-item" href="/instances/create?image=<?= $image ?>&fingerprint=<?= $fingerprint ?>"><?= $image ?></a>
                        <?php endforeach ?>
                    </div>
                </div>
            
        </div>
    </div>
</div>