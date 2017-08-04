#!/usr/bin/env bash

echo "Provisioning ONOS workstation"
# apt-get update
who am i

# Install Openssh server
if [ ! -f /etc/ssh/sshd_config ]; then
    echo "#### Installing OpenSSH Server"
    sudo apt-get install -y openssh-server 
fi 

if [ ! -f /usr/bin/git ]; then
    echo "#### Installing git"
    sudo apt-get install -y git
fi

# First install java
if [ ! -f /usr/bin/java ]; then
    echo "### Installing Java"
    sudo apt-get install -y software-properties-common 
    sudo add-apt-repository -y ppa:webupd8team/java 
    sudo apt-get update 
    sudo apt-get install -y oracle-java8-installer oracle-java8-set-default 
fi

if [ ! -d "~/Downloads" ]; then
    echo "### Making dowloads directory"
    mkdir ~/Downloads
fi

if [ ! -d ~/Applications ]; then
    echo "### Making applications directory"
    mkdir ~/Applications
fi

cd ~/Downloads
if [ ! -d ~/Applications/apache-maven-3.3.1 ]; then
    echo "### get karaf"
    wget -nv http://download.nextag.com/apache/karaf/3.0.3/apache-karaf-3.0.3.tar.gz
    tar -zxvf apache-karaf-3.0.3.tar.gz -C ../Applications/
fi

if [ ! -d apache-karaf-3.0.3 ]; then
    echo "### get maven"
    wget -nv http://archive.apache.org/dist/maven/maven-3/3.3.1/binaries/apache-maven-3.3.1-bin.tar.gz
   tar -zxvf apache-maven-3.3.1-bin.tar.gz -C ../Applications/ 
fi

cd ~
if [ ! -d ~/onos ]; then
    echo "### clone and build onos"
    git clone https://gerrit.onosproject.org/onos
    echo "\n. ~/onos/tools/dev/bash_profile\n" >> ~/.bashrc
    . ~/onos/tools/dev/bash_profile
    cd ~/onos
    onos-build
    onos-setup-karaf
fi

## Now clone app samples and build
cd ~
if [ ! -d ~/onos-app-samples ]; then
    echo "### clone and build onos-app-samples"
n    git clone https://gerrit.onosproject.org/onos-app-samples
    cd ~/onos-app-samples
    mvn clean install
fi

## Now get and build mininet
cd ~
if [ ! -d ~/mininet ]; then 
    echo "### clone and build mininet"
    git clone https://github.com/mininet/mininet.git
    cd mininet
    sh util/install.sh
fi


