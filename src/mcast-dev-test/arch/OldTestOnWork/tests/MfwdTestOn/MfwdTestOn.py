# Mfwd test script

class MfwdTestOn:
    def __init__( self ):
        self.default = ''

    def CASE1( self, main ):
        """
        startup sequence:
        # Perform startup initialization
        # Starts the OnOs CLI
        # TODO: Installs mfwd app - support is unavailable in onosclidriver
        # Activates mfwd app
        """
        global globalONOSip, testDir
        globalONOSip = main.ONOSbench.getOnosIps()
        testDir = main.tests_path + "/" + main.TEST
        main.scale = ( main.params[ 'SCALE' ] ).split( "," )
        main.numCtrls = int( main.scale[ 0 ] )
        main.scale.remove( main.scale[ 0 ] )
        main.numCtrls = main.params[ 'CTRL' ][ 'num' ]
        main.ONOSip = []
        main.ONOSport = []
        main.CLIs = []
        for i in range( 1, int( main.numCtrls ) + 1 ):
            main.CLIs.append( getattr( main, 'ONOScli' + str( i ) ) )
            main.ONOSport.append( main.params[ 'CTRL' ][ 'port' + str( i ) ] )
        maxNodes = ( len( globalONOSip ) - 2 )
        for i in range( maxNodes ):
            main.ONOSip.append( globalONOSip[ i ] )

        main.step( "\nStart ONOS cli" )
        cliResult = main.TRUE
        cliResult = cliResult and \
                    main.CLIs[ 0 ].startOnosCli( main.ONOSip[ 0 ] )
        stepResult = cliResult
        utilities.assert_equals( expect=main.TRUE,
                                 actual=stepResult,
                                 onpass="Successfully started OnOs CLI",
                                 onfail="Failed to start OnOs CLI" )
        if not stepResult:
            main.log.info( "Exiting as unable to start OnOs CLI" )
            main.cleanup()
            main.exit()

        main.step( "\nActivate mfwd app if not already activated" )
        cliResult = main.TRUE
        appName = 'org.onosproject.mfwd'
        cliResult = cliResult and main.CLIs[ 0 ].activateApp( appName )
        stepResult = cliResult
        utilities.assert_equals( expect=main.TRUE,
                                 actual=stepResult,
                                 onpass="Successfully activated mfwd app",
                                 onfail="Failed to activate mfwd app" )
        if not stepResult:
            main.log.info( "Exiting as unable to activate mfwd app" )
            main.cleanup()
            main.exit()

    def CASE2( self, main ):
        import json
        import pexpect
        from MfwdTestOn.utils.util import verifyJoin
        """
        startup sequence:
        # Assumes that OnOs CLI is started and mfwd app is active
        # Add join by executing 'mcast-join' CLI command
        # Verify join is present in 'mcast-show -j' output
        # Delete join by executing mcast-delete CLI command
        # Verify deleted join is not present in output of 'mcast-show -j'
        # Add join by invoking mcast join REST API
        # Verify join is present in output of mcast show REST API
        # Delete join by invoking mcast delete REST API
        # Verify deleted join is not present in output of mcast show REST API
        """
        main.log.case( "Test mfwd app configurations using CLI and REST" )

        try:
            src = main.params['MFWD']['src']
            grp = main.params['MFWD']['grp']
            ingress = main.params['MFWD']['ingress']
            egress = main.params['MFWD']['egress']
        except KeyError:
            main.log.error( "Exiting due to incorrect MFWD config from params" )
            main.cleanup()
            main.exit()

        main.step( "\nAdd join by executing 'mcast-join' CLI cmd" )
        mcastJoin = 'mcast-join %s %s %s %s' % ( src, grp, ingress, egress )
        output = main.CLIs[ 0 ].sendline( cmdStr=mcastJoin )
        utilities.assert_equals( expect="Added the mcast route",
                                 actual=output,
                                 onpass="Executed 'mcast-join' CLI cmd",
                                 onfail="%s output of %s" % ( output,
                                                              mcastJoin ) )

        main.step( "\nVerify join is present in output of 'mcast-show -j'" )
        # Spilt with ' ' as delimiter to sepeate out egress points
        egressPoints = egress.split()
        mcastShowOutput = main.CLIs[ 0 ].sendline( cmdStr='mcast-show -j' )
        mcastShowOutputJson = json.loads( mcastShowOutput )
        showResult = main.FALSE
        skipCheck = False
        # KeyError will be reported if no joins are present
        try:
            grpFlows = mcastShowOutputJson['mcastgroup']
        except KeyError:
            # In case of KeyError fail as earlier step had added join(s)
            skipCheck = True
        if skipCheck is not True:
            showResult = verifyJoin( src, grp, ingress, egressPoints,
                                     grpFlows )
        utilities.assert_equals( expect=main.TRUE,
                                 actual=showResult,
                                 onpass="Verify passed for 'mcast-join' cmd",
                                 onfail="Verify failed for 'mcast-join' cmd" )

        main.step( "\nDelete join by executing 'mcast-delete' CLI command" )
        mcastDel = 'mcast-delete %s %s' % ( src, grp )
        output = main.CLIs[ 0 ].sendline( cmdStr=mcastDel )
        # Parse the response for success and failure
        utilities.assert_equals( expect="",
                                 actual=output,
                                 onpass="Executed 'mcast-delete' CLI cmd",
                                 onfail="%s output of %s" % ( output,
                                                              mcastDel ) )

        main.step( "\nVerify join is not present in 'mcast-show -j' output" )
        showResult = main.TRUE
        mcastShowOutput = main.CLIs[ 0 ].sendline( cmdStr='mcast-show -j' )
        mcastShowOutputJson = json.loads( mcastShowOutput )
        skipCheck = False
        # If there was a single group which was deleted, KeyError is reported
        # Consider KeyError as pass
        try:
            grpFlows = mcastShowOutputJson['mcastgroup']
        except KeyError:
            skipCheck = True
        # If output has groups, fail if deleted group, src is present
        if not skipCheck:
            for grpFlow in grpFlows:
                if ( ( src == grpFlow['source_address'] ) and
                     ( grp == grpFlow['group_address']  ) ):
                    showResult = main.FALSE
                    break
        utilities.assert_equals( expect=main.TRUE,
                                 actual=showResult,
                                 onpass="Verify passed for 'mcast-delete' cmd",
                                 onfail="Verify failed for 'mcast-delete' cmd" )

        main.step( "\nAdd join by invoking mcast join REST API" )
        result = main.FALSE
        for point in range( 0, len( egressPoints )-1 ):
            egressPorts = egressPoints[point] + ','
        egressPorts = egressPorts + egressPoints[ len( egressPoints ) - 1 ]
        joinURI = 'curl -X POST http://%s:%s/onos/mfwd/mcast/join?src=%s\&grp=%s\&ports=%s,%s\r\n' \
                  % ( main.ONOSip[0], main.ONOSport[0], src,
                      grp, ingress, egressPorts )
        response = pexpect.run( joinURI )
        if ( response.find( "Successfully Inserted" ) != -1 ):
            result = main.TRUE
        utilities.assert_equals( expect=main.TRUE,
                                 actual=result,
                                 onpass="Executed %s" % joinURI,
                                 onfail="%s received for cmd %s"
                                 % ( response, joinURI ) )

        main.step( "\nVerify join is present in output of show REST API" )
        showResult = main.FALSE
        showURI = 'curl -X GET http://%s:%s/onos/mfwd/mcast/show-all\r\n' \
                  % ( main.ONOSip[0], main.ONOSport[0] )
        mcastShowOutput = pexpect.run( showURI )
        mcastShowOutputJson = json.loads( mcastShowOutput )
        showResult = main.FALSE
        skipCheck = False
        try:
            grpFlows = mcastShowOutputJson['mcastgroup']
        except KeyError:
            skipCheck = True
        if skipCheck is not True:
            showResult = verifyJoin( src, grp, ingress, egressPoints,
                                     grpFlows )
        utilities.assert_equals( expect=main.TRUE,
                                 actual=showResult,
                                 onpass="Verify passed for join REST API",
                                 onfail="Verify failed for join REST API" )

        main.step( "\nDelete join by invoking mcast delete REST API" )
        result = main.FALSE
        delURI = 'curl -X DELETE http://%s:%s/onos/mfwd/mcast/delete?src=%s\&grp=%s\r\n' \
                 % ( main.ONOSip[0], main.ONOSport[0], src, grp )
        response = pexpect.run( delURI )
        if ( response.find( "Deleted flow for src %s and grp %s"
             % ( src, grp ) ) != -1 ):
            result = main.TRUE
        utilities.assert_equals( expect=main.TRUE,
                                 actual=result,
                                 onpass="Executed %s" % delURI,
                                 onfail="%s received for cmd %s"
                                 % ( response, delURI ) )

        main.step( "\nVerify join is not present in output of show REST API" )
        showResult = main.FALSE
        showURI = 'curl -X GET http://%s:%s/onos/mfwd/mcast/show-all\r\n' \
                         % ( main.ONOSip[0], main.ONOSport[0] )
        mcastShowOutput = pexpect.run( showURI )
        mcastShowOutputJson = json.loads( mcastShowOutput )
        showResult = main.TRUE
        skipCheck = False
        try:
            grpFlows = mcastShowOutputJson['mcastgroup']
        except KeyError:
            # If single join which was deleted, KeyError is reported hence
            # consider KeyError as pass
            skipCheck = True
        if skipCheck is not True:
            for grpFlow in grpFlows:
                # Check if src, grp and inPort matches
                if ( ( src == grpFlow['source_address'] ) and
                     ( grp == grpFlow['group_address'] ) ):
                    showResult = main.FALSE
                    break
        utilities.assert_equals( expect=main.TRUE,
                                 actual=showResult,
                                 onpass="Verify passed for delete REST API",
                                 onfail="Verify failed for delete REST API" )

    def CASE3( self, main ):
        import json
        import pexpect
        from MfwdTestOn.utils.util import verifyJoin
        """
        startup sequence:
        # Assumes that OnOs CLI is started and mfwd app is active
        # Add mcast configuration from input JSON file
        # Verifies join(s) are present in the output of 'mcast-show -j'
        """
        main.log.case( "Test addition of mcast joins from JSON file" )
        jsonFilename = testDir + "/" + main.params[ 'MFWD' ]['file']
        main.step( "Add mcast joins from JSON file" )
        main.log.step( "Passed input file is %s" % jsonFilename )
        result = main.FALSE
        jsonCurlCmd = 'curl -v -X POST --data @%s -H \
                       "Content-Type:application/json"  \
                       http://%s:%s/onos/mfwd/mcast/join-multicast\r\n' \
                       % ( jsonFilename, main.ONOSip[0], main.ONOSport[0] )
        response = pexpect.run( jsonCurlCmd )
        if ( response.find( "Successfully Inserted" ) != -1 ):
            result = main.TRUE
        utilities.assert_equals( expect=main.TRUE,
                                 actual=result,
                                 onpass="Executed %s" % jsonCurlCmd,
                                 onfail="%s received for cmd %s"
                                 % ( response, jsonCurlCmd ) )
        # If ( result is not main.TRUE ):
        # TODO: On exit from here, test case is not reported as FAIL, check it

        main.step( "Verify join(s) are present in output of show REST API" )
        # Open the JSON input file
        try:
            jsonFile = open( jsonFilename, 'r' )
        except IOError:
            main.log.info( "Open of %s failed with IOError" % jsonFilename )
            main.cleanup()
            main.exit()
        # Load from JSON input file
        try:
            joinsInFile = json.load( jsonFile )
        except ( ValueError, KeyError, TypeError ):
            main.log.step( "Exiting as load from %s failed" % jsonFilename )
            main.cleanup()
            main.exit()
        main.log.step( "Load from %s is successful" % jsonFilename )
        # Check if input JSON file has atleast 1 join
        try:
            fileGrpFlows = joinsInFile['mcastgroup']
        except KeyError:
            main.log.step( "Exiting as input JSON doesn't have 'mcastgroup'" )
            main.cleanup()
            main.exit()
        matchedJoins = 0
        showResult = main.FALSE
        # Get the output of mcast show REST API
        showCurlCmd = 'curl -X GET  http://%s:%s/onos/mfwd/mcast/show-all\r\n' \
                      % ( main.ONOSip[0], main.ONOSport[0] )
        showOutput = pexpect.run( showCurlCmd )
        # Fail if show output does not have join and input file has joins
        if ( ( showOutput.find( "mcastgroup" ) == -1 ) and
             ( len( fileGrpFlows ) > 0  ) ):
            main.log.step( "Fail as file has join but show output is empty" )
            main.cleanup()
            main.exit()
        # Check join(s) in file is present in output of show
        joinsInShow = json.loads( showOutput )
        grpFlowsFrmShow = joinsInShow['mcastgroup']
        egPoints = []
        for i in range( 0, len( fileGrpFlows ) ):
            showResult = False
            src = fileGrpFlows[i]['source_address']
            grp = fileGrpFlows[i]['group_address']
            inPort = fileGrpFlows[i]['ingress_point']['McastConnectPoint']
            inPoint = "%s/%d" % ( inPort['elementId'], inPort['portNumber'] )
            egPorts = fileGrpFlows[i]['egress_point']['McastConnectPoint']
            # Add the egress points to list in "%s/%d" format
            for port in range( 0, len( egPorts ) ):
                egPoints.append( "%s/%d" % ( egPorts[port]['elementId'],
                                             egPorts[port]['portNumber'] ) )
            showResult = verifyJoin( src, grp, inPoint, egPoints,
                                     grpFlowsFrmShow )
            if ( showResult == main.TRUE ):
                matchedJoins += 1
                main.log.debug( "Src %s Grp %s matched; match count %d"
                                % ( src, grp, matchedJoins ) )
            else:
                main.log.debug( "Src %s Grp %s not matched" % ( src, grp ) )
            # Cleanup egPoints for next join
            for point in range( 0, len( egPoints ) ):
                port = egPoints.pop()
        if ( len( fileGrpFlows ) == matchedJoins ):
            showResult = main.TRUE
        else:
            showResult = main.FALSE
        # Pass if all joins from input JSON were present in show-all REST API
        utilities.assert_equals( expect=main.TRUE,
                                 actual=showResult,
                                 onpass="Verify passed for loading from file",
                                 onfail="Verify failed for loading from file" )

    def CASE4( self, main ):
        import time
        import os
        import imp
        """
        startup sequence:
        starts mininet topology
        executes pingall
        """
        main.case( "Setting up Mininet topology" )
        # Local Variables
        topology = testDir + "/" + main.params['MININET']['topo']
        main.log.step( "Passed topology file is %s" % topology )
        topoResult = main.Mininet1.startNet( topoFile=topology )
        stepResult = topoResult
        utilities.assert_equals( expect=main.TRUE,
                                 actual=stepResult,
                                 onpass="Successfully started topology",
                                 onfail="Failed to start topology" )
        # Exit if topology did not load properly
        if not topoResult:
            main.cleanup()
            main.exit()

        main.step(" Ping all hosts" )
        pingResult = main.FALSE
        time1 = time.time()
        pingResult = main.Mininet1.pingall( timeout=60 )
        time2 = time.time()
        main.log.info( "Time for pingall: %2f seconds" % ( time2 - time1 ) )
        utilities.assert_equals( expect=main.TRUE, actual=pingResult,
                                 onpass="All hosts are reachable",
                                 onfail="Some pings failed" )
