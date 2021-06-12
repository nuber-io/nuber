# Nuber (beta)

> Nube is cloud in Spanish.

Nuber is an open source container management platform it provides a front end to manage your own cloud infrastructure, using [Linux Containers](https://linuxcontainers.org/) virtualization technology. Linux containers is an open source container-based hypervisor, which is created and maintained by [Canonical](https://canonical.com/) the company behind Ubuntu. Linux containers offers almost 15x greater density than KVM whilst allowing criticial applications run at bare metal performance ([source](https://ubuntu.com/blog/lxd-crushes-kvm-in-density-and-speed)).

This software aims to make it easier for software companies and developers to setup and manage their own cloud, whilst achieving high density virtualization, both these goals reduce costs and administration time significantly.

Nuber is a web-based software with an underlying set of processes for setting up and managing containers making it extremley easy to manage virtualization in a consistent way whilst removing the burden and complexity of trying to do this from the command line.

## Installation

`Nuber` is installed inside its own container on a host which is running `LXD`. When you connect an `LXD` host to Nuber, depending on the storage driver it might make configuration changes to the host.

### Server Setup

You will need a server or a virtual machine with Linux installed, it is recommened that use [Ubuntu server LTS](https://ubuntu.com/download/server), for performance, security and easy of use. If you are setting this up at home on a MAC or a development machine, then you can install Ubuntu inside a virtual machine using [virtualbox](https://www.virtualbox.org/) or [Parallels Desktop](https://www.parallels.com/uk/products/desktop/). Please note that, LXD can't be installed inside a Docker container.

Its recommended to have a separate partition for the LXD storage pool, so do this during the install, create two partitions, one for the operating system, at least 15GB for `/` and leave the remaining space for the storage pool partition. Note, for production servers, you will need to ensure that you have enough free space for temporary files, such as when migrating containers to different servers, so 25GB or more is better.

The recommended storage pool driver is `ZFS`, see [server setup using ZFS storage driver](docs/lxd/ubuntu-zfs.md) for more information on how to set this up.

Once you have setup your server and `LXD` has been initiailzed you can run the following command to install Nuber.

> Nuber is best installed on a freshly installed system (without existing containers)

```bash
$ bash <(curl -s https://www.nuber.io/install.sh)
```

There is an option to create a bridged network connection, if you dont need this or you are installing in a virtual machine, then select `n`.

```bash
Do you want to setup a bridged network connection? (y/n) [n]
```

Once the installation is complete, you can go to `https://<ip_address>:3000/install` to setup your installation.

## Uninstall Nuber

To uninstall Nuber, you need to run the following commands on your server.

```bash
$ lxc stop nuber-app
$ lxc delete nuber-app
```

## Licence

This is software is free and open source and licensed under AGPL-3.0, which means if you make modifications
to the source (Nuber) or create any derived works using the Nuber source code and ditribute it (including over a network to deliver a service to users other than yourself) then the new version must also be open source and licensed under the AGPL-3.0.
