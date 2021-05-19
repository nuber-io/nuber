---
title: LXD Production Setup
description: A guide on configuring an Ubuntu LXD server for production setups
extends: _layouts.documentation
section: content
---

The [LXD User Guide](https://lxd.readthedocs.io/en/latest/production-setup/) gives advice on how to configure a server for production setup, the instructions here are from that and have been reproduced to make it easy to copy and paste.

Add the following to `/etc/security/limits.conf`

```
*               soft    nofile  1048576
*               hard    nofile  1048576
root            soft    nofile  1048576
root            hard    nofile  1048576
*               soft    memlock unlimited
*               hard    memlock unlimited
root            soft    memlock unlimited
root            hard    memlock unlimited
```

Add the following to the bottom of file `/etc/sysctl.conf`

```
fs.inotify.max_queued_events = 1048576
fs.inotify.max_user_instances = 1048576
fs.inotify.max_user_watches = 1048576
vm.max_map_count = 262144
kernel.dmesg_restrict = 1
net.ipv4.neigh.default.gc_thresh3 = 8192
net.ipv6.neigh.default.gc_thresh3 = 8192
net.core.bpf_jit_limit = 3000000000
kernel.keys.maxkeys = 2000
kernel.keys.maxbytes = 2000000
fs.aio-max-nr = 524288
```

Run the following command to reboot the server.

```bash
$ sudo reboot now
```

In the [LXD User Guide](https://lxd.readthedocs.io/en/latest/production-setup/), it also provides a section on Network Bandwidth Tweaking, which you should read.
