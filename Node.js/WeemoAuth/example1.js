var https = require('https');
var fs = require('fs');

var options = {
    hostname: 'oauths.weemo.com',
    port: 443,
    path: '/auth/?client_id=235a29bcabfbb1d3850c42c2d3afa4&client_secret=4ae26edf8eca1ced9535cb2d87d485',
    method: 'POST', // or 'POST'

    key:    fs.readFileSync('/Users/Sheffler/weemo/ruby/privateKey.pem'),
    cert:   fs.readFileSync('/Users/Sheffler/weemo/ruby/publicCert.pem'),
    ca:     fs.readFileSync('/Users/Sheffler/weemo/ruby/weemo-ca.pem'),
    requestCert:        true,
    rejectUnauthorized: false,
    passphrase: "XnyexbUF",
    secureProtocol: 'SSLv3_method',

    // follow_location
    // certtype = "PEM"

  headers: {
    'Content-Type': "application/x-www-form-urlencoded"
  }

};

var body = "uid=sheffler&identifier_client=tsheffler4.wauth&id_profile=7";
options.headers["Content-Length"] = body.length;

var req = https.request(options, function(res) {
  res.on('data', function(d) {
    console.info('POST result:\n');
    process.stdout.write(d);
    console.info('\n\nPOST completed');
  });
  res.on('end', function() {
    console.info('response ended');
  });
});

req.write(body);
req.end();
req.on('error', function(e) {
  console.error(e);
});

