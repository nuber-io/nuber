---
title: Set Up SSH Keys
description: Set up SSH keys for your Ubuntu server
extends: _layouts.documentation
section: content
---

# Set Up SSH Keys

These steps are to setup a SSH access for your Ubuntu server.

## Generate a key pair

First genreate a key pair

```bash
$ ssh-keygen
```

Now to copy the public key to your server

```bash
$ ssh-copy-id user@example.com
```

Test that you can login with the keys

```bash
$ ssh user@example.com
```

## Disable Password Authentication

If you were able to login then the next step is to disable password authentication

```bash
sudo nano /etc/ssh/sshd_config
```

Uncomment the line which has `PasswordAuthentication` by removing the `#`

```
PasswordAuthentication no
```

Now restart the SSH server

```bash
$ sudo systemctl restart ssh
```
