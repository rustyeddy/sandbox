<?php

class BackupBuddy {

	public $site;

   	/*
     * Backup info
     */
    public $backups;
    public $_backup_summary;
    public $_relative_backup_path = '/wp-content/uploads/backupbuddy_backups/';
    
    /**
     * The constructor
     */
    public function __construct( $site )
    {
    	$this->site = $site;
    }

    /**
     * Parse a Backup Buddy backup name.
     *
     * XXX This is broken for domain names that have a hyphen in them
     */
    private function _bb_regex( $bbname )
    {
        $foo = explode( '-', $bbname );

        $matches = array();
        if ( count ( $foo ) == 6 ) {
            $matches = array(
                'site'              => $foo[1],
                'date'              => $foo[2],
                'time'              => $foo[3],    
                'type'              => $foo[4],
                'fuzz-zip'          => $foo[5],
            );
        } else {
            $matches = array(
                'site'              => $foo[1],
                'date'              => $foo[2],
                'type'              => $foo[3],
                'fuzz-zip'          => $foo[4],
            );
        }
        return $matches;
    }

    private function _get_backup_dir()
    {
        $bdir = $this->site->wpdir . $this->_relative_backup_path;
        return $bdir;
    }

    public function get_backups()
    {
        $bdir = $this->_get_backup_dir();

        if ( ! file_exists( $bdir ) ) {
            $this->_backup_summary['info'] = "No backups";
            return $this->_backup_summary;
        }

        $files = scandir( $bdir );
        if ( count ( $files ) < 1 ) {
            $this->_backup_summary['info'] = "No backups";
            return null;
        }

        $bupfiles = preg_grep( "/^backup\-*/", $files );
        if ( count ( $bupfiles ) < 1 ) {
            $this->_backup_summary['info'] = "No backups";
            return null;
        }

        $total_size = 0;
        $bburl = $this->site->siteurl . $this->_get_backup_dir();

        foreach ( $bupfiles as $b ) {

            $this->_backup_summary[ 'count' ]++;

            $size = filesize( $bdir . $b );
            $total_size += $size;
            $m = $this->_bb_regex( $b );

            $d = $m[ 'date' ];
            $t = $m[ 'type' ];

            if ( $t == 'full' && $d > $this->_backup_summary[ 'lastfull' ] ) {
                $this->_backup_summary[ 'lastfull' ] = $d;
                $this->_backup_summary[ 'fullurl' ] = $bburl . $b;
            } else if ( $t == 'db' && $d > $this->_backup_summary[ 'lastdb' ] ) {
                $this->_backup_summary[ 'lastdb' ] = $d;
                $this->_backup_summary[ 'dburl' ] = $bburl . $b;
            }

            $this->_backups[ $b ] = array(
                'size'  => $size,
                'date'  => $d,
                'type'  => $t,
                'url'   => $bburl . $b,
                'dir'   => $bdir . $b,
            );
        }

        $this->_backup_summary[ 'totalsize' ] = format_bytes( $total_size );
        return $this->_backups;
    }

    public function get_backup_summary()
    {
        $this->_backup_summary = array(
            'lastfull'          => "",
            'lastdb'            => "",
            'totalsize'         => 0,
            'info'              => "",
            'fullurl'           => "",
            'dburl'             => "",
            'count'             => 0,
        );

        $this->get_backups();
        return $this->_backup_summary;
    }

}