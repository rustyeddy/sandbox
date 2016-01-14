#!/usr/bin/env python3

import lxc
import sys

class LXC_WordPress(object):
    """
    Provision an LXC container for WordPress
    """

    def __init__(self, name):
        self.name = name
        self.applist = []
        self.container = lxc.Container(self.name)

    def create_and_start(self):
        if not self.contianer.defined:
            print("Creating container %s" % self.name)
            self.container.create("download", lxc.LXC_CREATE_QUIET,
                { "dist": "ubuntu",
                  "release": "trusty",
                  "arch": "i386" })
        if not self.container.start():
            print("Error starting container", file=sys.stderr)
