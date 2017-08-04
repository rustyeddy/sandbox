<?php

namespace SierraHydrog;

class AquariusRest {

    use ContainerTrait;
	
	private static $_instance = null;
	private $_url = '';
	private $_curl = null;
	private $_token = null;
	private $_cookieFile = null;

	private $response = null;

    /**
     * @return instance of this singleton.
     */
	public static function getInstance()
	{
		if (null === AquariusRest::$_instance) {
            AquariusRest::$_instance = new AquariusRest();
		}
		return AquariusRest::$_instance;
	}

    /**
     * Do not allow another to create a REST object.
     */
	private function __constructor() {
	}

    /**
     * @return the AQUARIUS URL.
     */
	function getAquariusUrl() {
		$config = $this->settings();
        if ($this->_url == null) {
            $this->_url = 'http://' . $config['aquarius'] . '/AQUARIUS/Publish/V2/';
        }

		return $this->_url;
	}

    /**
     * Setup curl to make our REST requests.
     *
     * @return null|resource null if failed curl otherwise
     */
    function get( $url ) {
		$config = $this->settings();

		$url = $this->getAquariusUrl() . $url;
		if ( null === $this->_token ) {
			$t = $this->getAuthToken();
			if ( $t == null ) {
				die ("Could not Authorize rest command.\n");
			}
		}

		$curl = \curl_init( $url );
       	\curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
		\curl_setopt( $curl, CURLOPT_COOKIEFILE, $this->_cookieFile );
		$response = \curl_exec( $curl );
		if ( $response === false ) {
			echo "Failed: " . \curl_error($curl) . "\n";
			return null;
		} 

		\curl_close( $curl );
       	return $response;
	}

	/**
	 * getAuthorization
	 */
	function getAuthToken() {
		$config = $this->settings();

		$url = sprintf("http://%s/AQUARIUS/Publish/V2/GetAuthToken?userName=%s&encryptedPassword=%s",
			$config[ 'aquarius' ], $config[ 'username' ], $config[ 'password' ]);

		$cookiefile = tempnam( "/tmp/", "goes-" );
		$this->_cookieFile = $cookiefile;
		$curl = \curl_init( $url );
       	
       	\curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		\curl_setopt( $curl, CURLOPT_URL, $url );
		\curl_setopt( $curl, CURLOPT_COOKIEJAR, $cookiefile );
		
 		$response = \curl_exec($curl);
		\curl_close($curl);

		// XXX: check for fail or error
		if ( $response !== null ) {
			$authtoken = $response;
			$this->_token = $authtoken;
			return $authtoken;
		}
	}

    /* ------------------ Location ------------------------------- */


    /**
     * @return mixed|null
     */
    public function getLocationDescriptionList()
    {
        $json = $this->get('GetLocationDescriptionList');
        if ($json) {
            return json_decode($json);
        }
        return null;
    }

	/* ------------------ Timeseries ------------------------------- */

    /**
     * @return mixed|null
     */
    public function getTimeSeriesDescriptionList()
    {
        $json = $this->get('GetTimeSeriesDescriptionList');
        if ($json) {
            return json_decode($json);
        }
        return null;
    }

    /**
     * @param $tstr
     * @return null|resource
     */
	public function getTimeseriesData($tstr)
    {
		$config = $this->settings();

        $json = $this->get('GetTimeSeriesRawData?TimeSeriesIdentifier=' . $tstr);
        return $json;
    }

    /* ------------------ Parameters ------------------------------- */

    /**
     * @return mixed|null
     */
    public function getParameters()
    {
        $json = $this->get('GetParameterList');
        if ($json) {
            return json_decode($json);
        }
        return null;
    }

}
