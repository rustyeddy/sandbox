#!/usr/bin/python

import sys
sys.path.append(".")

from onos import OnosRest

class OnosApps( OnosRest ):
    "Handle applications"
    
    apps = {}                   # Map the app by the app name

    def getApps( self ):
        url = self.url + "applications/"
        self.sendRequest( url )
        apps = self.jsonData['applications']
        for app in apps:
            s = app['name'].split('.')
            n = s[2]
            self.apps[n] = {}
            self.apps[n]['name']        = app['name']
            self.apps[n]['id']          = app['id']
            self.apps[n]['state']       = app['state']
            self.printApps( True )

    def printApps( self, active = True ):
        for a in self.apps:
            if self.apps[a]['state'] != "ACTIVE": 
                continue
            print "%22s: %-40s %2i %s" % ((a, 
                                           self.apps[a]['name'],
                                           self.apps[a]['id'],
                                           self.apps[a]['state']))

if __name__ == "__main__":
	apps = OnosApps()
	apps.getApps()



