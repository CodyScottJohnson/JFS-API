<?php
require '/srv/jfsapp/vendor/autoload.php';
require '/srv/jfsapp/vendor/myfiles/config.php';
// error reporting (this is a demo, after all!)
//ini_set('display_errors',1);error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);
//Load Oauth2 Server
OAuth2\Autoloader::register();
// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn.'JFS_oauth2', 'username' => $username, 'password' => $password));
$server = new OAuth2\Server($storage);

// Add Grant Types
$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
$server->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
$server->addGrantType(new OAuth2\GrantType\RefreshToken($storage,array('always_issue_new_refresh_token' => true)));

//Initialize App
$app = new \Slim\App;
//Set Cores Options to allow remote access
$corsOptions = array(
    "origin" => "*",
    "exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
    "allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$cors = new \CorsSlim\CorsSlim($corsOptions);
$app->add($cors);

$app->post('/getToken/', function($request, $response) use ($app,$server)
{
	$checkToken= $server->handleTokenRequest(OAuth2\Request::createFromGlobals());//->send();
	$checkToken->send();
	$newResponse = $response->withStatus($checkToken->getStatusCode());
    return $newResponse;
});

$app->run();
?>
