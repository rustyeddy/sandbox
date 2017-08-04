#!/usr/bin/env python
'''
Created on 23-Oct-2012

@authors: Anil Kumar (anilkumar.s@paxterrasolutions.com),
          Raghav Kashyap(raghavkashyap@paxterrasolutions.com)



    TestON is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    TestON is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with TestON.  If not, see <http://www.gnu.org/licenses/>.


Utilities will take care about the basic functions like :
   * Extended assertion,
   * parse_args for key-value pair handling
   * Parsing the params or topology file.

'''
import re
from configobj import ConfigObj
# from core import ast as ast
import smtplib

import email
import os
import email.mime.application

class Utilities:
    '''
       Utilities will take care about the basic functions like :
       * Extended assertion,
       * parse_args for key-value pair handling
       * Parsing the params or topology file.
    '''

    def parse_args(self,args, **kwargs):
        '''
        It will accept the (key,value) pair and will return the (key,value) pairs with keys in uppercase.
        '''
        newArgs = {}
        for key,value in kwargs.iteritems():
            if isinstance(args,list) and str.upper(key) in args:
                for each in args:
                    if each==str.upper(key):
                        newArgs [str(each)] = value
                    elif each != str.upper(key) and (newArgs.has_key(str(each)) == False ):
                        newArgs[str(each)] = None

        return newArgs

    def parse(self,fileName):
        '''
        This will parse the params or topo or cfg file and return content in the file as Dictionary
        '''
        self.fileName = fileName
        matchFileName = re.match(r'(.*)\.(cfg|params|topo)',self.fileName,re.M|re.I)
        if matchFileName:
            try :
                parsedInfo = ConfigObj(self.fileName)
                return parsedInfo
            except StandardError:
                print "There is no such file to parse "+fileName
        else:
            return 0


if __name__ != "__main__":
    import sys

    sys.modules[__name__] = Utilities()
