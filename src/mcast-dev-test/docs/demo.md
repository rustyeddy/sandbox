Demo
-------
* Restart the machine 

* Shutdown eth0 and eth1
~~~
ifconfig eth0 down
ifconfig eth1 down
~~~

* Verify the arp and route table are empty

~~~
arp -an
route -n
~~~

Both table should be empty.

* Configure an IP address on eth0 and add a default route to that
  interface.  

~~~
ifconfig eth0 10.1.1.25/24
route add -net default dev eth0
~~~

* Verify the route table looks like:

~~~
Kernel IP routing table
Destination     Gateway         Genmask         Flags Metric Ref    Use Iface
0.0.0.0         0.0.0.0         0.0.0.0         U     0      0        0 eth0
10.1.1.0        0.0.0.0         255.255.255.0   U     0      0        0 eth0
~~~

With nothing else in it.

* Bring onos up

~~~
cd ~/onos; karaf clean
~~~

* Bring up Mininet and pingall, and install mcast route

~~~
cd ~/mdt; sudo mn -c; sudo ./demo.py
mininet> pingall

onos> mcast-join 10.1.1.11/32 227.1.1.1/32 of:0000000000000001/1 of:0000000000000002/1 of:0000000000000003/1 of:0000000000000004/5

~~~

* Verify onos can see the Mininet devices.  This may take a couple
  minutes so be patient.

~~~
onos> devices
~~~

We should see the four switches.

* Test the ONOS gui.
** Start firefox and browse to 

~~~
http://10.1.1.25:8181/onos/ui
~~~

* Boot wireless router (plug it in).  Wait for a few seconds and try
  pinging it.  It is ready when it can be pinged.

~~~
ping 10.1.1.200
~~~

* Attach eth1 to s4 in Mininet

~~~
~directv/mdt/bin/ovs-add-ports
~~~  

Check your work: 

~~~
root@malibu:/home/directv/mdt# ifconfig eth1
eth1      Link encap:Ethernet  HWaddr 00:05:1b:a2:bf:ad  
          inet6 addr: fe80::205:1bff:fea2:bfad/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:152 errors:0 dropped:1 overruns:0 frame:0
          TX packets:38 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:13422 (13.4 KB)  TX bytes:5901 (5.9 KB)

root@malibu:/home/directv/mdt# ifconfig s4
s4        Link encap:Ethernet  HWaddr 00:05:1b:a2:bf:ad  
          inet addr:10.1.1.102  Bcast:10.1.1.255  Mask:255.255.255.0
          inet6 addr: fe80::703d:f8ff:fee4:6cda/64 Scope:Link
          UP BROADCAST RUNNING  MTU:1500  Metric:1
          RX packets:158 errors:0 dropped:0 overruns:0 frame:0
          TX packets:8 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:0 
          RX bytes:49536 (49.5 KB)  TX bytes:648 (648.0 B)
~~~


WARNING
-------

* The default gateway may have been replaced.  If so remove it and put
back the default route we want on eth0.

~~~
route del -n default gw 10.1.1.200
route add -n default dev eth0
~~~

* remove ip address from s1, s2 & s3 (NOT s4)

~~~
ifconfig | grep 10.1.1
ifconfig | less
~~~

Search every sY-ethY that has an IP address assigned, then remove it

~~~
ifconfig sX-ethY 0.0.0.0
~~~

* Check on the following address are configured on the host machine. 

~~~
root@malibu:/home/directv/mdt# ifconfig | grep 10.1.1
          inet addr:10.1.1.25  Bcast:10.1.1.255  Mask:255.255.255.0
          inet addr:10.1.1.102  Bcast:10.1.1.255  Mask:255.255.255.0
~~~

* Now check pings from mininet to wireless network.

~~~
mininet> h1 ping 10.1.1.200
PING 10.1.1.200 (10.1.1.200) 56(84) bytes of data.
64 bytes from 10.1.1.200: icmp_seq=2 ttl=64 time=2.18 ms
~~~

* And the wireless network back to mininet

~~~
ping 10.1.1.11
~~~

You should get a response back from mininet.

* Test access from wireless laptop browser to ONOS gui on host
  machine.  This will indicate that we are able to access the rest api
  from the wireless network.

~~~
http://10.1.1.25:8181/onos/ui
~~~

You should see the network topology.

* Start the video server on h1

~~~
mininet> xterm h1
~directv/mdt/bin/start-video h1-eth0
~~~

* Start apache

~~~
/opt/lampp/lampp startapache 
~~~

NOTE: make sure you run _start-video_ on the h1 xterm.

* With wireless device join network with password.

~~~
DIRECTV-SDN-5
password: 1234554321
~~~

* Test starting video from wireless device browser.

~~~
http://10.1.1.25/dtv/channel.php
~~~

And push the button.


Troubleshooting
---------------


