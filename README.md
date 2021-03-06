# Nuber (beta)

> Nube is cloud in Spanish.

Nuber is an open source container management platform it provides a front end to manage your own cloud infrastructure, using [Linux Containers](https://linuxcontainers.org/) virtualization technology. Linux containers is an open source container-based hypervisor, which is created and maintained by [Canonical](https://canonical.com/) the company behind Ubuntu. Linux containers offers almost 15x greater density than KVM whilst allowing criticial applications run at bare metal performance ([source](https://ubuntu.com/blog/lxd-crushes-kvm-in-density-and-speed)).

This software aims to make it easier for software companies and developers to setup and manage their own cloud, whilst achieving high density virtualization, both these goals reduce costs and administration time significantly.

Nuber is a web-based software with an underlying set of processes for setting up and managing containers making it extremley easy to manage virtualization in a consistent way whilst removing the burden and complexity of trying to do this from the command line.

## Installation

`Nuber` is installed inside its own container on a host which is running `LXD`. When you connect an `LXD` host to Nuber, depending on the storage driver it might make configuration changes to the host.

### Server Setup

You will need a server or a virtual machine with Linux installed, it is recommened that use [Ubuntu server LTS](https://ubuntu.com/download/server), for performance, security and easy of use. If you are setting this up at home on a MAC or a development machine, then you can install Ubuntu inside a virtual machine using [virtualbox](https://www.virtualbox.org/) or [Parallels Desktop](https://www.parallels.com/uk/products/desktop/). Please note that, LXD can't be installed inside a Docker container.

Its recommended to have a separate partition for the LXD storage pool, so do this during the install, create two partitions, one for the operating system, at least 25GB for `/` and leave the remaining space for the storage pool partition.

The recommended storage pool driver is `ZFS`, see the [install guide](https://www.nuber.io/docs/lxd/install/) on how to install Ubuntu, setup ZFS and install LXD.

Once you have setup your server and `LXD` has been initiailzed you can run the following command to install Nuber.

> Nuber is best installed on a freshly installed system, and it assumes the storage pool configured in LXD is called `default`. Only networks created by Nuber will be visible in the web application, so if you are installing this on an existing server with containers then you will need to set the network description to `Nuber virtual network` to become visible in the network list.

```bash
$ bash <(curl -s https://www.nuber.io/install.sh)
```

### Securing the server

It is important to secure the server, this includes restricting access to the Nuber control panel so that only people from trusted IP addresses can access it, as well as setting up a firewall.

See the [securing the host guide](https://www.nuber.io/docs/lxd/securing-the-host/) for steps on how to do this.

## Uninstall Nuber

To uninstall Nuber, you need to run the following commands on your server.

```bash
$ lxc stop nuber-app
$ lxc delete nuber-app
```

## Licence

This is software is free and open source and licensed under AGPL-3.0, which means if you make modifications
to the source (Nuber) or create any derived works using the Nuber source code and ditribute it (including over a network to deliver a service to users other than yourself) then the new version must also be open source and licensed under the AGPL-3.0.
