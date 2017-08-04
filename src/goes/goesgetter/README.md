GOES Getter
==============

GOES Getter is a program that retrieves GOES Geoatmospheric data from the NOAA LRGS data collection sites.  The data collected is then placed in AQUARIUS for further analysis and comparison.

Aquarius currently stores two types of _TimeSeries_ data:

* _Field Visits_: water measured during field visits
* _DCP_: data collected from _Data Collection Platforms_
* _GOES_: data collected from the GOES satalite collection system.


Requirements
--------------

- LrgsClient
  - Install Oracle version of Java: I did 1.8 (8.0)
  - Grab LrgsClient from NOAA [https://dcs1.noaa.gov](https://dcs1.noaa.gov)

- MongoDB
  - apt install MongoDB-server
  - mongodb/mongodb:
    - composer require "mongodb/mongodb=^1.0.0"

- php-curl
  - apt install php-curl

References
------------

- [http://mongodb.github.io/mongo-php-library/](PHP MongoDB library)


Flow Chart
----------

### __Preparation:__

	1. Setup DCS toolkit to download to files in a particular format
	2. Configuration file for all sites and sensor code order

### Getting Data From NOAA GOES/LRGS System

#### __DCSToolkit:__ has been deprecated

	1. Sendor datafiles are stored in a configurable directory
	2. Files are named: yyyy-mm-dd-hhmmss-nesdisId
	3. Files have a timestamp as all files do for when they are written to disk

#### __LrgsClient__ has replaced __DCSToolKit__

    1. Update to the protocol and required passwords
    2. ./bin/getDcpMessages
    3. [https://dcs1.noaa.gov] -> ‘System Information (Left menubar) -> LRG Information (last section 2nd column)

### __GoesGetter:__ scans directory for new files:

1. GoesGetter scans configured dir foreach new file:

2. Parse file name for:
	1. NESDISID
	2. Capture date/time

3. Parse Sensor File
	1. Locate Parser for station
	2. Parse Time Stamp
	3. Parse Data for each sensor

4. Insert sensor data into AQUARIUS
	1. Authenticate with AQIARIUS
	2. Foreach Sensor

		1. Derive the TSIdentifier
			2. Map the SHEF code to the long description

		2. Determine the numeric TSIdentifier (AQ)
			3. If TSIdentifier == 0 then
				4. Create the TimeSeries

		1. Foreach data point
			1. Prepares data point into AQUARIUS csvbyte[] format
			2. Insert data into AQUARIUS
			3. Save the AppendToken for later deletion if needed?
			4. Log the transaction

5. Archive sensor file
	1. Move sensor file to the _processed_ directory
	2. Zip up the directory with the sensor data


Data Sets we Are Interested In
------------------------------

#### REST

* GetAuthToken
* GetLocationDescriptionList
* GetTimeSeriesDescriptionList

#### SOAP

* GetAuthToken
* GetAllLocations
* GetTimeSeriesList
* GetTimeSeriesListForLocation
*

##### Write

* CreateLocation
* CreateTimeSeries2
* AppendTimeSeriesListForLocation
* AppendTimeSeriesFromBytes2
* AppendAndMerge

Schemas
-------

~~~
GetAllLocations:
- ()
return:
- LocationId 		(long)
- LocationName 		(string)
- Identifier
+ NesdesId
~~~

~~~
CreateTimeSeries:
- Identifier		(string: "sensor long@GOES.siteId)
return:
- TimeSeriesID		(long)
~~~

~~~
GetTimeSeriesList:
- LocationID		(long)
- Parameter			(String: "HG")
return:
- AQDataID
- Num Points
- Start / End dates
~~~

~~~
GetTimeSeriesListForLocation:
- LocationID		(long)
return:
- AQDataID
- Num Points
- Start / End dates
~~~

~~~
AppendTimesSeriesFromBytes2
- TimeSeriesID		(long)
- csvbytes			(CSV YYYY-MM-DD HH:MM:SS, nnn.mmm, fff, ggg, iii, aaa, note)
- userName			(string)
- comment			(string)
return:
- appendResult		(string: token to delete the series)
~~~

~~~
UndoAppend
- identifier		(string: timeseriesId string)
- AppendToken		(string: token returned from AppendTimeSereisFromBytes2)
return:
- numPointsDeleted	(integer)
~~~
