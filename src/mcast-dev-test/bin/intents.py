#!/usr/bin/python

import urllib2
import json
import pprint

url = 'http://eddyconsulting.com:8181/onos/v1/intents'
response = urllib2.urlopen(url).read()
jstr = json.loads(response)

pprint.pprint(jstr)