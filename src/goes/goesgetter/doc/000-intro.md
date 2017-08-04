GOES Getter Software
====================

The GOESGetter software was designed to gather data from the GOES/LRGS system using the [DCS Toolkit](http://sutron.com/dcstoolkit), process the data then insert it into the [AQUARIUS Timeseries Software](http://aquamaticsinformatics.com/aquarius).

DCS Toolkit
-----------

DCS Toolkit is sold and maintained by the Sutron Corp and provides many capabilities of which we are really just using the _Retrieval Process_.  Basically the retreival process allows us to schedule periodic transfers of data form the GOES/LRGS satalite system into our AQUARIUS server.

In a nutshell, data is retrieved every hour for every station we are gathering data for (eventually every station we are monitoring).

The data retrieved every hour is stored as a file on the DCSToolkit server [goesgetter.com](http://goesgetter.com) in the directory 

>/srv/dcsdata

The data is then processed by the _GOES Getter Software_ to interpret the data, reformat it, look out for errors and store the data into AQUARIUS.