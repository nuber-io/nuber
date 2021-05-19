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

curl https://www.nuber.io/container-setup.sh --output container-setup.sh

if [ ! -f ./container-setup.sh ]; then
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

# Push the file and run the instllation
lxc file push container-setup.sh nuber-app/root/install 
lxc exec nuber-app -- chmod +x /root/install 
lxc exec nuber-app -- /root/install 

# Post config
lxc config device add nuber-app proxy-3000443 proxy listen=tcp:0.0.0.0:3000 connect=tcp:127.0.0.1:443
lxc config device add nuber-app proxy-80808080 proxy listen=tcp:0.0.0.0:8080 connect=tcp:127.0.0.1:8080

echo
ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}');
echo "In your browser open https://$ip:3000/install"

# This only prepares 

echo 
read -p "Do you want to setup a network bridge? (y/n) " -n 1 -r
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



read -p "Setup network bridge using '$interface' interface? (y/n) " -n 1 -r
echo # blank line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    sudo nmcli con add ifname nuberbr1 type bridge con-name nuberbr1
    sudo nmcli con add type bridge-slave ifname "$interface" master nuberbr1
fi

echo "Starting the Bridge Network (experimental)"
echo "=========================================="
echo 
echo "You will need to start the bridge network manually, this involves stopping the existing"
echo "connection, and then starting the bridge connection."
echo 
echo "The bridge network is configured as DHCP, so if you have a static IP address configured"
echo "in your hosts network settings, this will be ignored. "
echo 

# You need to start the bridge network like this, setting the connection name
# $ sudo nmcli con down <name of internet connection>; wait ; sudo nmcli con up nuberbr1

connection=sudo nmcli device | grep "$interface" | awk -- '{printf $1}'
if [ -z "$connection" ] 
then 
    echo  "Error: Unable to get network connection"
    exit 1
fi

echo "$ sudo nmcli con down $connection; wait ; sudo nmcli con up nuberbr1"