<?php


$usbizdata_csv2sql = array(

    'Company Name'              => 'companyName',
    'Email'                     => 'companyEmail',
    'SIC Code'                  => 'sicCode',
    'SIC Code Description'      => 'sicCodeDescription',
    'SIC Code6'                 => 'sicCode6',
    'SIC Code 6'                => 'sicCode6',
    'SIC Code6 Description'     => 'sicCode6Description',
    'SIC Code 6 Description'    => 'sicCode6Description',
    'NAICS Code'                => 'naicsCode',
    'Contact Name'              => 'contactName',
    'First Name'                => 'firstName',
    'Last Name'                 => 'lastName',
    'Title'                     => 'title',
    'Address'                   => 'address',
    'Address2'                  => 'address2',
    'City'                      => 'city',
    'State'                     => 'state',
    'Zip'                       => 'zip',
    'Phone'                     => 'phone',
    'Fax'                       => 'fax',
    'Company Website'           => 'companyWebsite',
    'Revenue'                   => 'revenue',
    'Annual Revenue'            => 'revenue',
    'Employees'                 => 'employees',
    'Industry'                  => 'industry',
    'Desc'                      => 'description',
    'County'                    => 'county'
);

class usbizdata extends Importer
{
    $csv2sql    = $usbizdata_csv2sql;
    $dbtable    = 'usbizdata';

    
}

?>