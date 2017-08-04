#! /usr/bin/env python

# scapy.contrib.description = PIM/PIMv2
# scapy.contrib.status = loads

from scapy.all import *

# Only supporting Hello and Join/Prune options
_PIM_Pkt_Types = { 0 : "Hello",
                   3 : "Join/Prune" }

class PIM(Packet):
    """PIM Message Class for v2.

    This class is derived from class Packet. You  need to "pimize"
    the IP and Ethernet layers before a full packet is sent.
    a=Ether(src="00:01:02:03:04:05")
    b=IP(src="1.2.3.4")
    c=PIM_Hdr(type=0)
    c.pimize(b, a)
    print "Joining IP " + c.gaddr + " MAC " + a.dst
    sendp(a/b/c, iface="en0")

    Parameters:
      type    PIM type field, 0x11, 0x12, 0x16 or 0x17
      mrtime  Maximum Response time (zero for v1)
      gaddr   Multicast Group Address 224.x.x.x/4
      
    See RFC4601, Section 2. Introduction for definitions of proper 
    IGMPv2 message format   http://www.faqs.org/rfcs/rfc2236.html

    """
    name = "PIM Header"
    fields_desc = [ 
                    BitField("version", 2, 4),
                    BitField("type", 0, 4),
                    ByteField("reserved", None),
                    XShortField("chksum", None)
                   ]

    def post_build(self, p, pay):
        p += pay
        if self.chksum is None:
            ck = checksum(p)
            p = p[:2]+chr(ck>>8)+chr(ck&0xff)+p[4:]
        return p

    def mysummary(self):
        """Display a summary of the PIM object."""
        if isinstance(self.underlayer, IP):
            return self.underlayer.sprintf("PIM: %IP.src% > %IP.dst% %PIM_Hdr.version% %PIM_Hdr.type%")
        else:
            return self.sprintf("PIM %PIM_Hdr.version% %PIM_Hdr.type%")

    def pimize(self, ip=None, ether=None):
        """Called to explicitely fixup associated IP and Ethernet headers
        The function adjusts the IP header based on conformance rules 
        and the group address encoded in the IGMP message.
        The rules are:
        1. Send all PIM packets except Register and Register-Stop to 224.0.0.13 with TTL 1
        2. TODO: Send Register, Register-Stop as unicast
 
        Parameters:
        self    The instantiation of an PIM class.
        ip      The instantiation of the associated IP class.
        ether   The instantiation of the associated Ethernet.

        Returns:
        True    The tuple ether/ip/self passed all check and represents
                a proper PIM packet.
        False   One of more validation checks failed and no fields 
                were adjusted.

        The function will examine the PIM message to assure proper format. 
        Corrections will be attempted if possible. The IP header is then properly 
        adjusted to ensure correct formatting and assignment. The Ethernet header
        is then adjusted to the proper PIM packet format.
        """
        if ip != None and ip.haslayer(IP) and ether != None and ether.haslayer(Ether):
            if self.type != 1 and self.type != 2:
                ip.dst = "224.0.0.13"
                ip.ttl=1
                ether.dst= "01:00:5e:00:00:0d"
                retCode = True
        else:
            retCode = True
        return retCode

    def extract_padding(self, s):
        return "", s

class Hello_Options_TLV(Packet):
    name = "PIM Hello Options TLV"
    fields_desc = [ShortField("type", 0 ),
                   # FieldLenField("len", None, length_of="val", fmt="B"),
                   FieldLenField("len", None, length_of=lambda x: x.val),
                   StrLenField("val", "", length_from=lambda x: x.len)]

    def guess_payload_class(self, p):
        return conf.padding_layer

class PIM_HelloOp_HoldTime(Hello_Options_TLV):
    name = "PIM Hello HoldTime Option"
    fields_desc = [ShortField("type", 1),
                   ShortField("len", 2),
                   ShortField("holdtime", 105)]

class PIM_HelloOp_LANPruneDelay(Hello_Options_TLV):
    name = "PIM Hello LAN PruneDelay Option"
    fields_desc = [ShortField("type", 2),
                   ShortField("len", 4),
                   ShortField("delay",500),
                   ShortField("interval",2500)]

class PIM_HelloOp_DRPriority(Hello_Options_TLV):
    name = "PIM Hello DR Priority Option"
    fields_desc = [ShortField("type", 19),
                   ShortField("len", 4),
                   IntField("priority", 1)]

class PIM_HelloOp_GenerationId(Hello_Options_TLV):
    name = "pim hello generation id option"
    fields_desc = [ShortField("type", 20),
                   ShortField("len", 4),
                   IntField("id",12133)]

# Only options supported by this packet generation
_PIM_Hello_OptionClasses = { 1 : "PIM_HelloOp_HoldTime",
                             2 : "PIM_HelloOp_LANPruneDelay",
                            19 : "PIM_HelloOp_DRPriority",
                            20 : "PIM_HelloOp_GenerationId" }

def _HelloGuessPayloadClass(p, **kargs):
    """ Guess the correct Hello Option class for a given payload """

    cls = conf.raw_layer
    # if len(p) >= 4:
    typ = struct.unpack("!H", p[0:2])[0]
    clsname = _PIM_Hello_OptionClasses.get(typ, "Hello_Options_TLV")
        # print "%s" % clsname
    cls = globals()[clsname]
    return cls(p, **kargs)

class PIM_Hello_Options(Packet):
    name = "PIM Hello Options"
    fields_desc = [PacketListField("options", [], _HelloGuessPayloadClass)]

    def extract_padding(self, s):
        return "", s

# IPv4 support only
class EncodedUnicast(Packet):
    name = "Encoded Unicast Address"
    fields_desc = [ByteField("family", 1),
                   ByteField("encoding", 0),
                   IPField("address", "0.0.0.0")]

class EncodedGroup(Packet):
    name = "Encoded Group Address"
    fields_desc = [ByteField("family", 1),
                   ByteField("encoding", 0),
                   BitField("B",0,1),
                   BitField("rsrvd",0,6),
                   BitField("Z",0,1),
                   ByteField("mask", 32),
                   IPField("grp", "0.0.0.0")]

class EncodedSource(Packet):
    name = "Encoded Source Address"
    fields_desc = [ByteField("family", 1),
                   ByteField("encoding", 0),
                   BitField("rsrvd",0,5),
                   BitField("S",0,1),
                   BitField("W",0,1),
                   BitField("R",0,1),
                   ByteField("mask", 32),
                   IPField("src", "0.0.0.0")]
      
class PIM_JoinPrune_Grp(Packet):
    name = "PIM Join/Prune Mcast Group"
    fields_desc = [ByteField("family", 1),
                   ByteField("encoding", 0),
                   BitField("B",0,1),
                   BitField("rsrvd",0,6),
                   BitField("Z",0,1),
                   ByteField("mask", 32),
                   IPField("grp", "0.0.0.0"),
                   FieldLenField("numjoins", None, fmt="!H", count_of="joinedsrcs"),
                   FieldLenField("numpruns", None, fmt="!H", count_of="prunedsrcs"),
                   PacketListField("joinedsrcs", [], EncodedSource,
                                    count_from=lambda pkt: pkt.numjoins,
                                    length_from=lambda pkt: pkt.numjoins * 8),
                   PacketListField("prunedsrcs", [], EncodedSource,
                                    count_from=lambda pkt: pkt.numpruns,
                                    length_from=lambda pkt: pkt.numpruns * 8)]

class PIM_JoinPrune(Packet):
    name = "PIM Join/Prune"
    fields_desc = [
                   ByteField("family", 1),
                   ByteField("encoding", 0),
                   IPField("neighbor", "0.0.0.0"),
                   ByteField("reserved", 0),
                   FieldLenField("numgrps", None, fmt="!B", count_of="mcastgroups"),
                   ShortField("holdtime",210),
                   PacketListField("mcastgroups", [], PIM_JoinPrune_Grp,
                                    count_from=lambda pkt: pkt.numgrps)]
# TODO: length_of may be used in case of packet dissection.
# Add length_from considering numjoins, numprunes of each group

    def extract_padding(self, s):
        return "", s

bind_layers(IP, PIM, proto=103)
bind_layers(PIM, PIM_Hello_Options, type=0)
bind_layers(PIM, PIM_JoinPrune, type=3)


