# Using Weemo with Rails

The Weemo Ruby client is suitable for use in a Rails project.  This note describes some best practices that can help get you up and running quickly.  These notes were prepared against a project using Rails4 with Devise for authentication.

Requirements:

- Openssl
- Libcurl
- Curb gem

You may obtain your Authentication Provider credentials from Weemo.


## Install the library

Copy ```lib/weemo_auth.rb``` into the top level ```lib/``` directory in your project.  Rails includes files in this directory by default, so you can simply 'require' the module later.

## Define your Weemo Credentials

In the top-level ```environment.rb``` define the global WEEMO variables the same as ```SimpleRubyServer.rb```.

## Extend the User Object

Each user in the Weemo cloud is authenticated using three pieces of information: their UID, Domain and Profile.  Extend the User model with three methods to help localize the logic mapping users to Weemo capabilities.

```ruby
class User < ActiveRecord::Base

  devise ... # (lines omitted)

  def weemo_uid
    email
  end

  def weemo_domain
    "yourdomain.com"
  end

  def weemo_profile
    "premium"
  end

end
```

The UID is the unique ID of the user in your system.  The domain will match your domain, or if you are a multi-tenanted service provider, the domain may specify the tenancy the user belongs to.  The profile identifies the service class of the user.  Customize these methods as appropriate for your installation.

## Implement a Weemo Authentication Controller

The WeemoContoller implements a single method called ```callback``` that obtains the token from Weemo using the Authentication Client.  The controller below verifies that the request comes from a logged-in user.  If so then the UID, DOMAIN and PROFILE of the current user are used to obtain a token.  Otherwise, an error is returned.

```ruby
require 'weemo_auth'

class WeemoController < ApplicationController

  def initialize
    @client = WeemoAuth.new(WEEMO_AUTH_URL, WEEMO_CACERT, WEEMO_CLIENTCERT, WEEMO_CLIENTCERT_KEY, WEEMO_CERTPASSWORD, WEEMO_CLIENT_ID, WEEMO_CLIENT_SECRET)
    super
  end

  def callback
    if user_signed_in?
      obj = @client.auth(current_user.weemo_uid,
                         current_user.weemo_domain,
                         current_user.weemo_profile)
    else
      obj = { "error" => 500, "error_description" => "unauthenticated user" }
    end

    logger.debug "Weemo#callback #{obj}"

    render :json => obj
  end
end
```

## Configure the route

Add the route for this controller near the top of ```config/routes.rb``` in your project.

```ruby
YourApp::Application.routes.draw do
  get "weemo/callback"
  ...
end
```


## Initialize the Weemo Javascript Object with a Token

Initialize the Weemo object with the token obtained through your controller.  A sketch appears below.

```javascript
  var weemoAppId = "abcdefghij";
  var weemo = null;

  $.ajax({
    type: "GET",
    url: "/weemo/callback",
    cache: false,
    dataType: "JSON"
    }).success(function(data) {
      var token = data.token;
      weemo = new Weemo(weemoAppId, token, 'internal', "", "1", "<%= current_user.email %>");
      weemo.initialize();
    }).error(function(e) {
      console.log(["WeemoToken error", e]);
    });

```
