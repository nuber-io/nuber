---
title: Setup a LXD host using BTRFS
description: Setup an LXD server using Ubuntu and a BTRFS storage pool
extends: _layouts.documentation
section: content
---

# Installation using BTRFS storage

This installation guide covers setting up an LXD host on Ubuntu server using the `BTRFS` storage. Check out the [storage documentation](https://lxd.readthedocs.io/en/stable-4.0/storage/) for more information.

## Install Ubuntu

First download the [Ubuntu server ISO](https://ubuntu.com/download/server).

When installing `Ubuntu` you will need to customize the partitions and make sure that you install `Open SSH` so that you can access the server, LXD is installed by default, so installation is pretty straightforward.

For additional information on installing `Ubuntu`, checkout the [server guide](https://ubuntu.com/server/docs/installation).

### Customizing Partitions

These steps here explain how to customize the partitions so that a separate partition can be used. Two partitions will be created, one for root, and the other will be for the storage pool.

When you get to the `guided storage configuration` screen, select `custom storage layout` and then `done`.

![alt text](/assets/img/ubuntu/custom-storage.png "Custom Storage")

Create the root partition `/`, with at least 10GB, the installation of Ubuntu Server uses around 5GB. For development purposes in a VM this is okay, but on a production server you will want alot more because operations such as migration will write to temporary files, which could be very large depending upon what you are migrating.

![alt text](/assets/img/ubuntu/create-root-partition.png "Create root partition")

Create a partition for the storage, format with BTRFS and set the mount point to `/btrfs`.

![alt text](/assets/img/ubuntu/create-storage-partiton-btrfs.png "Create storage partition")

### Installing OpenSSH

You will need to install openSSH server so you can access the server remotely, check the box when prompted.

![alt text](/assets/img/ubuntu/ubuntu-install-openssh.png "Install OpenSSH")

### Prepare the Server

After the install, login to the server using `ssh` using the ip address of the `LXD` host.

```bash
$ ssh user@192.168.1.x
```

Bring the system up to date

```bash
$ sudo apt update
$ sudo apt upgrade
```

If this server is accesible on the internet then you should secure it with [SSH Keys](/docs/lxd/set-up-ssh-keys).

Install the `BTRFS` utilities

```
$ sudo apt install btrfs-progs
```

## Setting up LXD

So that you can use `LXD` you need to initialize it, you can accept all the defaults except for when it prompts you `Create a new BTRFS pool?`, when it does select **no** and then enter `/btrfs` or the name that you created the partition.

```bash
$ sudo lxd init
Would you like to use LXD clustering? (yes/no) [default=no]:
Do you want to configure a new storage pool? (yes/no) [default=yes]:
Name of the new storage pool [default=default]:
Name of the storage backend to use (ceph, btrfs, dir, lvm) [default=btrfs]:
Create a new BTRFS pool? (yes/no) [default=yes]: no
Name of the existing BTRFS pool or dataset: /btrfs
Would you like to connect to a MAAS server? (yes/no) [default=no]:
Would you like to create a new local network bridge? (yes/no) [default=yes]:
What should the new bridge be called? [default=lxdbr0]:
What IPv4 address should be used? (CIDR subnet notation, “auto” or “none”) [default=auto]:
What IPv6 address should be used? (CIDR subnet notation, “auto” or “none”) [default=auto]:
Would you like LXD to be available over the network? (yes/no) [default=no]:
Would you like stale cached images to be updated automatically? (yes/no) [default=yes]
Would you like a YAML "lxd init" preseed to be printed? (yes/no) [default=no]:
```

See the [production setup guide](/docs/lxd/production-setup) for additional configuration recommendations.
