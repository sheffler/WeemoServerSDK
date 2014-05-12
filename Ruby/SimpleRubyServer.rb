#!/usr/bin/env ruby
#
# Simple HTTP service illustrating request a token from Weemo.
#

require 'webrick'
require_relative 'lib/weemo_auth'

# Path to the Weemo CA Cert
WEEMO_CACERT = "/Path/To/Your/Cert/weemo-ca.pem"

# Paths to the extracted key and cert from the client.p12 file
WEEMO_CLIENTCERT = "/Path/To/Your/Cert/publicCert.pem"
WEEMO_CLIENTCERT_KEY = "/Path/To/Your/Cert/privateKey.pem"

# Password
WEEMO_CERTPASSWORD = "abcdefgh"

# Weemo Auth endpoint, Client ID and Secret
WEEMO_AUTH_URL = "https://oauths.weemo.com/auth/"
WEEMO_CLIENT_ID = "7a7a7a7a7a8b8b8b8b8b9c9c9c9c9c"
WEEMO_CLIENT_SECRET = "19ab19ab19ab19ab28cd28cd28cd28"

class WeemoServlet < WEBrick::HTTPServlet::AbstractServlet

  def initialize(server, *options)
    @client = WeemoAuth.new(WEEMO_AUTH_URL, WEEMO_CACERT, WEEMO_CLIENTCERT, WEEMO_CLIENTCERT_KEY, WEEMO_CERTPASSWORD, WEEMO_CLIENT_ID, WEEMO_CLIENT_SECRET)
    super(server, *options)
  end

  def do_GET(request, response)

    # puts "do_GET:#{request}"

    if request.query["uid"]
      uid = request.query["uid"]

      # Set the client and profile identifiers as appropriate for your Weemo agreement
      identifier_client = "yourdomain.com";
      id_profile = "premium";

      obj = @client.auth(uid, identifier_client, id_profile)

      # puts "Obj:#{obj}"

      response.status = 200
      response.content_type = "application/json"
      response.body = JSON.generate(obj)
    else
      response.status = 500
      response.content_type = "application/json"
      response.body = JSON.generate( {"error" => "You did not not provide the correct parameters"} )
    end
  end

end

server = WEBrick::HTTPServer.new(:Port => 8000)
 
server.mount "/gettoken", WeemoServlet
 
trap("INT") {
    server.shutdown
  }

puts "Starting Server on port 8000"
 
server.start

