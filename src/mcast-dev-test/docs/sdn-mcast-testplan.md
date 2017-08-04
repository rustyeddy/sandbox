ONOS MCast Development Test Plan
================================

Overview
--------

This document is the test plan for developing the ONOS mcast test plan. This test plan will categorize our tests into three elements:

1. Unit tests using Junit
2. Protocol and API Validation tests
3. End to end System tests

### Unit tests

Are written to verify that every public method of each given class behaves just as it was designed to.  These tests are part of the ONOS application source code itself, they are written in Java using the JUnit framework.  These type of tests are often called _white box tests_. 

All tests are run every time the application is compiled. All unit tests must pass before code can be reviewed or committed. 

Ideally there should be one or more test cases for every Java class method, depending on the possible outcome of a given method.  However, it should be noted that too much testing

### Protocol and API Validation Tests

These tests are written to verify that all external interactions, through the CLI, REST API and protocol message handling behave correctly.  This type of testing is referred to as _black box testing_ all test procedures exist outside of the ONOS Mcast source code and treat the _Device Under Test (DUT)_ as a black box.

These tests will be written in _python_ with the use of _mininet_, _Scapy_ and _pytest_.  _mininet_ is used to create the desired topologies, represent network entities and connect to the controller.

The tests will verify the functionality REST and CLI calls directly.  Protocol interactions will be performed using Scapy to emulate PIM and IGMP messages to ensure that we are handling protocol messages correctly. 

### System Tests

The system tests are intended to test the software development as a whole network.  Basically, these tests will create a virtual network with the help of mininet, and for certain tests, routing code whether it is Cisco GNS3 or Quagga.  We'll pass traffic, alter the routing tables and verify traffic has converged as expected.

These tests will be part of the testing framework as the _Protocol and API Validation Tests_.


