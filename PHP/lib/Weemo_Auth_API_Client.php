<?php
/**
 * Class Weemo_Client
 */
class Weemo_Client
{
  private $_cacert;

    /** @var  string $_client_id */
    private $_client_id;
    /** @var  string $_client_secret */
    private $_client_secret;
    /** @var  string $_p12 */
    private $_p12;
    /** @var  string $_passphrase */
    private $_passphrase;

    /** @var  string */
    private $_file_pem;
    /** @var  string */
    private $_file_key;
    /** @var  array $_certs */
    private $_certs;
    /** @var  array $_scope */
    private $_scope;
    /** @var  resource $_curl */
    private $_curl;

    /**
     * @author Damien Lasserre <damien.lasserre@weemo.com>
     *
     * @param $client_id
     * @param $client_secret
     * @param $p12
     * @param string $passphrase
     * @param string $scope
     *
     * This function initialize parameters and call initCertificate, initCertificate check format of P12 file
     * and validate information.
     * If information is not valid, new exception is raised with message.
     */
    public function __construct($cacert, $client_id, $client_secret, $p12, $passphrase, $scope)
    {
        /** set vars */
        $this->_cacert          = $cacert;
        $this->_client_id       = $client_id;
        $this->_client_secret   = $client_secret;
        $this->_p12             = $p12;
        $this->_passphrase      = $passphrase;
        $this->_scope           = $scope;
        /** Launch check P12 file */
        $this->initCertificate();
    }

    /**
     * @author Damien Lasserre <damien.lasserre@weemo.com>
     *
     * @throws Exception
     *
     * This function create .pem file extract from P12,
     * if file already exists it doesn't create a new one, you must delete the file to regenerate a .pem
     *
     * @return Weemo_Client
     */
    public function createCertFile()
    {
        /** @var string $name */
        $name = str_replace('p12', 'pem', $this->_p12);

        if(!file_exists($name)) {
            if(!$fd = fopen($name, 'a+'))
                throw new Exception('Impossible to create PEM file : check permissions');
            file_put_contents($name, $this->_certs['cert']);
            fclose($fd);
        }
        $this->_file_pem = $name;

        /** return */
        return ($this);
    }

    /**
     * @author Damien Lasserre <damien.lasserre@weemo.com>
     *
     * @throws Exception
     *
     * This function create .key file extract from P12,
     * if file already exists it doesn't create a new one, you must delete the file to regenerate a .key
     *
     * @return Weemo_Client
     */
    public function createKeyFile()
    {
        /** @var string $name */
        $name = str_replace('p12', 'key', $this->_p12);

        if(!file_exists($name)) {
            if(!$fd = fopen($name, 'a+'))
                throw new Exception('Impossible to create KEY file : check permissions');
            file_put_contents($name, $this->_certs['pkey']);
            fclose($fd);
        }
        $this->_file_key = $name;

        /** return */
        return ($this);
    }

    /**
     * @author Damien Lasserre <damien.lasserre@weemo.com>
     *
     * @throws Exception
     */
    protected function initCertificate()
    {
        if(!file_exists($this->_p12))
            throw new Exception('This P12 not found on this server !');
        if(!openssl_pkcs12_read(file_get_contents($this->_p12), $this->_certs, $this->_passphrase)) {
            throw new Exception("Unable to parse the p12 file.  " .
            "Is this a .p12 file?  Is the password correct?  OpenSSL error: " .
            openssl_error_string());
        }
    }

    /**
     * @author Damien Lasserre <damien.lasserre@weemo.com>
     *
     * @param array $params
     *
     * This function initialize curl library and could takes optionaly specials parameters for curl,
     * in the new version the secret_client and client_id are sent in the HTTP head, don't panic it's optional :)
     *
     * @return Weemo_Client
     */
    public function initWCurl(array $params = null)
    {
        /** @var string _curl */
        $this->_curl = curl_init($this->_scope.'?client_id='.$this->_client_id.'&client_secret='.$this->_client_secret);
        if(null !== $params)
            curl_setopt_array($this->_curl, $params);

        return ($this);
    }

    /**
     * @author Damien Lasserre <damien.lasserre@weemo.com>
     *
     * @param string $uid
     * @param string|null $domain
     * @param string|null $profile
     *
     * This function sent HTTP request with curl and on SSL connection, it returns a token,
     * you should verify validity period of your P12 file, validity is set for one year.
     * Specify uid is the only mandatory value, if domain or profile are null, default domain and profile values are used
     *
     * There is a known issue if curl and NSS are installed on your server.
     *
     * @return mixed
     */
    public function sent($uid, $domain = null, $profile = null)
    {

        /** POSTS PARAMETERS */
        $params['uid']       = $uid;
        $params['identifier_client'] = $domain;
        $params['id_profile']        = $profile;

        /** OTHERS PARAMETERS */
        curl_setopt($this->_curl, CURLOPT_POST, true);
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $params);

        /** SSL PARAMETERS */
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($this->_curl, CURLOPT_VERBOSE, true);
        curl_setopt($this->_curl, CURLOPT_CERTINFO, true);

        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->_curl, CURLOPT_CAPATH, './');
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_curl, CURLOPT_FAILONERROR, 0);
        curl_setopt($this->_curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($this->_curl, CURLOPT_SSLKEYPASSWD, $this->_passphrase);
        curl_setopt($this->_curl, CURLOPT_SSLCERT, $this->_file_pem);
        curl_setopt($this->_curl, CURLOPT_SSLCERTPASSWD, $this->_passphrase);
        curl_setopt($this->_curl, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($this->_curl, CURLOPT_SSLKEY, $this->_file_key);
        curl_setopt($this->_curl, CURLOPT_CAINFO, $this->_cacert);

        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array('Expect:'));

        return curl_exec($this->_curl);
    }
}
?>
