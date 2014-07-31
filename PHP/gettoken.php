<?php

// Usage:
//  php -S localhost:8000
//
// Tested with PHP 5.5

// DEBUGGING FLAGS

// ini_set('display_error', 'On');
// error_reporting(E_ALL);

// Path to the Weemo CA Cert
$WEEMO_CACERT = "/Path/To/Your/Cert/weemo-ca.pem";

// Paths to the extracted key and cert from the client.p12 file
$WEEMO_CLIENTP12 = "/Path/To/Your/Cert/client.p12";

// Password
$WEEMO_CERTPASSWORD = "abcdefgh";

// Weemo Auth endpoint, Client ID and Secret
$WEEMO_AUTH_URL = "https://oauths.weemo.com/auth/";
$WEEMO_CLIENT_ID = "7a7a7a7a7a8b8b8b8b8b9c9c9c9c9c";
$WEEMO_CLIENT_SECRET = "19ab19ab19ab19ab28cd28cd28cd28";

// Include Weemo Auth Client File
require_once("./lib/Weemo_Auth_API_Client.php");


// Get the uid from the query parameters
$uid = $_GET['uid'];

// Set the client and profile identifiers as appropriate for your Weemo agreement
$identifier_client = "yourdomain.com";
$id_profile = "premium";

error_log("Got UID: " . $uid);

// Create a Weemo_Client object instance with correct parameters
//
// client_id = Auth API_KEY provided by Weemo
// client_secret = Auth Secret provided by Weemo
// p12_file = path to the client.p12 file downloaded on wdportal
// p12_passphrase = passphrase of the client.p12 file, can be found on wdportal
// auth_url = URL of Weemo Auth server API
//

try {
    $a = new Weemo_Client($WEEMO_CACERT, $WEEMO_CLIENT_ID, $WEEMO_CLIENT_SECRET, $WEEMO_CLIENTP12, $WEEMO_CERTPASSWORD, $WEEMO_AUTH_URL);

    // Created KEY file from P12
    $a->createKeyFile();

    // Create PEM file from P12
    $a->createCertFile();

    // Init WeemoCurl
    $a->initWCurl();

    // Get token access
    $access_token = $a->sent($uid, $identifier_client, $id_profile);

    header('Access-Control-Allow-Origin: *');
    echo $access_token;
}
catch(Exception $e) {
    error_log($e->getMessage());
    echo "Server Problem";
}
?>