#!/usr/bin/python

from mininet.log import debug

def verifyJoin( src, grp, ingress, egressPoints, destGrpFlows ):
    '''Verifies that passed src, grp, ingress and egress points are present
        in the passed JSON output of joins'''
    main.log.debug( "Verify src %s grp %s ingress %s" % ( src, grp, ingress ) )
    for grpFlow in destGrpFlows:
        # Check if src, grp and inPort matches
        if ( ( src == grpFlow['source_address'] ) and
             ( grp == grpFlow['group_address'] ) and
             ( ingress == "%s/%d" % ( grpFlow['ingress_point']['McastConnectPoint']['elementId'],
                                      grpFlow['ingress_point']['McastConnectPoint']['portNumber'] ) ) ):
            main.log.debug( "Verify egress ports" )
            try:
                for point in range( 0, len( egressPoints ) ):
                    match = False
                    for port in range (0, len( destGrpFlows ) ):
                        if( egressPoints[ point ] == "%s/%d"
                           % ( grpFlow['egress_point']['McastConnectPoint'][port]['elementId'],
                               grpFlow['egress_point']['McastConnectPoint'][port]['portNumber'] ) ):
                            match = True
                            break
                    # Egress ports didn't match
                    if ( match is not True ):
                        main.log.debug( "Breaking as egress %s didn't match"
                                        % egressPoints[ point ] )
                        return main.FALSE
                if( point == len( egressPoints ) - 1 ):
                    main.log.debug( "Returning true" )
                    return main.TRUE
            except IndexError:
                break
    return main.FALSE


def getIfNameFromNodeAndPort( nodename, n ):
    "Construct a canonical interface name node-ethN for interface n."
    return nodename + '-eth' + repr( n )

def getIpStripNetmask( ipAddrWithNetmask ):
    "Get the IP address stripping off netmask with '/' as delimiter"
    try:
        ipAddr = ipAddrWithNetmask.split( '/' )
        return ipAddr[0]
    except IndexError:
        debug( 'Invalid format of IP address with netmask' )
        return None

def getMininetSNameFromOnosUri( onosSwitchUri ):
    "Construct the mininet switch name from Onos switch URI"
    # Get the switch prefix with ':' as delimiter
    try:
        switchName = onosSwitchUri.split( ':' )
        if( len( switchName ) is not 2 ):
            debug( 'Invalid format of Onos switch uri' )
            return None
        prefixSname = switchName[0]
        switchNum = switchName[1].lstrip('0')
        mininetSwName = prefixSname + switchNum
        return mininetSwName
    except IndexError:
        debug( 'Invalid format of Onos switch uri' )
        return None

def getMininetSNameAndPortFromOnosUri( onosSwitchUri ):
    "Construct the mininet switch name and port from Onos switch URI"
    # Get the port number which is after '/' as delimiter
    try:
        sNamePort = onosSwitchUri.split( '/' )
        sName = sNamePort[0]
        sPort = sNamePort[1]
    except IndexError:
        debug( 'Invalid format of Onos switch uri' )
        return None, 0
    if( len(sNamePort) is not 2 ):
        debug( 'Invalid format of Onos switch uri' )
        return None, 0
    try:
        switchName = sName.split( ':' )
        if( len( switchName ) is not 2 ):
            debug( 'Invalid format of Onos switch uri' )
            return None, 0
        prefixSname = switchName[0]
        switchNum = switchName[1].lstrip('0')
        mininetSwName = prefixSname + switchNum
        return mininetSwName, int( sPort )
    except IndexError:
        debug( 'Invalid format of Onos switch uri' )
        return None, 0

def findHostAndIfaceFromIp( net, sourceIp ):
    "Find host with interface matching given IP and return that host and iface"
    for host in net.hosts:
        intfNameList = host.nameToIntf.keys()
        for ifName in intfNameList:
            debug( 'Ifname is %s\n' % ifName )
            iface = host.nameToIntf["%s" % ifName]
            ifIp = iface.IP()
            debug( 'Interface IP is %s\n' % ifIp )
            if( sourceIp == ifIp ):
                return host, ifName
    return None, None

def findNeighborFromSwitchPort( net, sname, portnum ):
    "Find hostname and interface which is connected to passed switch and port"
    # switch = net.nameToNode[sname]
    ifname = getIfNameFromNodeAndPort( sname, portnum )
    for link in net.links:
        if( link.intf1.name == ifname ):
            return link.intf2.node, link.intf2
        else:
            if( link.intf2.name == ifname ):
                return link.intf1.node, link.intf1
    return None, None
