#!/bin/bash
#
# Nuber.io
# Copyright 2020 - 2021 Jamiel Sharief.
#
echo '
    _   __      __             
   / | / /_  __/ /_  ___  _____
  /  |/ / / / / __ \/ _ \/ ___/
 / /|  / /_/ / /_/ /  __/ /    
/_/ |_/\__,_/_.___/\___/_/     
'                                 

if [ ! -f /snap/bin/lxd ]; then  # /usr/bin/lxd
    echo "LXD is not installed"
    exit 1
fi

# User needs to be member of group to use LXD
if  ! groups $USER | grep -q '\blxd\b' ; then
    echo "User needs to be member of 'lxd' group"
    echo "e.g. sudo adduser ${USER} lxd"
    exit 1
fi


# Create the container
image='images:ubuntu/focal/amd64'
if arch | grep 'aarch64' ; then
  image='images:ubuntu/focal/arm64'
fi
if  ! lxc launch $image nuber-app ; then
    echo "Error creating instance"
    exit 1
fi


curl https://www.nuber.io/container-setup.sh --output container-setup.sh
if [ ! -f ./container-setup.sh ]; then
    exit 1
fi

# Push the file and run the instllation
lxc file push container-setup.sh nuber-app/root/install 
lxc exec nuber-app -- chmod +x /root/install

# On production server installing in VM, there was a delay from creating the container before it had
# internet acesss this caused for it fail
echo "waiting...."
sleep 10
lxc exec nuber-app -- /root/install 

# Open Port
function askPort
{
    read -p "Which port should nuber be setup on? [3000]: " port
    port=${port:-3000}
    if [[ $port -lt 1024 || $port -gt 49151 ]] ; then
        echo "Port number must be between 1024 - 49151"
        echo
        askPort port;
    fi
}

askPort port
lxc config device add nuber-app proxy-"$port"443 proxy listen=tcp:0.0.0.0:"$port" connect=tcp:127.0.0.1:443

echo
ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}');
echo "In your browser open https://$ip:$port/install"

# TODO: move to host-setup.sh
echo
echo 
read -p "Do you want to install the ZFS kernel module and ZFS utils package (y/n) [y]" answer
answer=${answer:-y}
echo # blank line
if [[ $answer =~ ^[Yy]$ ]]
then
    sudo apt install -y zfsutils-linux
    sudo modprobe zfs
    sudo sh -c "echo 'zfs' >> /etc/modules"
fi

echo
echo 
read -p "Do you want to setup a bridged network connection? (y/n) [n]" -n 1 -r
echo # blank line
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit
fi

sudo apt install -y bridge-utils network-manager

interface=$(ip route get 8.8.8.8 | awk -- '{printf $5}')
if [ -z "$interface" ] 
then 
    echo  "Error: Unable to get network interface"
    exit 1
fi

read -p "Setup bridged network using '$interface' interface? (y/n) [n]" -n 1 -r
echo # blank line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    sudo nmcli con add ifname nuber-bridged type bridge con-name nuber-bridged
    sudo nmcli con add type bridge-slave ifname "$interface" master nuber-bridged
fi

echo "Starting the Bridge Network (experimental)"
echo "=========================================="
echo 
echo "You will need to start the bridged network manually, this involves stopping the existing"
echo "connection, and then starting the bridge connection."
echo 
echo "The bridged network is configured as DHCP, so if you have a static IP address configured"
echo "in your hosts network settings, this will be ignored. "
echo 

# You need to start the bridge network like this, setting the connection name
# $ sudo nmcli con down <name of internet connection>; wait ; sudo nmcli con up nuber-bridged

connection=$(sudo nmcli device | grep "$interface" | awk -- '{printf $1}')
if [ -z "$connection" ] 
then 
    echo  "Error: Unable to detect network connection interface"
    exit 1
fi

echo "$ sudo nmcli con down $connection; wait ; sudo nmcli con up nuber-bridged"