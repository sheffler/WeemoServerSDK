/*
 * Simple HTTP service illustrating requesting a token from Weemo.
 *
 * Dependencies:
 *   Java 1.6
 *
 * How to run:
 *   javac -cp WeemoServerAuth-1.0-java6.jar SimpleJavaService.java util/ParameterFilter.java
 *   java -cp WeemoServerAuth-1.0-java6.jar:util:. SimpleJavaService
 *
 * On Windows, replace the ':' in the classpath with ';'.
 */

// Import Weemo Auth Classes
import com.weemo.auth.WeemoException;
import com.weemo.auth.WeemoServerAuth;

import java.io.IOException;
import java.io.OutputStream;
import java.net.InetSocketAddress;

import java.util.Map;

import com.sun.net.httpserver.HttpExchange;
import com.sun.net.httpserver.HttpHandler;
import com.sun.net.httpserver.HttpServer;
import com.sun.net.httpserver.Headers;
import com.sun.net.httpserver.HttpContext;

/*
 * The example SimpleService illustrates how to proxy a request for a
 * token to Weemo.
 *
 * A single endpoint is exposed: gettoken.  The HTTP handler parses
 * the request for arguments, looking for the parameter 'uid'.  It
 * passes this along to the Weemo authentication portal.
 *
 * Example:
 *  curl -X POST http://localhost:8000/gettoken?uid=yourname
 *
 */
public class SimpleJavaService {

    public static void main(String[] args) throws Exception {

        // Enter below your path to certificate files, to Weemo Auth Server and your credentials
        final String CA_FILE        = "/Path/To/Your/Cert/weemo-ca.pem";
        final String P12_FILE       = "/Path/To/Your/Cert/client.p12";
        final String AUTH_URL       = "https://oauths.weemo.com/auth/";
        final String P12_PASS       = "abcdefgh";
        final String CLIENT_ID      = "7a7a7a7a7a8b8b8b8b8b9c9c9c9c9c";
        final String CLIENT_SECRET  = "19ab19ab19ab19ab28cd28cd28cd28";

        // Initialize WeemoServerAuth Object
        WeemoServerAuth auth = null;

        try {
             auth = new WeemoServerAuth(AUTH_URL, CA_FILE, P12_FILE, P12_PASS, CLIENT_ID, CLIENT_SECRET);
        }
        catch (WeemoException e) {
            e.printStackTrace();
            return ;
        }

        // Start a simple Web Server
        int port = 8000;
        HttpServer server = HttpServer.create(new InetSocketAddress(port), 0);
        HttpContext context = server.createContext("/gettoken", new MyHandler(auth));
        context.getFilters().add(new ParameterFilter());
        server.setExecutor(null); // creates a default executor
        server.start();
        System.out.println("Starting server on port " + port);

    }

    static class MyHandler implements HttpHandler {

        WeemoServerAuth auth = null;

        public MyHandler(WeemoServerAuth wauth) {
            auth = wauth;
        }
            
        public void handle(HttpExchange t) throws IOException {

            // The JSON response we will send back
            String response;

            // Extract Query and Post paramters and place in a single dict called 'params'
            Map params = (Map)t.getAttribute("parameters");
            System.out.println("Parameters:" + params);

            // Determine UID of caller.  Return an error if not a valid user.
            String uid = (String) params.get("uid");

            String domain = "yourdomain.com";         // group of users
            String profile = "premium";               // premium profile

            if (uid == null) {
                System.out.println("No UID found in request");
                response = "{ \"error\" : \"unspecified\" }";
            }
            else {

                try {
                    String authToken = auth.getAuthToken(uid, domain, profile);
                    System.out.println("AuthToken:" + authToken);
                    response = authToken;
                }
                catch (WeemoException e) {
                    e.printStackTrace();
                    response = "{ \"error\" : \"unspecified\" }";
                }

                System.out.println("Response:" + response);
            }

            // add the required response header for JSON
            Headers h = t.getResponseHeaders();
            h.add("Content-Type", "application/json");
            h.add("Access-Control-Allow-Origin", "*");

            // write the response body
            t.sendResponseHeaders(200, response.length());
            OutputStream os = t.getResponseBody();
            os.write(response.getBytes());
            os.close();
        }
    }
}
