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

The recommended storage pool driver is `ZFS`, see [server setup using ZFS storage driver](docs/lxd/ubuntu-zfs.md) for more information on how to set this up.

Once you have setup your server and `LXD` has been initiailzed you can run the following command to install Nuber.

> Nuber is best installed on a freshly installed system, and it assumes the storage pool configured in LXD is called `default`. Only networks created by Nuber will be visible in the web application, so if you are installing this on an existing server with containers then you will need to set the network description to `Nuber virtual network` to become visible in the network list.

```bash
$ bash <(curl -s https://www.nuber.io/install.sh)
```

### Securing the server

It is highly recommened that you only allow the web interface to be accessed from trusted IP addresses.

You should also block all external traffic coming into the server, except the SSH port. When you add port forwarding to a container or virtual machine in Nuber, LXD will automatically create the rules.

#### Setting up IP Tables (firewall)

In the following example it assumes:

1. Your network device is called `eth0`, use `ip a` to find out what it is
2. Your home or work IP address is `123.123.123.123`, you can run `curl ipinfo.io/ip` to get your public IP address
3. Nuber was installed on port `3000`, change it if you used something else
4. Your SSH is configured on port 22

Replace the values with yours, and run the following, all changes will be persisted when you restart the server as well.

```bash
$ sudo iptables -A INPUT -i eth0 -p tcp --dport 3000 -s 123.123.123.123 -j ACCEPT
$ sudo iptables -A INPUT -i eth0-p tcp --dport 22 -j ACCEPT
$ sudo iptables -A INPUT -i eth0 -m state --state ESTABLISHED,RELATED -j ACCEPT
$ sudo iptables -A INPUT -i eth0 -j REJECT
$ sudo iptables-save
```

Check that you can still SSH in and things are working.

#### Persist on boot

The rules need be restored each time the server boots, install the following packages

```bash
$ sudo apt-get install iptables-persistent
```

Select `yes` when it asks you to save the current rules. Test that you can still login via SSH from another console window, then run `sudo reboot`

You can test the server has been locked down from another server by running the following command and replacing the IP address and port number, it still should work from your browser.

```
$ curl https://123.123.123.123:3000/login
curl: (7) Failed to connect to 123.123.123.123 port 3000: Connection refused
```

## Uninstall Nuber

To uninstall Nuber, you need to run the following commands on your server.

```bash
$ lxc stop nuber-app
$ lxc delete nuber-app
```

## Licence

This is software is free and open source and licensed under AGPL-3.0, which means if you make modifications
to the source (Nuber) or create any derived works using the Nuber source code and ditribute it (including over a network to deliver a service to users other than yourself) then the new version must also be open source and licensed under the AGPL-3.0.
