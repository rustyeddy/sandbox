#!/bin/sh

#
# These are commands that should be run as the user.  Probably
# vagrant in our case.
#

# Should start using grunt or gulp?

if [ ! -d /home/vagrant/Downloads ]; then
    mkdir /home/vagrant/Downloads
fi

if [ ! -d /home/vagrant/Applications ]; then
    mkdir /home/vagrant/Applications
fi

cd /home/vagrant/Downloads
if [ ! -d /home/vagrant/Applications/apache-karaf-3.0.3 ]; then
    wget http://download.nextag.com/apache/karaf/3.0.3/apache-karaf-3.0.3.tar.gz
    tar -zxvf apache-karaf-3.0.3.tar.gz -C ../Applications/
fi

if [ ! -d /home/vagrant/Applications/apache-maven-3.3.1 ]; then
   wget http://archive.apache.org/dist/maven/maven-3/3.3.1/binaries/apache-maven-3.3.1-bin.tar.gz
   tar -zxvf apache-maven-3.3.1-bin.tar.gz -C ../Applications/ 
fi

cd /home/vagrant
if [ ! -d /home/vagrant/onos ]; then
    git clone https://gerrit.onosproject.org/onos
    echo -e "  # Source the ONOS profile" >> /home/vagrant/.bashrc
    echo -e "if [ -e /home/vagrant/onos/tools/dev/bash_profile ]; then" >> /home/vagrant/.bashrc
    echo -e " . /home/vagrant/onos/tools/dev/bash_profile" >> /home/vagrant/.bashrc
    echo -e "fi" >> /home/vagrant/.bashrc
    . /home/vagrant/onos/tools/dev/bash_profile
fi

# build onos as vagrant
if [ -d /home/vagran/onos ]; then
    cd /home/vagrant/onos
    . /home/vagrant/onos/tools/dev/bash_profile
    onos-build
    onos-setup-karaf
fi

## Now get and build mininet
cd /home/vagrant
if [ ! -d /home/vagrant/mininet ]; then 
    git clone https://github.com/mininet/mininet.git
    cd /home/vagrant/mininet
    sudo sh util/install.sh
fi


## Get mcast-dev-test  TODO: figure out how to get this private repo.
#if [ ! -d /home/vagrant/mcast-dev-test ]; then
#    sudo -EH -u vagrant -- git clone https://rustyeddy@bitbucket.org/sdnmcast/mcast-dev-test.git
#fi

cd /home/vagrant
if [ ! -d /home/vagrant/src ]; then 
   mkdir src
fi

# iperf
cd /home/vagrant/src
if [ ! -d /home/vagrant/src/iperf ]; then 
   git clone https://github.com/esnet/iperf.git
   cd /home/vagrant/src/iperf
   ./configure; make; make install
fi

cd /home/vagrant/src
if [ ! -d /home/vagrant/src/mtraf ]; then 
    git clone https://github.com/sdnmcast/mtraf.git
    cd mtraf; make
fi
