OVS Configurations
==================

To add a mininet switch to a physical interface.  The problem here is that interface becomes unreachable.

# Add uber to switch s1
sudo ovs-vsctl add-port s1 eth0
sudo ovs-vsctl show | grep eth0
sudo ifconfig s1 10.1.1.102 netmask 255.255.255.0 up
sudo ifconfig eth0 10.1.1.102 netmask 255.255.255.0 up

# On goober to switch s2 
sudo ovs-vsctl add-port s2 eth2
sudo ovs-vsctl show  | grep eth2
sudo ifconfig s2 10.1.1.104 netmask 255.255.255.0 up
sudo ifconfig eth2 10.1.1.104 netmask 255.255.255.0 up

mcast-join 10.1.1.104/32 227.1.1.1/32 of:0000000000000001/5 of:0000000000000003/1 of:0000000000000004/1 of:0000000000000002/1

