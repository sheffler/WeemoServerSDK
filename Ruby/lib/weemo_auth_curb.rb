#
# Weemo Auth Client for Ruby
#
# Prior to using this module, unpack the "client.p12" into its two components as follows:
# 
# openssl pkcs12 -in client.p12 -nocerts -out privateKey.pem
# openssl pkcs12 -in client.p12 -clcerts -nokeys -out publicCert.pem
#
# Tom Sheffler
# Feb 2014
#

require 'curb'
require 'uri'
require 'json'
require 'logger'

Logger = Logger.new(STDOUT)

class WeemoAuth

  def initialize(auth_url, ca_file, public_cert, private_key, cert_password, client_id, client_secret)
    @auth_url = auth_url
    @ca_file = ca_file
    @public_cert = public_cert
    @private_key = private_key
    @cert_password = cert_password
    @client_id = client_id
    @client_secret = client_secret
  end

  def auth(uid, domain, profile)
    Logger.info "WeemoAuth#auth #{uid}"

    url = "#{@auth_url}?client_id=#{@client_id}&client_secret=#{@client_secret}"

    params = {
      "uid" => uid,
      "identifier_client" => domain,
      "id_profile" => profile
    }

    args = URI.encode_www_form(params)

    Logger.debug "WeemoAuth#auth POST #{url}"
    Logger.debug "WeemoAuth#auth ARGS #{args}"

    req = Curl::Easy.http_post(url, args) do |curl|

      curl.verbose = true
      curl.ssl_version = 3
      curl.follow_location = true

      curl.cacert = @ca_file
      curl.cert = @public_cert
      curl.certpassword = @cert_password
      curl.certtype = "PEM"
      curl.cert_key = @private_key

      Logger.debug "WeemoAuth#auth:curl #{curl.inspect}"
      
    end

    # Upon success, this returns an object like:
    #  {"token"=>"7d7744de4dfa349aaa4d4706c6038fc6890cb2da"}
    # Upon failure, this returns an object like:
    #  {"error"=>552, "error_description"=>"[MULTITENANT] This domain is disabled"}

    # Logger.debug "WeemoAuth#body_str #{req.body_str.inspect}"

    begin
      obj = JSON.parse(req.body_str)
    rescue Exception => e
      Logger.debug "NonJSON Response:::#{req.body_str}:::"
      obj = { "error" => 500, "error_description" => "Unparsable JSON" }
    end

    Logger.debug "WeemoAuth#auth:obj #{obj.inspect}"
    return obj

  end

end
