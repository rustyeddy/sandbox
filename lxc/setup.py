#!/usr/bin/env python3

import lxc
import sys

c = lxc.Container("apicontainer")
if c.defined:
    print("Container already exists")
    #sys.exit(1)

# create the container
if not c.create("download", lxc.LXC_CREATE_QUIET,
                {"dist": "ubuntu",
                 "release": "trusty",
                 "arch": "i386"}):
    print("Container already created?");
    #sys.exit(1)

# start the container
if not c.start():
    print("Failed to start the container: ", file=sys.stderr)
    #sys.exit(1)

print("Container state: %s" % c.state)
print("Container PID: %s" % c.init_pid)

# Stop the container
if not c.shutdown(30):
    print("Failed to cleanly shutdown container, forcing")
    if not c.stop():
        print("Failed to kill the container", file=sys.stderr)
        sys.exit(1)

# Destroy the container
if not c.destroy():
    print("Failed to destroy the container. ", file=sys.stderr)
    sys.exit(1)

