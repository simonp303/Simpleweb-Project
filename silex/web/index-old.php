<?php
require_once __DIR__.'/../vendor/autoload.php';
//require_once __DIR__.'/../vendor/gonzalo123/SilexTwitterLogin.php';

require_once __DIR__.'/../vendor/twitteroauth-master/twitteroauth/twitteroauth.php';
 
$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
 
 

 
$app->get('/', function () use ($app){

	session_start();

    echo '<p>Hello</p>';

	$consumerKey    = "EQYtaiVbYrcvVbK7ke20Mq1H6";
	$consumerSecret = "eQAQpPbVENUI2qSiRGb9pij4kiPQ0aTb2la1QtZDiMSp1mdtNS";



	if (!isset($_GET["oauth_token"])) {
	    // set these values in a config file somewhere.
	    $twitter = new TwitterOAuth($consumerKey, $consumerSecret);

	    $content = $twitter->get('account/verify_credentials');

	    // append a ?. This is your callback URL if you specify something.
	    $credentials = $twitter->getRequestToken("http://silex.simpleweb/?");

	    // try and be a bit more elegant with the URL... This is a minimal example
	    $url = $twitter->getAuthorizeUrl($credentials);
	    echo $url;


		$access_token = $twitter->getAccessToken('oauth_verifier');

		$content = $connection->get('account/verify_credentials');

	    // these are temporary tokens that must be used to fetch the new,
	    // permanent access tokens. store these in some way,
	    // session is a decent choice.
	    $_SESSION["token"] = $credentials["oauth_token"];
	    $_SESSION["secret"] = $credentials["oauth_token_secret"];
	} else {

	    // use the user's previously stored temporary credentials here
	    $twitter = new TwitterOAuth($consumerKey, $consumerSecret,
	                    $_SESSION["token"], $_SESSION["secret"]);

	    // uses the oauth_token (from the request) already.
	    // you store these credentials in your database (see below).
	    $credentials = $twitter->getAccessToken($_GET["oauth_verifier"]);

	    // just a printout of credentials. store these, don't display them.
	    echo "<pre>";
	    var_dump($credentials);
	    // valid credentials, provided you give the app access to them.
	    echo "</pre>";
	}

	$home_timeline = $twitter->get('statuses/home_timeline');
	print_r($home_timeline);



	/*

	// The TwitterOAuth instance
	$twitteroauth = new TwitterOAuth($consumerKey, $consumerSecret);

	// Requesting authentication tokens, the parameter is the URL we will be redirected to
	$request_token = $twitteroauth->getRequestToken('http://silex.simpleweb/');


    // If everything goes well..
	if($twitteroauth->http_code==200){
	    // Let's generate the URL and redirect
	    //$url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']);
	    //header('Location: '. $url);

		echo '<p>worked</p>';
		//$request_token = $twitteroauth->getRequestToken('http://silex.simpleweb/');

		var_dump($request_token["oauth_token"]);

		$credentials = $twitteroauth->getAccessToken($request_token["oauth_token"]);


		$home_timeline = $credentials->get('statuses/home_timeline');
		print_r($home_timeline);

		$user_info = $twitteroauth->get('account/verify_credentials');
		// Print user's info
		print_r($user_info);

	} else {
	    // It's a bad idea to kill the script, but we've got to know when there's an error.
	    die('Something wrong happened.');
	}

	*/

	return '';

});

$app->get('/loggedin', function () use ($app){
	$consumerKey    = "EQYtaiVbYrcvVbK7ke20Mq1H6";
	$consumerSecret = "eQAQpPbVENUI2qSiRGb9pij4kiPQ0aTb2la1QtZDiMSp1mdtNS";


	// The TwitterOAuth instance
	$twitteroauth = new TwitterOAuth($consumerKey, $consumerSecret);

	$home_timeline = $twitteroauth->get('statuses/home_timeline');
	print_r($home_timeline);

	return 'logged in';
});
 
$app->run();