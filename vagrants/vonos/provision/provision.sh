#!/usr/bin/env bash

start_seconds="$(date +%s)"

echo "Provisioning ONOS workstation"

VHOME='/home/vagrant'

sudo apt-get update
sudo apt-get -y -f install

# Install Openssh server
sudo apt-get install -y openssh-server
sudo apt-get install -y git git-core git-review

# First install java
echo "Installing Java"
sudo apt-get install software-properties-common -y
sudo add-apt-repository ppa:webupd8team/java -y
sudo apt-get update
sudo echo oracle-java7-installer shared/accepted-oracle-license-v1-1 select true | `sudo /usr/bin/debconf-set-selections`
sudo apt-get install oracle-java8-installer 
sudo apt-get install oracle-java8-set-default -y

# Install some other important things
sudo apt-get install wireshark -y

# Run the following script as vagrant
sudo -EH -u vagrant -- /bin/sh /vagrant/provision/user.sh

# Chown of vagrant home to vagrant
chown -R vagrant.vagrant /home/vagrant

#
# Other things to get:
#   git review
#   iperf
#   mtraf
#   wireshark
