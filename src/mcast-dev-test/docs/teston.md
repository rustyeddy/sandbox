TestOn 
======

    TestON framework is an end to end automation framework for testing the
Openflow/SDN components. TestON solution aims to interact with OpenFlow/SDN 
components and automate the functionality of the components.

* [TestOn Tutorial](https://wiki.onosproject.org/display/ONOS/TestON+Tutorial?src=breadcrumbs-parent)
  
* [Installing TestOn](https://wiki.onosproject.org/display/ONOS/Installation)

* [TestOn files](https://wiki.onosproject.org/display/ONOS/TestON+Files)

* [How to write a TestOn test?](https://github.com/opennetworkinglab/OnosSystemTest/wiki/How-to-write-a-TestOn-test)

* [How to execute TestOn test?](https://wiki.onosproject.org/display/ONOS/Running+TestON)

Additional Configuration
=========================

* On running a testcase, if you face following error:
`Traceback (most recent call last):
  File "/usr/lib/python2.7/threading.py", line 810, in __bootstrap_inner
    self.run()
  File "./cli.py", line 525, in run
    self.test_on = TestON(self.options)
  File "/home/yogesh/ONLabTest/TestON/core/teston.py", line 134, in __init__
    self.componentInit(component)
  File "/home/yogesh/ONLabTest/TestON/core/teston.py", line 164, in componentInit
    driverModule = importlib.import_module(classPath)
  File "/usr/lib/python2.7/importlib/__init__.py", line 37, in import_module
    __import__(name)
  File "/home/yogesh/ONLabTest/TestON/drivers/common/cli/onosdriver.py", line 23, in <module>
    from requests.models import Response
ImportError: No module named requests.models`

   workaround is to comment `from requests.models import Response` in 
ONLabTest/TestON/drivers/common/cli/onosdriver.py
    
* Define environment defaults like _ONOS_ROOT_, _ONOS_USER, 
or change the default environment variables in `envDefaults`
file located at `_ONOS_ROOT/tools/build`

