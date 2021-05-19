---
title: Reset Password
description: Reset nuber password
extends: _layouts.documentation
section: content
---
# Reset Password

If you forget your password and you need to reset this, these are the steps that you need to do

Enter the `nuber` container and go into the app directory.

On your LXD host run the following commands:

```bash
$ lxc exec nuber-app bash
$ cd /var/www
```

now run the reset password command

```bash
$ bin/console nuber:reset-password
Nuber - Reset Password
What is the user email address?
> jon@example.com

What password would you like to change to
> xxxxx

Password has been changed
```