Tell FWD to Ignore Multicast Packets
====================================

The following command can be used to tell the fwd application to ignore multicast packets (so mfwd can handle) them.  This command can also be used instead of the patch I had sent out a couple weeks ago.

As matter of fact, this command MUST be used for multicast packets to forward properly when forward is being used.

~~~
onos> cfg set org.onosproject.fwd.ReactiveForwarding ignoreIPv4Multicast true
~~~