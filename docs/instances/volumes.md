---
title: Volumes
description: How to work with snapshots on a LXD container
extends: _layouts.documentation
section: content
---
# Volumes

Volumes allow you to add extra disk space to your instances, each volume can only be attached to one instance at a time, but you can attach and detach as you need.

## Creating Volumes

Click on the **volumes tab** on the top of the page, then click on the **new volume button**. On the new volume page, enter a **name** for the volume and set the **size** for the volume in gigabytes, so for 5 GB, enter `5GB`.

## Attaching Volumes

From the instances home, click on the **instance** and then click on the **volumes tab** in the left-hand side.

![alt text](/assets/img/nuber/instance-volumes.png)

Select the **volume** from the drop downlist, and chose a unique mount point and then click on the **attach volume button**.

## Detataching a Volume

From the instances home, click on the **instance** and then click on the **volumes tab** in the left-hand side. From
the list of mounted volumes, click on the **eject icon** to detach. A confirmation
dialog will then be shown before the volume is detached.