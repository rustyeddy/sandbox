GOES Getter Software
====================

The GOESGetter software was designed to gather data from the GOES/LRGS system using the [DCS Toolkit](http://sutron.com/dcstoolkit), process the data then insert it into the [AQUARIUS Timeseries Software](http://aquamaticsinformatics.com/aquarius).

DCS Toolkit
-----------

DCS Toolkit is sold and maintained by the Sutron Corp and provides many capabilities of which we are really just using the _Retrieval Process_.  Basically the retreival process allows us to schedule periodic transfers of data form the GOES/LRGS satalite system into our AQUARIUS server.

### Projects and Sites

We have grouped a series sites together under a project directory with the project acronymn as the name of the directory.  Data for each station is downloaded once per hour and saved in the project directory.

Data retrieved by DCSToolkit is stored in the following root directory:

>/srv/dcsdata/<project-id>

_Example:_ new data waiting to be processed for Madera Irrigation District is stored in:

>/srv/dcsdata/mid

__TO DO:__ I need to change the MID accronymn since Madera and Merced both use MID.  Also, I need to sort out the files being saved to the _'mid'_ directory into the respective directories for _Madera_ and _Merced_.

#### Data File naming convention

Each site has one hours worth of data is stored in a separate file.  The file naming convention for each file is stored as follows:

> NESDISID_YYYY-MM-DD-hhmmss

_Example:_ Data stored for station ___WST___ (NESDIS ID: CE94D1A6) is stored in files that look like:

```sh
rusty@goesgetter:/srv/dcsdata/mid$ pwd
/srv/dcsdata/mid

rusty@goesgetter:/srv/dcsdata/mid$ ls -1
...
CE94D1A6_2016-01-22-200557
CE94D1A6_2016-01-22-210557
CE94D1A6_2016-01-22-220557
CE94D1A6_2016-01-22-230557
...

```

Each file contains one hours worth of data, in most cases the data has a similar format, but there are occasions where some stations format data a little different.

___WST___ uses a very typical format to store it's data which looks something like:

```
rusty@goesgetter:/srv/dcsdata/mid$ cat CE94D1A6_2016-01-22-210557

CE94D1A616023020557G39+0NN088WUP00070"
25.93 25.93 25.93 25.93 
39.8 39.9 39.9 39.7 
:BATTLOAD 0 13.71  

```

In this case the data is as follows: 

* The first line is the _header_, which encodes things like:
+ The second and third lines are sensor for the hour at 15 minute increments
+ The forth line is the _Battery Load_ (good guess right?)

__The Header:__ The first line of data files is always the header.  This header tells us things like: 

* NESDIS ID
* Timestamp data was collected
* Various other flags like, failure code, signal strength, data length, etc. 

The header basically encapsulates everything we need to know about the retrieved data, except for 1 very import piece of information, that is what the following data represents.

__The Data:__ We have no way of knowing what the data in the data file represents simply by looking at the file.  Therefore we need the help of an external piece of informtion defined in the _PDT_.  

More on that on the PDT in a second.

__The Battery Load:__ In this specific case it is easy to figure out what the fourth line of the data file represents, the _Battery Load_.  Almost all (all?) files have a line that represents the battery.

However, the data produced for the battery may be the _Batteries Voltage Level_ or it may represent the _Battery Under Load_.  This particular piece of information can come in a variety of different formats, often it is represented by a single numeric value for the entire hour.

### The PDT

Back to the PDT, or knowing exactly what the data in the data file means, or specifically what sensors are used to produce the data that we have received.  

To do this we have to refer to the PDT that corresponds to each individual station we are tracking and translate that PDT into an electronic version we use in the _GOES Getter Software_:

The file is in the standard _JSON_ file formate understood by pretty much every computer language that exists today.   

Here we show the entry for ___WST___ in the file _etc/sites.json_ that will tell our program information about the site ___WST___, specifically the __NESDIS ID__ and the __shefOrder__ which in turn tells us what the sensor data represents.

```json
rusty@goesgetter:/srv/goesgetter$ cat etc/sites.json 
[
    ...

    {
        "siteName": "WST",
        "mnemonic": "WST",
        "projectId": "UNKNOWN",
        "pdtType": "ACOE",
        "nesdisId": "CE94D1A6",
        "decodingScheme": "Oldest First",
        "scanInterval": "15",
        "shefOrder": "PC, TA, VX",
        "latitude": "372658",
        "longitude": "-1193859"
    },

    ...

]
```

Here we can see the _NESDIS ID_ for ___WST___ is _CE94D1A6_ and we will use the __shefOrder__ to know the sensor data in the above file is:

* _PC:_ Culmulative Pricipitation
* _TA:_  Air Temprature
* _VX:_ Battery/Voltage Load

These codes also let us know, implicitly, what units each of the measurements use.

