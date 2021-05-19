---
title: Migrate an Instance
description: How to migrate an LXD container
extends: _layouts.documentation
section: content
---

# Migrate an Instance

From the instances home, click on the **instance** that you want to migrate and then click on the **migrate tab** in the left-hand side. It is advisible to always test with a dummy container the migration process on newly connected hosts.

![alt text](/assets/img/nuber/instance-migrate.png)

Select the `LXD` host that you want to migrate the instance too, then either click **move instance** or **copy instance**.

Moving transfers the instance and its snapshots to the remote host, the local instance and its data will be destroyed after the instance has been copied to the other server. Whilst copy creates a clone of the instance with its snapshots on the remote host but the clone will have a different hardware address.

Things to consider

- The LXD versions should be the same, use the LTS or the latest but not both
- If the static IP address is already used on the destination server, then a different one
  will be allocated
- The system time should be synchronized
