#!/usr/bin/env python

import psutil
import pprint

# Get the CPU times for all of the CPU's
times = psutil.cpu_times(True)
pprint.pprint(times)

# % pcu for all CPU's
perc = psutil.cpu_percent(interval=1, percpu=True)
pprint.pprint(perc)

cpu_count = psutil.cpu_count(logical=False)
print (cpu_count);

vm = psutil.virtual_memory()
pprint.pprint(vm)

swap = psutil.swap_memory()
pprint.pprint(swap)

## Start here with disks!