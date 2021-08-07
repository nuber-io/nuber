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

First setup an Ubuntu server using the [setup LXD with ZFS guide](/docs/lxd/ubuntu-zfs), then once `LXD` has been setup and configured, run the following command to install `Nuber`.

```bash
$ bash <(curl -s https://www.nuber.io/install.sh)
```

> It is best to install `Nuber` on a fresh install of Ubuntu with LXD configured as instructed in the documentation, if you have an existing installation see the `README.md` in the github repository for additional information on steps that might be required.

Once the installation script is complete, open your browser and goto `https://<IP_ADDRESS>:<PORT_NUMBER>/install`, to create a user and add the `LXD` host.

## Adding a host

So that `Nuber` can manage an `LXD` host you need to configure the server to listen on port `8843` and set a temporary password, which you should remove afterwards. When installing Nuber or when adding a new host, `Nuber` will randomly generate a UUID and provide you with copy and paste instructions like below, for you run on the `LXD` host.

You will need to configure LXD to listen over the network, for security reasons you want this to only listen internally, to do so provide the local IP address which you can find by running `ip a`

```bash
$ lxc config set core.https_address 192.168.1.x
```

> If you don't have a local IP address then you can run `lxc config set core.https_address "[::]:8443"` which will listen on all, but this means that the API is accessible from the outside as well, so on a production server you would want to lock this down.

Next set a temporary password so that `Nuber` can authorize itself with `LXD`.

```bash
$ lxc config set core.trust_password "7259619d-9428-4bff-b0ad-eca3057d9655"
```

After you have added the `host` and `Nuber` can connect, you can remove the password to prevent brute force attacks using the following command:

```bash
$ lxc config unset core.trust_password
```

## Securing access to the panel

It is highly recommened that you only allow the web interface to be accessed from trusted IP addresses.

First find out what your IP address is, from your client machine run the following command:

```
$ curl ipinfo.io/ip
```

Then on your server create the following firewall rule which will reject traffic to the selected port when not coming from a trusted IP address.

Replace `<IP_ADDRESS>` with your IP address and `<PORT_NUMBER>` with the port number that you chose when installing nuber.

```bash
$ sudo iptables -I INPUT -p tcp ! -s <IP_ADDRESS> --dport <PORT_NUMBER> -j REJECT
```
