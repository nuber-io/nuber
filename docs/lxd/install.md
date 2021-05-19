---
title: Install LXD
description: How to install LXD on Ubuntu
extends: _layouts.documentation
section: content
---

# Install LXD on Ubuntu

LXD is installed by default on `Ubuntu server 20.04 LTS`, but if you need to install this on Ubuntu desktop or on an older version of Ubuntu server then this guide will show you how.

First verify that `LXD` is not already installed by the running the following command.

```bash
$ snap list
Name    Version   Rev    Tracking         Publisher   Notes
core18  20200707  1883   latest/stable  canonical✓  base
lxd     4.0.2     16103  4.0/stable/…   canonical✓  -
snapd   2.45.2    8543   latest/stable  canonical✓  snapd
```

If LXD is not installed then install it with `snap`.

```bash
$ sudo snap install lxd --channel=4.0/stable
```

Add the current logged in user to the `lxd` group so that you need to use `sudo` in future when managing LXD.

```bash
$ sudo adduser $USER lxd
```

Run the following command to apply for group membership, so that you don't have to logout and log back in again.

```
$ newgrp lxd
```
