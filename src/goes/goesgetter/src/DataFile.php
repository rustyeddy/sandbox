<?php
/**
 * Created by PhpStorm.
 * User: rusty
 * Date: 2/28/16
 * Time: 7:04 PM
 */

namespace SierraHydrog\Goes;


class DataFile
{
    // File Parts
    private $fullpath;
    private $dirname;
    private $extension;
    private $filename;

    // Site NESDIS ID
    private $siteName;
    private $nesdisId;
    private $timestamp;

    // Measurements
    private $measurements = null;

    // Log info
    private $info;

    // The file name alone
    private $error;

    /**
     * DataFile constructor. Create an object from a given file
     *
     * @param $fparts
     */
    public function __construct($fparts)
    {
        foreach ($fparts as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * @return mixed
     */
    public function getFullpath()
    {
        return $this->fullpath;
    }

    /**
     * @param mixed $fullpath
     */
    public function setFullpath($fullpath)
    {
        $this->fullpath = $fullpath;
    }

    /**
     * @return mixed
     */
    public function getDirname()
    {
        return $this->dirname;
    }

    /**
     * @param mixed $dirname
     */
    public function setDirname($dirname)
    {
        $this->dirname = $dirname;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param mixed $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    /**
     * @return mixed
     */
    public function getNesdisId()
    {
        return $this->nesdisId;
    }

    /**
     * @param mixed $nesdisId
     */
    public function setNesdis($nesdisId)
    {
        $this->nesdisId = $nesdisId;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return null
     */
    public function getMeasurements()
    {
        return $this->measurements;
    }

    /**
     * @param null $measurements
     */
    public function setMeasurements($measurements)
    {
        $this->measurements = $measurements;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }


}
