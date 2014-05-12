//
// Weemo Client
//
// Tom Sheffler
// Februrary 2014

var https = require('https');
var fs = require('fs');
var url = require('url');
var util = require('util');

var WeemoClient = function(auth_url, ca_file, public_cert, private_key, cert_password, client_id, client_secret)
{

  var auth = function(uid, domain, profile, success, failure) {
    var uri = url.parse(auth_url);
    console.log(["uri.host", uri.host]);
    console.log(["uri.port", uri.port]);
  
    var options = {
      hostname: uri.host,
      port: uri.port,
      path: util.format('/auth?client_id=%s&client_secret=%s', client_id, client_secret),
      method: 'POST',

      key:    fs.readFileSync(private_key),
      cert:   fs.readFileSync(public_cert),
      ca:     fs.readFileSync(ca_file),
      requestCert:        true,
      rejectUnauthorized: false,
      passphrase: cert_password,
      secureProtocol: 'SSLv3_method',

      // follow_location
      // certtype = "PEM"

      headers: {
        'Content-Type': "application/x-www-form-urlencoded"
      }
    };

    var body = util.format("uid=%s&identifier_client=%s&id_profile=%s", uid, domain, profile);
    options.headers["Content-Length"] = body.length;

    // console.log(["options", options]);

    var req = https.request(options, function(res) {
      var body = '';
      res.on('data', function(d) {
        console.info('POST result:\n');
        process.stdout.write(d);
        body += d;
      });

      res.on('end', function() {
        console.info('response ended');
        success(body);
      });
    });

    req.write(body);
    req.end();
    req.on('error', function(e) {
      console.error(e);
      failure(e);
    });
  }

  this.auth = auth;
};


module.exports = WeemoClient;
