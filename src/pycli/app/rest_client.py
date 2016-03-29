import json
import urllib2

class RestClient(object):
    """A Rest client base class"""
    url = None
    json = None

    def __init__(self):
        pass

    def url(self, url):
        self.url = url

    def geturl(self, url):
        retstr = urllib2.urlopen(url)
        self.json = json.load(retstr)

