#!/usr/bin/python

import sys
import pprint
sys.path.append(".")

from onos import OnosRest

class OnosFlows( OnosRest ):
    "Examine flows"
    
    flows = {}

    def getFlows( self ):
        url = self.url + "flows/"
        self.sendRequest( url )
        json = self.jsonData['flows']
        for j in json:
        	f = new Flow(j)
        	self.flows['id'] = f
        pprint.pprint(self.flows)

    def printFlows( self ):
        for a in self.flows:
        	print a

class FLow():
	"""
	An ONOS FLow
	"""

	def	__init__()



if __name__ == "__main__":
	flows = OnosFlows()
	flows.getFlows()

