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
if  ! lxc launch ubuntu:20.04 nuber-app ; then
    echo "Error creating instance"
    exit 1
fi

# Download the container installation script (run inside the container)
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

ip=$(ip route get 8.8.8.8 | awk -F"src " 'NR==1{split($2,a," ");print a[1]}'); # $(curl ipinfo.io/ip)

askPort port
lxc config device add nuber-app proxy-"$port"443 proxy listen=tcp:0.0.0.0:"$port" connect=tcp:127.0.0.1:443
echo 
echo "It is highly recommened that you only allow the web interface to be accessed from trusted IP addresses."
echo "Use the following command, replacing x.x.x.x with the public IP address of your home or work internet connection."
echo "If your IP address changes then you will need to delete the existing rule first."
echo 
echo "> sudo iptables -I INPUT -p tcp ! -s x.x.x.x --dport $port -j REJECT"

echo
echo "In your browser open https://$ip:$port/install"