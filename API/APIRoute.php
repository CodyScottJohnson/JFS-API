<?php

require '../vendor/autoload.php';
require '/srv/jfsapp/vendor/myfiles/config.php';
use GraphQL\GraphQL;
use mikehaertl\wkhtmlto\Pdf;

//error reporting (this is a demo, after all!)
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
//Load Rescource Server
$pdo = new PDO($dsn.'JFS_v1', $username, $password);
$db = new NotORM($pdo);
$pdo2 = new PDO($dsn.'JFS_oauth2', $username, $password);
$db2 = new NotORM($pdo2);
//InitializeApp
$app = new \Slim\App();
//Set Cores To Allow Cross Domain Requests
$corsOptions = array(
    'origin' => '*',
    'exposeHeaders' => array('Content-Type', 'X-Requested-With', 'X-authentication', 'X-client'),
    'allowMethods' => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'),
);
$cors = new \CorsSlim\CorsSlim($corsOptions);
$app->add($cors);
$mongo = new Mongo();
$mongodb = $mongo->selectDB('JFS');
//Validate Token Before Request
if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    $server->getResponse()->send();
    die;
}

//API Routes
$app->post('/graphql/', function ($request, $response) use ($app, $db, $server, $pdo) {
		$data = $request->getParsedBody();
		$requestString = isset($data['query']) ? $data['query'] : null;
		$requestString = trim(preg_replace('/\s+/', ' ', $requestString));
		$operationName = isset($data['operation']) ? $data['operation'] : null;
		$variableValues = isset($data['variables']) ? $data['variables'] : null;
	 	require 'Routes/graphql/root.php';
		try {
    // Define your schema:
    $result = GraphQL::execute(
        $schema,
        $requestString,
        $variableValues,
        $operationName
    );
		} catch (Exception $exception) {
    	$result = [
        'errors' => [
            ['message' => "That Didn't Work"]
        ]
    ];
		}
     $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($result);
    return $response;
});
$app->get('/User/', function ($request, $response) use ($app, $db, $server, $pdo) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    //$response->withJSON($token);
            $user = $db->uc_users->where('user_name', $token['user_id']);
	 	$sth = $pdo->prepare("select a.*,b.PermissionLevel
													from uc_users a
																inner join (select user_id,min(permission_id) as PermissionLevel  
																						from uc_user_permission_matches 
																						group by user_id) b on b.user_id = a.id
													where a.user_name=:userid");
    
    $sth->bindValue(':userid', $token['user_id']);
    $sth->execute();
    $user = $sth->fetchAll(PDO::FETCH_ASSOC);
   //$Info = json_decode($recruit->fetch()['Info']);
	 	$Info = $user[0];//->fetch();
		$Info['DropboxToken'] = 'Q97s2PcThkMAAAAAAAB12r6Z6FAIKdLFxUy8uTSFqAv2VnRRG6QxtK80OukeGzBh';
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Info);
    //$newResponse = $response->withStatus($token->getStatusCode());
    return $response;
});
$app->get('/User/Info', function ($request, $response) use ($app, $db, $server) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    //$response->withJSON($token);
            $user = $db->uc_users->where('user_name', $token['user_id']);
    $userid = $user->fetch()['id'];
    $info = $db->UserInfo->where('UserID', $userid);
   //$Info = json_decode($recruit->fetch()['Info']);
     $response->withHeader('Content-Type', 'application/json');
    $response->withJSON(json_decode($info->fetch()['Info']));
    //$newResponse = $response->withStatus($token->getStatusCode());
    return $response;
});
$app->get('/User/Settings/Global', function ($request, $response) use ($app, $db, $server) {
    //$response->withJSON($token);
 		$settings = $db->Settings->where('Setting_Name', 'Global');
    $settings_return = json_decode($settings->fetch()['Settings']);
		$response->withHeader('Content-Type', 'application/json');
    $response->withJSON($settings_return);

    //$newResponse = $response->withStatus($token->getStatusCode());
    return $response;
});
$app->post('/User/Settings/Global', function ($request, $response, $args) use ($app, $db, $server, $pdo) {

   	$settings = $db->Settings->where('Setting_Name', 'Global');
    $post = $request->getParsedBody();
    $post['Settings'] = json_encode($post['Settings']);
    if ($settings->fetch()) {
        $result = $settings->update($post);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        $result = $db->User_Notes->insert($post);
    }

        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->patch('/User/', function ($request, $response) use ($app, $db, $server) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    //$response->withJSON($token);
		$post = $request->getParsedBody();
    $user = $db->uc_users->where('user_name', $token['user_id']);
     if ($user->fetch()) {
			 echo 'there';
         
			  $update = array_intersect_key($post, array_flip(['display_photo']));
			 
         $result = $user->update($update);
			 			echo 'there';
         echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
     } else {
         echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));
     }
});
$app->get('/User/Assigned/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $getDetail = false;
    if (isset($_GET['detail'])) {
        $getDetail = $_GET['detail'];
    }
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
    if ($getDetail == 'true') {
        $sth = $pdo->prepare("select a.*, r.*,u.display_name as 'FlaggedBy_DisplayName', concat(r.FNAME,' ',r.LNAME) as RecruitName, r.ProfilePic
													from Tasks a
															left join uc_users u on u.id=a.AssignedBy_ID
															left join ReqruitingSummary r on r.INDV_ID = a.Recruit_ID
													where a.User_ID=:userid");
    } else {
        $sth = $pdo->prepare("select a.*, concat(r.FNAME,' ',r.LNAME) as RecruitName,u.display_name as 'FlaggedBy_DisplayName', r.ProfilePic
													from Tasks a
															left join uc_users u on u.id=a.AssignedBy_ID
															left join ReqruitingSummary r on r.INDV_ID = a.Recruit_ID
													where a.User_ID=:userid");
    }
    $sth->bindValue(':userid', $user->fetch()['id']);
    $sth->execute();
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($results);
        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->get('/User/Notes/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
    $notes = $db->User_Notes->where('User_ID', $user->fetch()['id']);
    $notes = json_decode($notes->fetch()['Notes']);
    $response->withJSON($notes);
        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->post('/User/Notes/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
    $notes = $db->User_Notes->where('User_ID', $user->fetch()['id']);
    $post = $request->getParsedBody();
    $post['notes'] = json_encode($post['notes']);
    if ($notes->fetch()) {
        $result = $notes->update($post);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        $result = $db->User_Notes->insert($post);
    }

        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->get('/User/Blog/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
    $notes = $db->Blog_Posts->where('User_ID', $user->fetch()['id']);
    $notes = json_decode($notes->fetch()['Post']);
    $response->withJSON($notes);
        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->get('/User/Blog/{id}/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $notes = $db->User_Notes->where('User_ID',  $args['id']);
    $notes = json_decode($notes->fetch()['Notes']);
    $response->withJSON($notes);
        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->post('/User/Blog/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
    $notes = $db->Blog_Posts->where('User_ID', $user->fetch()['id']);
    $post = $request->getParsedBody();
    $post['Post'] = json_encode($post['Post']);
    if ($notes->fetch()) {
        $result = $notes->update($post);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        $result = $db->User_Notes->insert($post);
    }

        //$post = $request->getParsedBody();
        //var_dump($post);
});
$app->get('/Users/', function ($request, $response) use ($app, $db, $server) {
    //$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    //$response->withJSON($token);
            $users = $db->uc_users->select('id,display_name,display_photo,email,user_name,active,title');
   //$Info = json_decode($recruit->fetch()['Info']);
     $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($users);
    //$newResponse = $response->withStatus($token->getStatusCode());
    return $response;
});
$app->post('/assignColorQuiz/', function ($request, $response, $args) use ($app, $db) {
    $response->withHeader('Content-Type', 'application/json');
    $post = $request->getParsedBody();
    $result = $db->Color_Test()->insert($post);
});
$app->get('/ColorQuiz/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $sth = $pdo->prepare("select a.*, concat(r.FNAME,' ',r.LNAME) as RecruitName,r.FNAME,r.LNAME,r.EMAIL,r.BUS_PH_NBR,r.ProfilePic
															from Color_Test a
															inner join ReqruitingSummary r on r.INDV_ID = a.Recruit_ID
													");
    $sth->execute();
    if ($sth->rowCount() > 0) {
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        $results[0]['answers'] = json_decode($results[0]['answers']);
        $response->withJSON($results);

        return $response;
    } else {
        // $newResponse = $response->withStatus(401);
            echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));

            //return $newResponse;
    }
});
$app->patch('/ColorQuiz/', function ($request, $response, $args) use ($app, $db, $pdo) {
		$post = $request->getParsedBody();
		$quiz = $db->Color_Test()->where('ColorTest_ID', $post['ColorTest_ID']);
		$update = array_intersect_key($post, array_flip(['Status']));
    $response->withHeader('Content-Type', 'application/json');
    if ($quiz->fetch()) {
         $result = $quiz->update($update);
     }
		$response->withJSON($quiz);
});
$app->delete('/ColorQuiz/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $test = $db->Color_Test->where('Test_Token', $args['id']);
    if ($test->fetch()) {
        $test->delete();
    } else {
        echo json_encode(array(
          'status' => false,
          'message' => "Couldn't Find ID",
     ));
    }
});
$app->get('/ColorQuiz/Print/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
    //use mikehaertl\wkhtmlto\Pdf;

    $pdf = new Pdf(array(
    'margin-top' => 0,
    'margin-right' => 0,
    'margin-bottom' => 0,
    'margin-left' => 0,
    'page-size' => 'letter',
  'no-stop-slow-scripts',
  'window-status' => 'print',
  ));
    $pdf->addPage('http://127.0.0.1:3456/#/ColorTestINDV?id='.$args['id']);
  //$pdf->send('test.pdf');
    //return $response;
    //$pdf->send('test.pdf');
    $newResponse = $response->withHeader('Content-Type', 'application/pdf');
    $content = $pdf->toString();
    $newResponse->write($content);

    return $newResponse;
});
$app->get('/PrintReport/{url}/', function ($request, $response, $args) use ($app, $db, $pdo) {
    //use mikehaertl\wkhtmlto\Pdf;

    $pdf = new Pdf(array(
    'margin-top' => 0,
    'margin-right' => 0,
    'margin-bottom' => 13,
    'margin-left' => 0,
        'footer-html' => '<!doctype html><html style="padding:0;margin:0"><head></head><body style="padding:0;margin:0"><div style="margin-top:.3mm;height:.5in;width:8.5in;background-color:#edf5fb;border-top: 3px solid #226493;"></div></body></html>',
    'page-size' => 'letter',
    'no-stop-slow-scripts',
            'javascript-delay' => 2000,
    //'window-status' => 'print',
  ));
    $pdf->addPage('http://127.0.0.1:3456/#/'.$args['url']);
  //$pdf->send('test.pdf');
    //return $response;
    //$pdf->send('test.pdf');
    $newResponse = $response->withHeader('Content-Type', 'application/pdf');
    $content = $pdf->toString();
    $newResponse->write($content);

    return $newResponse;
});
$app->get('/Texts/', function ($request, $response) use ($app, $db, $server, $pdo) {
    $sth = $pdo->prepare("select a.*, concat(r.FNAME,' ',r.LNAME) as RecruitName,r.ProfilePic
													from Incoming_Texts a
															left join ReqruitingSummary r on r.INDV_ID = a.from_id
													order by a.date_recieved");
    $sth->execute();
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($results);

    return $response;
});
$app->post('/Text/', function ($request, $response) use ($app, $db, $server, $pdo) {
    $post = $request->getParsedBody();
    $url = 'https://rest.nexmo.com/sms/json?'.http_build_query(
    [
      'api_key' => 'a8eab6a5',
      'api_secret' => '189481e377bb2058',
      'to' => $post['to'],
      'from' => '12034869296',
      'text' => $post['text'],
    ]
  );
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $text_response = curl_exec($ch);
    $decoded_response = json_decode($text_response, true);
    //$message = $decoded_response['messages'];
     foreach ($decoded_response['messages'] as $message) {
         if ($message['status'] == 0) {
             $new_message = ['from_number' => '12034869296', 'to_number' => $message['to'], 'message' => $post['text'], 'nexmo_id' => $message['message-id'], 'type' => 1, 'status' => 0, 'to_id' => $post['Recruit_ID'], 'id_number' => $message['to'],
                           ];
             $result = $db->Incoming_Texts()->insert($new_message);
             $response->withJSON($result);

             return $response;
         } else {
             echo 'oops';
          //echo "Error" . $message['status'].$message['error-text'].";
         }
     }

    //return $response;
});
$app->patch('/Texts/{id}/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $text = $db->Incoming_Texts->where('nexmo_id', $args['id']);
    if ($text->fetch()) {
        $post = $request->getParsedBody();
        $result = $text->update($post);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));
    }
});
$app->post('/getToken/', function ($request, $response) use ($app, $server, $db) {
    $checkToken = $server->handleTokenRequest(OAuth2\Request::createFromGlobals()); //->send();
    $checkToken->send();
    $newResponse = $response->withStatus($checkToken->getStatusCode());

    return $newResponse;
});
$app->get('/Recruits/', function ($request, $response) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($db->ReqruitingSummary());
    //$response->withHeader("Content-Type", "application/json");
    //$response->getBody()->write(json_encode($cars, JSON_FORCE_OBJECT));
    return $response;
});
$app->post('/sendColorTestEmail/', function ($request, $response) use ($app, $server, $db) {
    $datas = $request->getParsedBody();
    include 'Emails/colortest.php';
});
$app->get('/Recruits/{status}', function ($request, $response, $args) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($db->ReqruitingSummary()->where('RecruitStatus_ID', $args['status']));
    //$response->withHeader("Content-Type", "application/json");
    //$response->getBody()->write(json_encode($cars, JSON_FORCE_OBJECT));
    return $response;
});
$app->get('/Recruits/NextStep/{search}/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $recruits = $db->ReqruitingSummary()->where('NextStep like ?', '%'.$args['search'].'%');
    $response->withJSON($recruits);

    //$response->withHeader("Content-Type", "application/json");
    //$response->getBody()->write(json_encode($cars, JSON_FORCE_OBJECT));
    return $response;
});
$app->get('/Recruits/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $recruit = $db->RecruitInfo->select('Info')->where('RecruitID', $args['id']);
    $Info = json_decode($recruit->fetch()['Info']);
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Info);
});
$app->get('/Recruit/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id'])->fetch();
    $recruitInfo = $db->RecruitInfo->select('Info')->where('RecruitID', $args['id']);
    $Info = json_decode($recruitInfo->fetch()['Info']);
    $recruit['Info'] = $Info;
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($recruit);
});
//Assign Recruit to User
$app->post('/Recruits/Assign/', function ($request, $response) use ($app, $db, $server) {
    $post = $request->getParsedBody();
        //var_dump($post);
        $response->withHeader('Content-Type', 'application/json');
    $recruit = $db->Tasks()->where('Task_ID', $post['Task_ID']);
    if ($recruit->fetch()) {
        $post = $request->getParsedBody();
        $result = $recruit->update($post);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        $result = $db->Tasks()->insert($post);
        echo 'Done';
        echo json_encode($post);
    }
});
$app->get('/Recruits/{id}/All', function ($request, $response, $args) use ($app, $db, $pdo) {
    $recruit = $db->RecruitInfo->select('Info')->where('RecruitID', $args['id']);
    $Info = $recruit->fetch();
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Info);
});
$app->get('/Recruits/{id}/sms/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $recruit_texts = $db->Incoming_Texts->where('from_id', $args['id'])->or('to_id', $args['id']);

    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($recruit_texts);
});
$app->post('/Recruits/{id}/sms/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $recruit_texts = $db->Incoming_Texts->where('from_id', $args['id']);

    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($recruit_texts);
});
$app->get('/Recruits/sms/summary/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $sth = $pdo->prepare("select *
													from (select a.*,concat(r.FNAME,' ',r.LNAME) as RecruitName,r.ProfilePic,r.INDV_ID from Incoming_Texts  a
															left join ReqruitingSummary r on r.INDV_ID = a.from_id or r.INDV_ID = a.to_id
															order by date_recieved desc) a
													group by INDV_ID");
    $sth->execute();
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($results);

    return $response;
});
$app->post('/Recruits/', function ($request, $response) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $recruit = $request->getParsedBody();
    $result = $db->ReqruitingSummary()->insert($recruit);
    $newRow = $db->ReqruitingSummary()->where('INDV_ID', $result['id']);
    echo json_encode($newRow->fetch());
});
$app->patch('/Recruits/{id}/ProfilePic/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id']);
    $post = $request->getParsedBody();
    $current = $recruit->fetch();
    if ($current) {
        $post = $request->getParsedBody();
        $fileName = uniqid().'-'.uniqid();
                //echo $current['ProfilePic'];
                $ProfilePic = array('ProfilePic' => $fileName.'.jpg');
        copy($post['ProfilePic'], '../../Images/Recruits/'.$fileName.'.jpg');
        if ($current['ProfilePic'] != null) {
            unlink('../../Images/Recruits/'.$current['ProfilePic']);
        }
        $result = $recruit->update($ProfilePic);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
                        'ProfilePic' => $fileName.'.jpg',
            ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));
    }
});
$app->patch('/Recruits/{id}/ProfilePicDataUrl/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id']);
    $post = $request->getParsedBody();
		$img = $post['ProfilePic'];
		echo $img;
    $current = $recruit->fetch();
    if ($current) {
        $post = $request->getParsedBody();
        $fileName = uniqid().'-'.uniqid();
        $ProfilePic = array('ProfilePic' => $fileName.'.jpg');
				$img = str_replace(' ', '+', $img);
  			$fileData = base64_decode($img);
        if ($current['ProfilePic'] != null) {
            unlink('../../Images/Recruits/'.$current['ProfilePic']);
        }
				file_put_contents('../../Images/Recruits/'.$fileName.'.jpg', $fileData);
        $result = $recruit->update($ProfilePic);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
                        'ProfilePic' => $fileName.'.jpg',
            ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));
    }
});

$app->post('/newUser/', function ($request, $response) use ($app, $db, $pdo) {
    $dsn = 'mysql:host=johnsonfinancialservice.com;dbname=anpac_oauth2';
    $username = 'anpac_cody';
    $password = 'skiutah4969';
// error reporting (this is a demo, after all!)
ini_set('display_errors', 1);
    error_reporting(E_ALL);

    OAuth2\Autoloader::register();

// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new OAuth2\Storage\Pdo(array('dsn' => $dsn, 'username' => $username, 'password' => $password));
    $server = new OAuth2\Server($storage);
    $response->withHeader('Content-Type', 'application/json');
    //$recruit = $request->getParsedBody();
 $storage->setUser('Scott', 'Skibum60', 'Scott', 'Johnson');
    $storage->setUser('Sherry', 'Skibum60', 'Sherry', 'Johnson');
    $storage->setUser('Dave', 'Skibum60', 'Dave', 'Moultrie');

    if ($storage->checkUserCredentials('Cody', 'skiutah4969')) {
        echo 'true';
    }
   // $result = $db->ReqruitingSummary()->insert($recruit);
  // echo json_encode(array('id' => $result['id']));
});
$app->patch('/Recruits/{id}', function ($request, $response, $args) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');

    $recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id']);
    if ($recruit->fetch()) {
        $post = $request->getParsedBody();
        var_dump($post);
        echo 'here';
        $result = $recruit->update($post);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));
    }
});
$app->patch('/Recruits/{id}/All/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $response->withHeader('Content-Type', 'application/json');
    $post = $request->getParsedBody();
    $recruit = $db->RecruitInfo()->where('RecruitID', $args['id']);
    if ($recruit->fetch()) {
        $result = $recruit->update($post['Detail']);
       //echo json_encode(array(
         //   "status" => (bool)$result,
           // "message" => "Recruit Info updated successfully"
            //));
    }
    $recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id']);
    if ($recruit->fetch()) {
        $result = $recruit->update($post['Recruit']);
        echo json_encode(array(
            'status' => (bool) $result,
            'message' => 'Recruit updated successfully',
            ));
    } else {
        echo json_encode(array(
            'status' => false,
            'message' => "Couldn't Find ID",
       ));
    }
});
$app->get('/Tasks/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $sth = $pdo->prepare("select a.*, r.*,u.display_name as 'FlaggedBy_DisplayName', u.display_photo as 'FlaggedBy_DisplayPhoto'
																				,u2.display_name as 'Assigned_DisplayName', u2.display_photo as 'Assigned_DisplayPhoto'
																				, concat(r.FNAME,' ',r.LNAME) as RecruitName
													from Tasks a
															left join uc_users u on u.id=a.AssignedBy_ID
															left join uc_users u2 on u2.id=a.User_ID
															left join ReqruitingSummary r on r.INDV_ID = a.Recruit_ID
													");
    $sth->execute();
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($results);
});
$app->get('/Tags/', function ($request, $response, $args) use ($app, $db, $pdo) {
     $sth = $pdo->prepare("select *
													from Tags
													");
    $sth->execute();
    $results = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($results);
    $response->withHeader('Content-Type', 'application/json');
		return $response;
});
$app->patch('/Tags/', function ($request, $response, $args) use ($app, $db, $pdo) {
    $post = $request->getParsedBody();
    $tag = $db->Tags()->where('id', $post['id']);
		$tag2 = $db->Tags()->where('Name', $post['Name']);
    $update = array_intersect_key($post, array_flip(['id'
																										,'Name'
																												]));
		if ($tag->fetch()) {
						$result = $tag->update($update);
						$result = $tag;

		} else if($tag2 = $tag2->fetch()){
			$result = $tag2;
		} else {
				$result = $db->Tags()->insert($update);
		}
    $response->withJSON($result);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});
require 'Routes/UserManagment.php';
require 'Routes/Recruits.php';
require 'Routes/Tasks.php';
require 'Routes/Notifications.php';

$app->group('/Email', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    require 'Routes/Email.php';
});
$app->group('/Agents', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    require 'Routes/Agents.php';
});
$app->group('/Messages', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    require 'Routes/Messages.php';
});
$app->group('/Images', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    require 'Routes/Images.php';
});
$app->run();
