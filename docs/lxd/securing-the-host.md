---
title: Securing the host
description: Securing the LXD host
extends: _layouts.documentation
section: content
---

# Securing the LXD host

In this guide we will cover some basic steps to secure the host.

## Setup SSH Keys

Create a key pair which will be used for authentication and upload the public key to the LXD host.

```bash
$ ssh-keygen -t ecdsa -b 521
$ ssh-copy-id user@123.123.123.123
$ ssh user@123.123.123.123
```

## Secure openSSH

Disable root login, change the SSH port and enable public key authentication.

Open the SSH server configuration file

```bash
$ sudo vi /etc/ssh/sshd_config
```

Set the following settings, change port `2222` to something that you perfer, ensuring that the port
does not clash with something else, usually < 1024 are reserved, so probably pick a number between `1024` and `65535`.

```
port 2222
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
```

Check that the config is correct.

```bash
$ sudo sshd -t
```

Restart the SSH server.

```bash
$ sudo systemctl restart sshd
```

Without logging out, from another terminal window try logging in using the public key and new port number to ensure it is all working correctly.

```bash
$ ssh -p 2222 user@123.123.123.123
```

Check that only public keys are accepted

```bash
$ ssh -p 2222 root@123.123.123.123
root@123.123.123.123: Permission denied (publickey).
```

## Setup a firewall (IP Tables)

Here we are going to configure the firewall to block all traffic, and then create a rule to allow SSH traffic and restrict external traffic to the nuber control panel from a trusted IP address.

Look up your internet device name, and replace `eth0` with that. You can run `ip a` to find out what your device is called, it will be next to the main IP address.

```bash
$ sudo iptables -A INPUT -i eth0 -p tcp --dport 3000 -s 123.0.0.10 -j ACCEPT
```

Now lets create a rule to allow all traffic for `ssh`, remember to change `eht0` and the port number.

```bash
$ sudo iptables -A INPUT -i eth0 -p tcp --dport 2222 -j ACCEPT
```

Now run the following to block all external new traffic and save then save those rules.

```
$ sudo iptables -A INPUT -i eth0 -m state --state ESTABLISHED,RELATED -j ACCEPT
$ sudo iptables -A INPUT -i eth0 -j REJECT
$ sudo iptables-save
```

You can see the rules that have been configured.

```bash
$ sudo iptables -S
```

From a different console window check that you can still SSH in before continuning.

Test that the nuber control panel has been locked down, from a different IP address run the following command and replacing the host IP address and port number.

```
$ curl https://123.123.123.123:3000/login
curl: (7) Failed to connect to 123.123.123.123 port 3000: Connection refused
```

From your current IP address in your browser check you can access it `https://123.123.123.123:3000/login`.

When the server is rebooted IP Tables rules will need to be set again, to have this done automatically you can install the following package

```bash
$ sudo apt install iptables-persistent
```

Select `yes` when it asks you to save the current rules. Test that you can still login via SSH from another console window, then run `sudo reboot`

You can also run a port scan to see which ports are open. We will use `nmap` with special options due to the firewall, if not it will just hang.

```bash
$ sudo nmap -sS -T4 123.123.123.123
```

## Setup Fail2ban

Here we will setup fail2ban, so any IP addresses that try excess SSH logins will be banned.

To install `fail2ban`

```bash
$ sudo apt install fail2ban -y
```

Create the local jail configuration file

```
$ sudo vi /etc/fail2ban/jail.local
```

Add the following which will ignore local ip addresses and ban people for 24 hours.

```ini
[DEFAULT]
ignoreip = 127.0.0.1/8

# Ban for 24 hours (24 * 3600)
bantime = 86400
findtime = 600

banaction = iptables-multiport
```

Edit the SSHD jail configuration

```bash
$ sudo vi /etc/fail2ban/jail.d/sshd.conf
```

Set the following settings, remember to make sure that the SSH port number matches your configured port

```ini
[sshd]
enabled = true
port = 2222
mode = aggressive
maxretry = 4
```

Restart and check all is running OK

```bash
$ sudo systemctl restart fail2ban
```

To check the status of the JAILs

```bash
$ sudo fail2ban-client status
$ sudo fail2ban-client status sshd
```

From another server (not from home address) do a sanity check to see if you get banned

Run this command 4 times and you should get banned

```bash
$ ssh -p 2222 root@123.123.123.123
```

You can then unban an ip address like this

```bash
$ sudo fail2ban-client set sshd unbanip 23.34.45.56
```
