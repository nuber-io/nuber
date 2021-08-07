#!/bin/bash
#
# Nuber.io
# Copyright 2020 - 2021 Jamiel Sharief.
#
# This has been moved to its own script and is designed to be run on the LXD host

echo 
read -p "(experimental) Do you want to setup a bridged network connection? (y/n) [n]" -n 1 -r
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

read -p "Setup the bridged network connection using '$interface' interface? (y/n) [n]" -n 1 -r
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