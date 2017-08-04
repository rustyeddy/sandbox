root@hermosa:~/mdt# ovs-dpctl dump-flows | grep 227.1.1.1

# Flowing
skb_priority(0), in_port(17), eth_type(0x0800):
    ipv4(src=10.1.1.1/255.255.255.255,dst=227.1.1.1/255.255.255.255,
    proto=1/0,tos=0/0,ttl=1/0,frag=no/0xff),
    packets:1, bytes:98, used:0.724s, actions:19,16,20

skb_priority(0),in_port(6),eth_type(0x0800),
	ipv4(src=10.1.1.1/255.255.255.255,dst=227.1.1.1/255.255.255.255,
	proto=1/0,tos=0/0,ttl=1/0,frag=no/0xff),
	packets:1, bytes:98, used:0.724s, actions:9
	
skb_priority(0),in_port(14),eth_type(0x0800),
	ipv4(src=10.1.1.1/255.255.255.255,dst=227.1.1.1/255.255.255.255,
	proto=1/0,tos=0/0,ttl=1/0,frag=no/0xff),
	packets:1, bytes:98, used:0.724s, actions:11
	
skb_priority(0),in_port(2),eth_type(0x0800),
	ipv4(src=10.1.1.1/255.255.255.255,dst=227.1.1.1/255.255.255.255,
	proto=1/0,tos=0/0,ttl=1/0,frag=no/0xff),
	packets:1, bytes:98, used:0.724s, actions:5

# Being Punted
root@hermosa:~/mdt# ovs-dpctl dump-flows | grep 227.1.1.1
skb_priority(0),in_port(17),eth_type(0x0800),
	ipv4(src=10.1.1.1/0.0.0.0,dst=227.1.1.1/0.0.0.0,
	proto=1/0,tos=0/0,ttl=1/0,frag=no/0xff,
	packets:7, bytes:686, used:0.068s,
	actions:userspace(pid=4294950891,slow_path(controller))
