#!/usr/bin/python

import urllib2
import json
import pprint

class OnosRest:
	"""
	Handle ONOS rest requests, responses, then handle parsing
	the JSON responses and turning them into an dict that we
	can more easily use
    """

	url = 'http://eddyconsulting.com:8181/onos/v1/'
	response = []
	jsonData = {}
	jsonString = ''

	apps = []

	def sendRequest(self, url):
		self.response = urllib2.urlopen(url).read()		
		self.jsonData = json.loads(self.response)
		return self.jsonData
