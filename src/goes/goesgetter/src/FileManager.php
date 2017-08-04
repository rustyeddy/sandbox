<?php

namespace SierraHydrog\Goes;

class FileManager {

	// The logger
	private $log;
	
    // place to find files to process
    private $newfiledir;

    // place to archive processed files
    private $archivedir;

	// This is a singleton
	private static $instance = null;

	// Files that need to be processed
	private $newFilelist = array();

    // Keep a list of the problem files
    private $problemFiles;

    // Keep the missing timeseries file name
    private $missingTimeseriesFile;

	// Single get an instance
	public static function getInstance() {
		if (null === static::$instance) {
			static::$instance = new FileManager();
			static::$instance->init();
		}
		return static::$instance;
	}

	// Prevent another instance of being created
	protected function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	// Initialize stuff for this class
	private function init($basedir = "") {
		global $config;
		global $log;

		if (!array_key_exists('archivedir', $config) ||
            !array_key_exists('newfiledir', $config)) {
            $log->error("archivedir and newfiledir must be defined in config");
            die("Define archivedir and newfiledir and try again!");
        }

        // Set the directories and determine if we need to create them
        foreach (['archivedir', 'newfiledir'] as $f) {
            $this->$f = $config[$f];
            if (!file_exists($this->$f)) {
                mkdir($this->$f, 0755, true);
            }
		}
	}

	/* -------------------- Public Functions ------------------ */

    /**
     * @param $fname
     * @param null $basedir
     * @return mixed
     */
    public function parseFilename($fname, $basedir = null) {
        global $log;

        $parts = pathinfo($fname);

        // Save the full path
        $fparts['fullpath'] = $fname;
        foreach ($parts as $k => $v) {
            $fparts[$k] = $v;
        }

        // Now save the NESDISID and Timestamps
        $basename = $parts['basename'];

        $fparts['nesdisid']  = substr($basename, 0, 8);
        $fparts['timestamp'] = substr($basename, 9);

        // Now save the project subdir
        if ($basedir) {
            $basdirs = explode('/', $basedir);
            $alldirs = explode('/', $parts['dirname']);

            $diff = array_diff($alldirs, $basdirs);
            $fparts['projectdir'] = implode('/', $diff);
        }
        return $fparts;
	}

    /**
	 * This function will search the _new files_ directory for
	 * files that match the glob directory.  The first paramenter
	 * is used as a glob pattern, it defaults to find everything '*'. 
	 * 
	 * @param  String glob string to match files, defaults to '*'
	 * @param  Directory to start the search from, defaults to dcsdir
	 * @return [list] of files matching criteria
	 */
	public function getFiles($nesdisid = null, $newfiledir = null, $count = -1)
    {
        global $config;
        global $log;

        if (!$newfiledir) {
            $newfiledir = $config['newfiledir'];
        }
        $log->debug("processing files from " . $newfiledir);

        if (!is_dir($newfiledir)) {
            $log->error($newfiledir . " is not a directory\n");
            return null;
        }

        // TODO: filter on NESDIS
        $allfiles = [];
        $dir = new \RecursiveDirectoryIterator($newfiledir,
            \RecursiveDirectoryIterator::SKIP_DOTS);

        $objs = new \RecursiveIteratorIterator($dir,
            \RecursiveIteratorIterator::SELF_FIRST);
        if (count($objs) <= 0) {
            $log->warn("No files to be found for: $nesdisid");
            return null;
        }

        foreach ($objs as $name => $obj) {
            $pos = $nesdisid ? strpos($name, $nesdisid) : true;
            if (is_file($name)) {
                if (!$nesdisid || $pos !== false) {
                    $allfiles[] = $name;
                    if ($count > 0) $count--;
                    if ($count == 0) {
                        break;
                    }
                }
            }
        }

        $log->debug("Found " . count($allfiles) . " files to process");

        $processFiles = array();
        foreach ($allfiles as $f) {
            $nid = null;
            $fparts = $this->parseFilename($f, $newfiledir);
            if ($fparts === null) {
                $log->debug("We had a problem with file: " . $f);
                $this->problemFiles[] = $f;
                continue;
            } else if (!$nesdisid) {
                $nid = $fparts['nesdisid'];
            } else {
                $nid = $nesdisid;
            }

            $fparts['nesdisId'] = $nid;
            $processFiles[$nid][] = new DataFile($fparts);
        }
        return $processFiles;
    }

 	/**
 	 * Rename the given file to a new file name, if the paths of the old and
 	 * new file differ the file will be moved drom the old to new folders.
	 *
 	 * @param  old name / path
 	 * @param  new name / path
 	 * @return [type]
 	 */
    public function rename($old, $new)
    {
        global $log;
    	$res = false;

        $log->info("rename " . $old . " to " . $new);
        if (file_exists( $old )) {
	        $res = rename( $old, $new );
            if ($res === false) {
                $log->error("Failed to rename " . $old . " to " . $new);
            }
        } else {
            print "Warning: $old does not exist\n";
        }
        return $res;
    }

    /**
     * Save processed files in the processed direct.
     * 
     * @param  string $path  relative to the storedir
     * @param  string $fname the name of the file (probably siteId)
     * @param  string $data  Data to be stored
     * @return boolean true/false based on success
     */
    public function storeData($path, $fname, $data) {
    	global $log;

    	$path = $this->archivedir . '/' . $path;

		if (! is_dir($path)) {
			$ok = mkdir($path, 0744, true);
			if ( !$ok ) {
				$log->error("could not create directory: " . $path);
				return false;
			}
		}

		$fname = $path . '/' . $fname;
		$c = file_put_contents($fname, $data, FILE_APPEND);
		if (false === $c) {
			$log->error("failed to write data to file " .
				$fname);
			return false;
		}
		$log->debug("wrote $c bytes to " . $path);
    }

    /**
     * Move a file to the failed dir.
     *
     * @param $fdata full path to be moved.
     * @return bool result of the move.
     */
    public function moveToFaildir($fdata)
    {
        global $config;
        global $log;

        $faildir = $config['faildir'];
        if (!file_exists($faildir)) {
            $res = mkdir($faildir);
            if (!$res) {
                die("Could not create fail dir: " . $faildir);
            }
        }
        

        $res = $this->rename($fdata->getFullpath(), $faildir . $fdata->getFilename());
        if ($res == false) {
            $log->error("Unable to move: " . $fdata->getFilename() . " to " .
                $faildir);
        }
    }

    /**
     * Archive a file that has been successfully processed.
     *
     * @param $fname - name of file to be archived
     * @return bool if the file was saved.
     */
	public function archiveFile($fdata) {
        global $config;
		global $log;

        $newloc = $this->archivedir;
        if (!file_exists($newloc)) {
            $log->debug("Archive directory does not exist, creating: " . $newloc);
            mkdir($newloc, 0755, true);
            assert(file_exists($newloc));
        }

        // XXX: May not work if the file does not come from newfile dir
        $old = $fdata->getFullpath();
        if (!file_exists($old)) {
            $log->debug("Can not move file, it was probably failed");
            return false;
        }

        $newloc .= $fdata->getFilename();
        $success = rename($old, $newloc);
        $log->debug("archiving: " . $old . " => " . $newloc . " " . $success);
        return $newloc;
   }

    /**
     * Copy a directory structure from one place to another.
     *
     * @param $from source directory
     * @param $to destication directory
     * @return bool true on success false on failure
     */
    public function copy($from, $to)
    {
        $success = copy($from, $to);
        return $success;
    }


    /**
     * @return string missing timeseries name.
     */
    public function getMissingTimeseriesFilename()
    {
        global $config;

        $fname = 'missing-timeseries.txt';
        if (!$this->missingTimeseriesFile) {
            if (array_key_exists('createTimeseries', $config)) {
                $fname = $config['createTimeseries'] . '/' . $fname;
            } else {
                $fname = $this->archivedir . '/' . $fname;
            }
            $this->missingTimeseriesFile = $fname;
        }
        return $this->missingTimeseriesFile;
    }

    /**
     * Save the missing files.
     *
     * @param $site
     * @return bool|int
     */
    public function saveMissingTimeseries( $tstring ) {
        global $config;
        global $log;

        $fname = $this->getMissingTimeseriesFilename();
        $res = file_put_contents($fname, $tstring, FILE_APPEND );
        if ($res === false) {
            $log->error("Unable to save missing timeseries: " . $tstring . " to file " . $fname);
            return false;
        }
        $log->debug("Saved missing timeseries " . $tstring . "to file, bytes: " . $res);
        return $res;
    }

    /**
     * @return array of missing timeseries strings
     */
    public function getMissingTimeseries()
    {
        $tslist = file($this->getMissingTimeseriesFilename());
        return $tslist;
    }
}
