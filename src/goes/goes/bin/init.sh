#!/bin/bash

goesbase='/srv/goes'
goesdata=${goesbase}/data
goesgetter=${goesbase}/getter

# Make sure lrgs client has been installed such that it can be
# copied to the container.
if [ ! -d ${goesdata} ]; then
    mkdir -p ${goesdata}/archive
fi

# Now prepare to create my own id
if [ ! -e getter/bin/adduser.sh ]; then
    user=`whoami`
    uid=`id -u`
    echo "adduser --uid ${uid} --gecos "" --disabled-login --disabled-password ${user}" > getter/bin/adduser.sh
    chmod +x getter/bin/adduser.sh 
fi
