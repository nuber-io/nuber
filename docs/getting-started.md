---
title: Getting Started
description: A quick guide to getting setup
extends: _layouts.documentation
section: content
---

# Getting Started

> Nube is cloud in Spanish.

Nuber is an open source virtualization management software, it provides a front end to manage your own cloud infrastructure, using [Linux Containers](https://linuxcontainers.org/) virtualization technology. Linux containers is an open source container-based hypervisor, which is created and maintained by [Canonical](https://canonical.com/) the company behind Ubuntu. Linux containers offers almost 15x greater density than KVM whilst allowing criticial applications run at bare metal performance[\*](https://ubuntu.com/blog/lxd-crushes-kvm-in-density-and-speed).

This software aims to make it easier for software companies and developers to setup and manage their own cloud, whilst achieving high density virtualisation, both these goals reduce costs and administration time significantly.

Nuber provides a web based interface with an underlying set of processes for setting up and managing containers making it extremley easy to manage virtualization in a consistent way whilst removing the burden and complexity of trying to do this from the command line.

## Installation

To install `Nuber` you will need a server or virtual machine with [Ubuntu server](https://ubuntu.com/download/server) installed. `LXD` which is the daemon which is used by `Nuber` to create and manage containers, and it is installed by default on Ubuntu server 20.04 LTS.

LXD supports various [storage drivers](https://lxd.readthedocs.io/en/stable-4.0/storage/#feature-comparison), but the best ones are [ZFS](/docs/lxd/ubuntu-zfs) and [BTRFS](/docs/lxd/ubuntu-btrfs).

First setup an Ubuntu server using the [setup LXD with ZFS guide](/docs/lxd/ubuntu-zfs). Then once `LXD` has been setup and configured, run the following command to install `Nuber`

```bash
$ bash <(curl -s https://www.nuber.io/install.sh)
```

Once the installation script is complete, open your browser and goto `https://your-ip-address:3000/install`, to create a user and add the `LXD` host.

## Adding a host

So that `Nuber` can manage an `LXD` host you need to configure the server to listen on port `8843` and set a temporary password, which you can remove afterwards.

When installing Nuber or when adding a new host, `Nuber` will randomly generate a UUID and provide you with copy and paste instructions like below, for you run on the `LXD` host.

```bash
$ lxc config set core.https_address "[::]:8443"
$ lxc config set core.trust_password "7259619d-9428-4bff-b0ad-eca3057d9655"
```

After you have added the `host` and `Nuber` can connect, you can remove the password to prevent brute force attacks using the following command:

```bash
$ lxc config unset core.trust_password
```
