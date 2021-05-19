---
title: Adding Host
description: How to add a remote LXD host
extends: _layouts.documentation
section: content
---

# Adding a Host

Click on your name in the top right hand corner, and a drop down menu will show. Select **manage hosts**, then click on the **add hosts** button.

Enter a **name** and the **IP address** and then provide the host **password**.

![alt text](/assets/img/nuber/host-add.png)

For your convenience `Nuber` will generate a UUID for you, then you can just click on the **host configuration instructions** and copy and paste the commands into the command prompt on the remote server to enable `LXD` to listen on port `8443` and set a temporary password.

For example:

```bash
$ lxc config set core.https_address "[::]:8443"
$ lxc config set core.trust_password "7259619d-9428-4bff-b0ad-eca3057d9655"
```

After you have added the `host` and `Nuber` can connect, you can remove the password to prevent brute force attacks using the following command:

```bash
$ lxc config unset core.trust_password
```
