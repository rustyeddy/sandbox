GOES Commands
=============

Working
-------

* /api/config/list

* /api/process/count/[{siteId}]
* /api/process/store/[siteId]/[count=N]/[dryrun=true]

* /api/site/list
* /api/site/describe/{siteId}
* /api/site/missing

* /api/location/list

* /api/location/id
* /api/location/describe

### GET timeseries

* /api/timeseries/list
* /api/timeseries/describe{tstr}
* /api/timeseries/id/{tstr}
* /api/timeseries/location/{locstr}
* /api/timeseries/parameters

### DELETE timeseries

* /api/timeseries/delete

--- Not yet working cli & api --- 

* /api/timeseries/create/{tstr}
* /api/timeseries/append/{tstr}/{data}




