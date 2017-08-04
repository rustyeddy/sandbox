<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 1/5/16
 * Time: 5:02 AM
 */

namespace SierraHydrog\Goes;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $nids;
    protected $testdata;

    public static function setUpBeforeClass()
    {
        // copy the test data
        $res = shell_exec('tests/bin/setup-data');
    }

    public static function tearDownAfterClass()
    {
        // remove temporary test data
        $res = shell_exec('tests/bin/teardown-data');
    }

    public function setUp()
    {
        global $config;
        $fm = FileManager::getInstance();

        $testdata = "tmp/data/dcsdata";

        // MID
        $this->files['3A800160'] = [
            "3A800160-2015-06-28-115424",
            "3A800160-2015-06-28-225424",
            "3A800160-2015-07-01-025423",
        ];
        $this->files['CE94A736'] = [
            "CE94A736-2015-07-05-234942",
        ];

        // Utica
        $this->files['6A100576'] = [
            "6A100576_2015-12-24-104024",
            "6A100576_2015-12-24-124024",
            "6A100576_2015-12-24-114024",
        ];
    }

    public function testFilenameParser()
    {
        $fname = "CE94D1A6-2015-07-01-180557";

        $fm = FileManager::getInstance();
        $parsed = $fm->parseFilename($fname);
        $nesdis = $parsed['nesdisid'];
        $timestamp = $parsed['timestamp'];

        $this->assertEquals("CE94D1A6", $nesdis);
        $this->assertEquals("2015-07-01-180557", $timestamp);
    }

    public function testGetAllFiles()
    {
        // Test getting all the files
        $fm = FileManager::getInstance();
        $flist = $fm->getFiles(null, null);

        // There should be 2 nesdis
        $this->assertEquals(count($this->files), count($flist));

        // Check all keys we expect will be there.
        foreach(array_keys($this->files) as $nid) {

            // Make sure all the stations where found
            $this->assertArrayHasKey($nid, $flist);

            // Make sure each files were found the station
            $this->assertEquals(count($this->files[$nid]), count($flist[$nid]));
        }
    }

    public function testGetNesdis()
    {
        $nesdis = "3A800160";

        $fm = FileManager::getInstance();
        $flist = $fm->getFiles($nesdis, $this->testdata);
        $this->assertCount(1, $flist);
        $this->assertCount(count($this->files[$nesdis]), $flist[$nesdis]);
        return $flist;
    }

    /**
     * Test storing data in the archive directory.
     *
     * @depends testGetNesdis
     */
    public function testArchiveFile($flist)
    {
        $fm = FileManager::getInstance();

        $nes = array_shift($flist);
        $fparts = $nes[0];

        // Archive the file and test that it has been archived
        $newfile = $fm->archiveFile($fparts);
        $this->assertTrue(file_exists($newfile));

        // Also test it has been removed from the original directory
        $this->assertFalse(file_exists($fparts['fullpath']));

        $files = [$fparts['fullpath'], $newfile];
        return $files;
    }

    /**
     * Test renaming (moving) files to and fro.  We'll move the
     * archived file back to the original directory
     *
     * @depends testArchiveFile
     */
    public function testRename($files)
    {
        $fm = FileManager::getInstance();
        $fm->rename($files[1], $files[0]);

        $this->assertTrue(file_exists($files[0]));
        $this->assertFalse(file_exists($files[1]));
    }

    /**
     * Just make up a timestring to be saved as missing.
     */
    public function testSaveMissingTimeseries()
    {
        $missingTimeseries = "FakeStage.GOES@NOWHERE";

        $fm = FileManager::getInstance();
        $fm->saveMissingTimeseries($missingTimeseries);

        $allmissing = $fm->getMissingTimeseries();
        $this->assertTrue(in_array($missingTimeseries, $allmissing));
    }

}
