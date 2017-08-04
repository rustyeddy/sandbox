The GOES Getter System
----------------------

This documents the GOES Getter system that will grab data fromt he
NOAA GOES/LRGS systems and insert that data into the AQUARIUS
Timeseries Manager.

The remainder of the document explains the system, it's architecture
and how it all works.  This system has been developed with a
_microservices_ approach using _docker_ containers.

Basic Workflow
--------------

1. Data is gathered by the LRGS client library based on configuration
   files and site IDs.

2. The collected data is parsed into a single sheet that consists of:
   location, 1 hours worth of data and the values for all of the
   location sensors.

3. Each _"sheet"_ of data is passed to a message bus that will then be
   processed by:

   1. Stored in a local database for analysis
   2. Queued up and sent to AQUARIUS
   3. Possibly saved in a local filesystem or sent to S3

4. The appropriate UI's and logging will be provided for configuration
   and analysis, etc.

Services
--------

### Data Grabber

The _Data Grabber_ uses the publicly available OPEN DCS toolkit to
grab data from the NOAA GOES/LRGS data servers, it parses the data
into data _"sheets"_, then writes them off to REDIS for future
processing.

#### Required Software

- Java (OpenJDK or Oracle???)
- https://dcs1.noaa.gov](https://dcs1.noaa.gov)
- Language used: php7 (hopefully move to Clojure? since JVM)
- git lfs for storing LRGS Java client

Getting the LRGS Open Project:

1. Goto [https://dcs1.noaa.gov]
2. Left menubar click: _'Sytem Information'_
3. Lower right hand column click under _"LRGS Information"_:
   1. click: _"LRGS Client Software Download"_

_NOTE:_ You can find the user guide and protocol specification for the
LRGS client software in the same location.

#### Services Used

- _REDIS_: to send parsed messages
- _MongoDB_: to access site information and sensor data
- _Logger_: to log stuff
- _SiteManager_: to access site information and add/modfiy Sites
