# Ruby Client for Weemo

The Ruby client for Weemo can be used on your server to communicate with Weemo back-end services.  Its primary purpose is to enable your server as an Authentication Provider for Weemo video calls.

The Ruby client is distributed with a simple demonstration server you can use for testing and development purposes.

Requirements:

- Ruby 1.9 or greater
- Openssl - command line tools and developer tools (to build Curb)
- Libcurl - developer tools (to build Curb)
- Curb gem

You may obtain your Authentication Provider credentials from Weemo.

**Rails Support:** integration is straightfoward and is described here: [Rails4](doc/RAILS.md).

## Installing the Client

Place the ```weemo_auth.rb``` file in your project and include it in your code.

    require_relative 'lib/weemo_auth'

In a Rails project, we recommend creating a ```lib``` directory and placing it
there.


## Preparing the Certificates

You will have received two files from Weemo: ```weemo-ca.pem``` and ```client.p12```.  Place these in a directory that is readable by Ruby on your server.  If you are using a code repository for your project, it is a good idea to place these files in a location where they are not checked in.

Your ```client.p12``` file is used to verify the identity of your server.  It contains two components: a private key and  public cert.  Before using the Ruby client, you will need to extract these two components.  Use the commands below.

    openssl pkcs12 -in client.p12 -nocerts -out privateKey.pem
    openssl pkcs12 -in client.p12 -clcerts -nokeys -out publicCert.pem

You will be prompted for a password three times for the first command and
once for the second.  Use the "Passphrase" given to you from Weemo in all cases



## Using the Example Server

The Ruby client library is distributed with an example server called ```SimpleRubyServer.rb```.  This little server accepts requests for a given UID and returns a Weemo token as a response.  Using this servlet as a guide, it is not too difficult to incorporate the Weemo client into your own Ruby project.

To use the example server, configure ```SimpleRubyServer.rb``` with the following pieces of information.

| parameter    | description |
|--------------|-------------|
| WEEMO_CACERT         | Path to the "weemo-ca.pem" file given to you by Weemo. |
| WEEMO_CLIENTCERT      | Path to the "publicCert.pem" file you extracted earlier. |
| WEEMO_CLIENTCERT_KEY  | Path to the "privateKey.pem" file you extracted earlier. |
| WEEMO_CERTPASSWORD   | The p12 "passphrase" given to you. |
| WEEMO_CLIENT_ID      | The 30 character "Client ID" given to you by Weemo. |
| WEEMO_CLIENT_SECRET  | The 30 character secret accompanying the ID above. |
| WEEMO_AUTH_URL       | "https://oauths.weemo.com" |

You will also need to set these two variables in the body of the ```do_GET``` method.

| parameter    | description |
|--------------|-------------|
| identifier_client | A domain you registered with Weemo. Each domain identifies a group of users. |
| id_profile        | A profile you registered with Weemo.  This describes a user's capabilities. |


Run the example.  It will start a WEBrick server listening on port 8000.

    ruby SimpleRubyServer.rb


## Testing the Example Server

You can use curl to obtain a token for a user id this way.

    curl "http://localhost:8000/gettoken.php?uid=test99"

If everything is ok, you will find a JSON object as the response.

    {"token":"lnrsodluk3vmr1087nn1j83i51"}




