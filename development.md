# Development

These are the steps required to setup the docker container for development.

## Setup

Download the source code

```bash
$ git clone https://github.com/nuber-io/nuber
```

Download dependencies and setup the environment for the installation.

```bash
$ cd nuber
$ composer install
$ bin/install
$ npm install ./websocket
```

Create the database and tables, also setup the database for testing.

```bash
$ bin/console db:setup
$ bin/console db:test:prepare
```

Build the container

```bash
$ docker-compose build
```

## Usage

Once you have built the docker image, you can work with it like this

To start the container

```bash
$ docker-compose up
```

Check this works fine
[https://localhost:3000](https://localhost:3000)

Once the Docker container has been started, you can go into it by running the following command:

```bash
$ docker-compose exec nuber bash
```

Enable debug mode by setting the `APP_DEBUG` environment variable to `true`, you may need to delete `config/.env.php` which is the cached version.

```
$ nano config/.env
```

Once your done open your browser and goto `https://localhost:3000/install`. You should get a certificate warning because the certificate self signed, proceed anyway.

> If you are using Google Chrome, you will need to enable self signed certs by opening `chrome://flags/#allow-insecure-localhost`

Follow the instructions, and on the next page enter the IP address and following the instructions for configuring the LXC host. LXD can't be installed in Docker, I use virtual box with a Ubuntu server and a fixed IP address, e.g. 192.168.1.150.

## LXD

To setup the LXD host

```bash
lxc config set core.https_address "[::]:8443"
lxc config set core.trust_password "00000000-0000-0000-0000-000000000000"
```

To see instance logs

```
$ lxc info alpine --show-log
```

LXD logs

```
$ sudo cat /var/snap/lxd/common/lxd/logs/lxd.log
```

Checking out SNAP journal

```
$ journalctl -u snap.lxd.daemon -n 100
```

To list container info on ZFS

```
$ zfs list
```

Whilst on BTRFS, go to the directory

```
$ /btrfs/containers/alpine
```

To see BTRFS usage

```
$ sudo btrfs fi show
Label: none  uuid: 916f6d6a-c243-477c-b43b-20d12eb15518
	Total devices 1 FS bytes used 10.88GiB
	devid    1 size 53.50GiB used 21.03GiB path /dev/sda3
```

To enable debug mode, if you reall get stuck then run the command and check the logs

```
sudo snap set lxd daemon.debug=true; sudo systemctl reload snap.lxd.daemon
sudo tail -f /var/snap/lxd/common/lxd/logs/lxd.log
```
