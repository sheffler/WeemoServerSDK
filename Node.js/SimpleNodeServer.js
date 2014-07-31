#!/usr/bin/env node

//
// Simple HTTP service illustrating requesting a token from Weemo.
//
// 

var http = require('http');
var url = require('url');
var querystring = require('querystring');
var weemo_auth = require('./weemo_auth');

// Path to the Weemo CA Cert
var WEEMO_CACERT = "/Path/To/Your/Cert//weemo-ca.pem";

// Paths to the extracted key and cert from the client.p12 file
var WEEMO_CLIENTCERT = "/Path/To/Your/Cert/publicCert.pem";
var WEEMO_CLIENTCERT_KEY = "/Path/To/Your/Cert/privateKey.pem";

// Password
var WEEMO_CERTPASSWORD = "abcdefgh";

// Weemo Auth endpoint, Client ID and Secret
var WEEMO_AUTH_URL = "https://oauths.weemo.com/";
var WEEMO_CLIENT_ID = "7a7a7a7a7a8b8b8b8b8b9c9c9c9c9c";
var WEEMO_CLIENT_SECRET = "19ab19ab19ab19ab28cd28cd28cd28";


var client = new weemo_auth(WEEMO_AUTH_URL, WEEMO_CACERT, WEEMO_CLIENTCERT, WEEMO_CLIENTCERT_KEY, WEEMO_CERTPASSWORD, WEEMO_CLIENT_ID, WEEMO_CLIENT_SECRET);

var server = http.createServer(function (request, response) {

  var uri = url.parse(request.url);
  var path = uri.pathname;
  var query = uri.query;
  var qparams = querystring.parse(query);

  if (path == "/gettoken") {
    if (qparams["uid"]) {

      var uid = qparams["uid"];

      // Set the client and profile identifiers as appropriate for you Weemo agreement
      var identifier_client = "yourdomain.com";
      var id_profile = "premium";

      console.log(["uid", uid, identifier_client, id_profile]);

      // Ask the client for a token
      client.auth(
        uid, identifier_client, id_profile,
        function(result) {
          response.writeHead(200, {'Content-Type' : 'application/json', 'Access-Control-Allow-Origin' : '*'});
          response.write(result);
          response.end();
        },
        function(e) {
          response.writeHead(500, {'Content-Type' : 'text/plain'});
          response.write(e);
          response.end();
        });
    }
    else {
      response.writeHead(500, {'Content-Type' : 'application/json'});
      response.write(JSON.stringify({ "error" : "You did not provide the correct parameters" }));
      response.end();
    }
  }
  else {
    response.writeHead(404, {'Content-Type' : 'text'});
    response.end("Not Found");
  }

  });

console.log("Starting server on port 8000");
server.listen(8000, '0.0.0.0');



