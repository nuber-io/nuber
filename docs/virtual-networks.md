---
title: Virtual Networks
description: Managing virtual networks
extends: _layouts.documentation
section: content
---

# Virtual Networks

When you install nuber or add a host, it will automatically create `vnet0` which is the default network that is used
when creating instances.

You can use networks to configure your networking settings based upon your prefered IP range, or maybe to use a block of IP addresses that your cloud provider has given you. You can also create seperate networks for different groups of instances.

![alt text](/assets/img/nuber/virtual-networks.png)

## Creating Networks

Click on the **networks tab** on the top of the page, then click on the **new network button**. On the new networks page, enter a **name** for the network.

> If your cloud provider has given you an IP range this is where you can set it using CIDR notation.

If you want the network to use IPv4, then set the **ipv4 address** and **prefix size** for example, `10.1.2.1/24`.

If you want to enable IPv6, then set the **ipv6 address** and **prefix size** for example, `fd42:603c:9fbb:199::1`.

After you have finished click on the **save button**.

## Editing Networks

Click on the **networks tab** on the top of the page, then click on the **actions dropdown** next to the network that you want to edit,and then click on `edit`.

You cannot rename networks if they are being used by instances. If you have incorrect network settings or settings that overlap with another network, you might not be able to start instances.

If you do not want to use IPv4 then clear the **ipv4 address** field.

If you do not want to use IPv6 then clear the **ipv6 address** field.

## Deleting Networks

You cannot delete a network if it is being used by instances.

To delete a network, click on the **networks tab** on the top of the page, then click on the **actions dropdown** next to the network that you want to delete,and then click on `delete`.
