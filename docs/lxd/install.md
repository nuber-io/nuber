---
title: LXD Host Installation
description: A full installation guide for a LXD host
extends: _layouts.documentation
section: content
---

# Install

This installation guide covers setting up an LXD host on Ubuntu server using the `ZFS` storage.

## Install Ubuntu

First download the [Ubuntu server ISO](https://ubuntu.com/download/server).

When installing `Ubuntu` you will need to customize the partitions and make sure that you install `Open SSH` so that you can access the server, LXD is installed by default, so installation is pretty straightforward.

For additional information on installing `Ubuntu`, checkout the [server guide](https://ubuntu.com/server/docs/installation).

### Customizing Partitions

These steps here explain how to customize the partitions so that a separate partition can be used. Two partitions will be created, one for root, and the other will be for the storage pool.

When you get to the `guided storage configuration` screen, select `custom storage layout` and then `done`.

![alt text](/assets/img/ubuntu/custom-storage.png "Custom Storage")

Create the root partition `/`, with at least 25GB. On production servers you should always allow more, because certain processes like migration will need to copy the instances to the remote server, and there needs to be sufficent temporary disk space.

![alt text](/assets/img/ubuntu/create-root-partition.png "Create root partition")

Create a partition for the storage, leaving the size field empty so it uses the remaining space, change the format to `leave unformatted`.

![alt text](/assets/img/ubuntu/create-storage-partition.png "Create storage partition")

### Installing OpenSSH

You will need to install openSSH server so you can access the server remotely, check the box when prompted.

![alt text](/assets/img/ubuntu/ubuntu-install-openssh.png "Install OpenSSH")

## Post Install

After the install, login to the server using `ssh` using the ip address of the `LXD` host.

```bash
$ ssh user@192.168.1.x
```

Bring the system up to date

```bash
$ sudo apt update
$ sudo apt upgrade
```

Install the `zfs` utilities

```bash
$ sudo apt install zfsutils-linux -y
```

## Configuring the ZFS storage pool

To load the `ZFS` module now and when the server starts up

```bash
$ sudo modprobe zfs
$ sudo sh -c "echo 'zfs' >> /etc/modules"
```

Check that the module is loaded

```bash
$ sudo lsmod | grep zfs
zfs                  4030464  0
zunicode              331776  1 zfs
zavl                   16384  1 zfs
icp                   303104  1 zfs
zcommon                90112  2 zfs,icp
znvpair                81920  2 zfs,zcommon
spl                   126976  5 zfs,icp,znvpair,zcommon,zavl
zlua                  147456  1 zfs
```

### Creating the ZFS Pool

To see the partitions, we are looking for the empty partition that we [created](/assets/img/ubuntu-install.md) but did not mount .

Run the following command to see the partitions

```bash
$ lsblk
NAME   MAJ:MIN RM  SIZE RO TYPE MOUNTPOINT
loop0    7:0    0 29.9M  1 loop /snap/snapd/8542
loop1    7:1    0   55M  1 loop /snap/core18/1880
loop2    7:2    0 71.3M  1 loop /snap/lxd/16099
sda      8:0    0   25G  0 disk
├─sda1   8:1    0    1M  0 part
├─sda2   8:2    0   10G  0 part /
└─sda3   8:3    0   15G  0 part
sr0     11:0    1 1024M  0 rom
```

The `sda1` is the BIOS partition, and `sda2` is the `root` partition which is mounted to `/`, therefore it is `sda3`. If you are not sure you can run `df` or `sudo fdisk -l` to get more information.

> WARNING: Choose the wrong block device and you will erase your operating system or other data

Run the following command , changing the `sdaX` to the **unmounted** partition that you want to use, e.g. `sda3`.

```bash
$ sudo zpool create lxd /dev/sdaX
```

You can check that it was created okay

```bash
$ sudo zpool list
NAME      SIZE  ALLOC   FREE  CKPOINT  EXPANDSZ   FRAG    CAP  DEDUP    HEALTH  ALTROOT
lxd  50G   110K  50G        -         -     0%     0%  1.00x    ONLINE  -
```

## Configuring LXD

> The default storage pool should be called `default`, do not change this name

So that you can use `LXD` you need to initialize it, you can accept all the defaults except for when it prompts you `Create a new ZFS pool?`, when it does select **no** and then enter `lxd` or the name that you used when creating the `ZFS` pool earlier.

```bash
$ sudo lxd init
Would you like to use LXD clustering? (yes/no) [default=no]:
Do you want to configure a new storage pool? (yes/no) [default=yes]:
Name of the new storage pool [default=default]:
Name of the storage backend to use (lvm, zfs, ceph, btrfs, dir) [default=zfs]:
Create a new ZFS pool? (yes/no) [default=yes]: no
Name of the existing ZFS pool or dataset: lxd
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
