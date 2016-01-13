<?php

/*
 * Copyright (c) Eddy Consulting, LLC 2010 
 */

class Uploader 
{
    private $tmpdir	= 'tmp/';
    private $workingdir	= 'working/';
	
    private $error = '';
    private $html;
    
    private $addid 	= false;
    private $startid 	= 0;

    function __construct()
    {
    }

    /**
     * The intent of the destructor is to free up the memory consummed
     * by the headers and records. 
     */
    function __destruct()
    {
    }

    function get_records()
    {
        return $this->records;	
    }
    
    function get_headers()
    {
        return $this->headers;
    }
    
    function add_id($start = 0)
    {
        $this->addid = true;
        $this->startid = $start;
    }

    function loadfile($filename = '')
    {
        $fh = $this->openfile($filename);
        $this->loadrecords($fh);
        fclose($fh);
	    
        return $this->records;
    }
    
    /**
     * Load a file for processing
     * 
     * @param unknown_type $fname
     * @return unknown_type
     */
    function openfile($filename = '')
    {
        error_reporting(E_ALL);
	
        if ($filename != '') {
            $target_path = $filename;
        } else {
            $fname = basename( $_FILES['file']['name']);
            
            $target_path = 'tmp/' . $fname;
            
            // Move the uploaded csv file from tmp to the working directory
            if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
                $this->html = "The file ".  basename( $_FILES['file']['name']).
                    " has been uploaded";
            } else {
                die ("There was an error uploading the file, please try again!");
            }
        }
	    
        // Open the csv file, and get it ready for processing
        $fh = fopen($target_path, "r");
        if ($fh == false) {
            die ("Error reading imported file");
        }
	
        return $fh;
    }

    /**
     * Load the .csv file into this MLS_List object.
     * 
     * @param $target_path
     * @return unknown_type
     */
    function loadrecords($fh)
    {
        // Start gulping up the csv file
        $headers = array();
        
        // Read each of the lines and create Real Properties
        $headers = fgetcsv($fh);
        $delim = ',';
        if (count($headers) == 1) {
        	$headers = preg_split("/\s+/", $headers[0]);
        	$delim = "\t";
        }
        
        /*
         * XXX: Need to remove the spaces from the headers.
         */
        $nhdrs = array();
        foreach ($headers as $hdr) {
        	$hdr = str_replace (" ", "", $hdr);
        	$hdr = str_replace ("#", "", $hdr);
        	$nhdrs[] = $hdr; 
        }
		$this->headers = $nhdrs;

        $count = 0;
        while ($line = fgetcsv($fh, 0, $delim)) {
        	try {
				/*
        		$prop = $this->mapper($headers, $line);
        		$listing = new MLSListing($prop);
        		$listing->setId($count);
        		*/
        		$record = array();
        		$hdrs = $nhdrs;
        		
        		if ($this->addid) {
        			$record['id'] = $this->startid + $count;
        		}
        		
        		while(!empty($hdrs)) {
        			$hdr = array_shift($hdrs);
            		$val = array_shift($line);
            	
				    $record[$hdr] = $val;
        		}
        
        		$count++;
        		$this->records[] = $record;
        	} catch (Exception $e) {
        		echo "Caught exception: ", $e->getMessage(), "\n";
        	}
        }
        
        return $this->records;
    }
}

?>
