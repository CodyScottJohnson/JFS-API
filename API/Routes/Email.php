<?php
$app->group('/Send', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    require 'Emails/Templates.php';
});
$app->post('/Static/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $server->getStorage('user_credentials')->getUserDetails($token['user_id']);
    $UserScope = $db->uc_user_permission_matches()->select('permission_id')->where('user_id', $user['UserID']);
    $taskArray = $UserScope->fetch();
    $toast = array_map('iterator_to_array', iterator_to_array($UserScope));
    var_dump($toast);
    if (array_search('1', $toast['permission_id'])) {
        echo 'Got Irix';
    }
});
$app->get('/Templates/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
	
    $EmailTemplates = $db->Email_Templates();
	 	$array1 = array_map('iterator_to_array', iterator_to_array($EmailTemplates));
		array_walk($array1,'ArrayFromJson','Variables');
		$response->withHeader('Content-Type', 'application/json');
    $response->withJSON($array1);
   
});
$app->group('/Lists', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    $app->get('/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
        $ListID = $request->getQueryParam('ListID', $default = 'All');
        if ($ListID == 'All') {
            $people = $db->Email_MailingLists();
            $response->withHeader('Content-Type', 'application/json');
            $response->withJSON($people);
        } else {
            $sth = $pdo->prepare('select b.*
													    from Email_People_MailingList a
                                  inner join Email_People b on b.EmailPeople_ID = a.EmailPeople_ID
													where a.MailingList_ID=:listid');

            $sth->bindValue(':listid', $ListID);
            $sth->execute();
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
            $response->withJSON($results);
        }
    });
    $app->get('/People', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
        $ListID = $request->getQueryParam('ListID', $default = 'All');
        if ($ListID == 'All') {
            $people = $db->Email_People();
            $response->withHeader('Content-Type', 'application/json');
            $response->withJSON($people);
        } else {
            $sth = $pdo->prepare('select b.*
													    from Email_People_MailingList a
                                  inner join Email_People b on b.EmailPeople_ID = a.EmailPeople_ID
													where a.MailingList_ID=:listid');

            $sth->bindValue(':listid', $ListID);
            $sth->execute();
            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
            $response->withJSON($results);
        }
    });
    $app->post('/People', function ($request, $response) use ($app, $db, $pdo) {
        $response->withHeader('Content-Type', 'application/json');
        $contact = $request->getParsedBody();
   // var_dump($contact);
        $result = $db->Email_People->insert($contact);
        $newRow = $db->Email_People()->where('EmailPeople_ID', $result['id']);
        echo json_encode($newRow->fetch());
    });
    $app->delete('/People/{id}', function ($request, $response, $args) use ($app, $db, $pdo) {
        $test = $db->Email_People->where('EmailPeople_ID', $args['id']);
        if ($temp = $test->fetch()) {
            $test->delete();
            echo json_encode($temp);
        } else {
            echo json_encode(array(
          'status' => false,
          'message' => "Couldn't Find ID",
            ));
        }
    });
});
