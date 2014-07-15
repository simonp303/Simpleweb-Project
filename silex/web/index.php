<?php
require_once __DIR__.'/../vendor/autoload.php';
//require_once __DIR__.'/../vendor/gonzalo123/SilexTwitterLogin.php';
require_once __DIR__.'/../vendor/twitteroauth-master/twitteroauth/twitteroauth.php';
 
$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
 
$consumerKey    = "EQYtaiVbYrcvVbK7ke20Mq1H6";
$consumerSecret = "eQAQpPbVENUI2qSiRGb9pij4kiPQ0aTb2la1QtZDiMSp1mdtNS";
 
$twitterLoggin = new SilexTwitterLogin($app, 'twitter');
$twitterLoggin->setConsumerKey($consumerKey);
$twitterLoggin->setConsumerSecret($consumerSecret);
$twitterLoggin->registerOnLoggin(function () use ($app, $twitterLoggin) {
    $app['session']->set($twitterLoggin->getSessionId(), [
        'user_id'            => $twitterLoggin->getUserId(),
        'screen_name'        => $twitterLoggin->getScreenName(),
        'oauth_token'        => $twitterLoggin->getOauthToken(),
        'oauth_token_secret' => $twitterLoggin->getOauthTokenSecret()
    ]);
});
 
$twitterLoggin->mountOn('/login', function () {
    return '<a href="/login/requestToken">login</a>';
});

$app->get('/', function () use ($app){

	echo '<p><a href="/getdata">Get data</a> - <a href="/list-users">Display Data</a></p>';
    return 'Hello ' . $app['session']->get('twitter')['screen_name'];
});
 
$app->get('/getdata', function () use ($app){

	echo '<h2>Adding records...</h2>';

	echo '<p><a href="/">Home</a></p>';
    
    $consumerKey    = "EQYtaiVbYrcvVbK7ke20Mq1H6";
	$consumerSecret = "eQAQpPbVENUI2qSiRGb9pij4kiPQ0aTb2la1QtZDiMSp1mdtNS";

	// Get the token and secret from the session
    $oAuthToken = $app['session']->get('twitter')['oauth_token'];
    $oAuthSecret = $app['session']->get('twitter')['oauth_token_secret'];

    // Create a twitter instance
    $tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);

    // Get the follwers for the logged in account
    $ids = $tweet->get('followers/ids', array('screen_name' => $app['session']->get('twitter')['screen_name']));

    // Split them intoi groups of 100 as twitter can only process the users/lookup in blocks of 100
    $idChunks = array_chunk($ids->ids, 100);

    $m = new MongoClient();
   // select a database
   	$db = $m->twitterUsers;
   	$collection = $db->information;


    // Work through each chunk
    foreach($idChunks as $idChunk) {
    	// Convert the array into a comma seperated list
		$comma_separated = implode(",", $idChunk);
		// Now get the user details for this chunk
		$user_details = $tweet->get('users/lookup', array('user_id' => $comma_separated));
		// Save each of the user details into our array
		foreach($user_details as $user_detail) {
			
			$document = 
				array(
			  "userid" => $user_detail->id, 
			  "username" => $user_detail->name
			);

			// check if record exists

			$query = array('username' => $user_detail->name);
			$cursor = $collection->findOne($query);

			// If no record exists
			if(empty($cursor)) {
				// Add it
				$collection->insert($document);
				echo $user_detail->name.' record added<br />';
			} else {
				echo 'record already exists<br />';
			}

		}

    }

    return 'records added';

});


// Display the users from the db
$app->get('/list-users', function() use($app) {

	// connect to mongodb
	$m = new MongoClient();
	// select a database
	$db = $m->twitterUsers;
	// and collection
	$collection = $db->information;

  	// start our data output
	$data = '<h2>Followers of '.$app['session']->get('twitter')['screen_name'].'</h2>';
	$data .= '<p><a href="/">Home</a></p>';

	// Get the data
	$cursor = $collection->find();
	// iterate cursor to display title of documents
	foreach ($cursor as $document) {
	  	$data .= $document["username"] . " [".$document["userid"]."]<br />";
	}
	return '<p>'.$data.'</p>';

});


 
$app->run();