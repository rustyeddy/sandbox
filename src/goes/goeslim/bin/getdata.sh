#!/bin/bash

user=$1
pass=$2

#export JAVA_HOME=/usr/local/jdk1.8.0_102

echo "Getting data U: " ${user} "P: " ${pass} " " `date -Ihour` >> /srv/logs/getlog.log

prog=/srv/LrgsClient/bin/getDcpMessages
sites=/srv/goeslim/etc/all-realtime.sc
fname=/srv/goesdata/datablocks/goes-`date -Iseconds`
server=cdadata.wcda.noaa.gov 

$prog -u $user -P $pass -h $server -f $sites -a '====' -x -t 120 > $fname

# Now process the data...
echo "Finished gathering data: " `date -I` >> /srv/logs/getlog.log
