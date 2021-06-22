---
title: Network Settings
description: How to set the network settings for an LXD container
extends: _layouts.documentation
section: content
---

# Setting the Network Settings

From the instances home, click on the **instance** that you want to change the network settings. If the **instance** is running, then you will need to stop this. To stop the instance click on the **stop** button.

![alt text](/assets/img/nuber/instance-network-settings.png)

## IP address

You can set the IP address for the instance on a virtual network, e.g. `10.0.0.123`, simply enter the **IP address** in the field and then click on **update IP settings**.

## Network Interfaces

By default when you create an instance, the instance will be configured to use the `Virtual Network`. If you need your instance to be visible on your local network or you want to set a static public IP address, then you will need use `macvlan` or `bridged`.

**Virtual Network** Instances can see other instances on the virtual network, and they have internet access but they cannot be reached from outside (without port forwarding).

**Macvlan Network** Instances have internet access and can be reached from outside, they are unable to communitate with the host. This is works out of the box, and is faster than bridging, so unless you need to communicate with the host, then this is your best choice. If you are using this on `eth1` or you dont want to use DCHP for allocating IP addresses then you will need to adjust the network settings inside the container.

**Bridged Network** Instances have internet access and can be reached from outside, and they can communicate with the host.
When you installed `nuber` you would have been asked if you want to setup a bridged network, this would of created the device `nuber-bridged`[^1], which is used by Nuber. If you did not start it then see the `install.sh`, as it needs to be running and working. If you are using this on `eth1` or you dont want to use DCHP for allocating IP addresses then you will need to adjust the network settings inside the container.

If your cloud hosting provider gives you a virtual MAC address, here you can also set this. To randomly generate a MAC address leave the address empty.

[^1]: In version 0.1.0, the network bridge was created as `nuberbr1`.
