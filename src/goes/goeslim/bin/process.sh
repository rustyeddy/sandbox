#!/bin/bash

echo "";
echo `date`;
echo "=============== Processing data ==================" ;
curl "http://localhost/api/v1/measurements";

echo "";
echo `date`;
echo "=============== Creating timeseries ==============";
curl "http://localhost/api/v1/timeseries";

echo "";
echo `date`;
echo "=============== Storing in AQUARIUS ==============";
curl "http://localhost/api/v1/aquarius";

echo "";
echo `date`;
echo "=============== All Done ===================";

