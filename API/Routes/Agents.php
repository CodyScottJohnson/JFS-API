<?php
$app->get('/', function ($request, $response) use ($app, $db, $server, $pdo) {
    $Agents = $db->Agents();
   // $sth = $pdo->prepare("select a.*,case when a.User_ID is null then false else true end as 'Active'
		//											from Agents a"
		//													);
    //$sth->execute();
    //$Agents = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($Agents as $Agent){
      if($Agent['User_ID']===null){
        $Agent['Active'] = false;
      }
      else{
        $Agent['Active'] = true;
      }
    }
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Agents);
    return $response;
});
$app->get('/Agent/{Agent_ID}', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $Agents = $db->Agents()->where('Agent_ID',  $args['Agent_ID']);
    foreach ($Agents as $Agent){
      if($Agent['User_ID']===null){
        $Agent['Active'] = false;
      }
      else{
        $Agent['Active'] = true;
      }
    }
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Agents[0]);
    return $response;
});
$app->get('/Agent/{Agent_ID}/DailyNumbers', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
	 	//$AgentsNumbers = $db->Agents_DailyNumbers()->where('Agent_ID', $Agents->fetch()['Agent_ID']);
		$sth = $pdo->prepare("SELECT `id`
															 , :agentid as `Agent_ID`
															 , Fact_Finders
															 , New_Appointments
															 , Quotes
															 , Close
															 , Referrals_AskedFor
															 , Referrals_Recieved
															 , Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY) as Date
															 , week(Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY),0) as week
															 , YEAR(NOW()) as year
												FROM generator_4k a
 														left join `Agents_DailyNumbers` b on b.Date = Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY) and b.Agent_ID = :agentid
												WHERE n<365 and DAYOFWEEK(Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY)) != 1");
    
    $sth->bindValue(':agentid', $args['Agent_ID']);
    $sth->execute();
    $numbers = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($numbers);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Agents);
    return $response;
});
$app->patch('/', function ($request, $response,$args) use ($app, $db, $server, $pdo) {
        $post = $request->getParsedBody();
        $agent = $db->Agents()->where('Agent_ID', $post['Agent_ID']);
        $agent_structue = $db->Agents()->limit(1);
        $agentArray = json_decode(json_encode($agent_structue->fetch()), true);
        $update = array_intersect_key($post, $agentArray);
        if ($agent->fetch()) {
                unset($update['Agent_ID']);
                $result = $agent->update($update);
								$result = $agent;

        } else {
 
            $result = $db->Agents()->insert($update);
        }
    $response->withJSON($result);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});
$app->delete('/{id}', function ($request, $response,$args) use ($app, $db, $server, $pdo) {
      $user = $db->Agents->where('Agent_ID', $args['id']);
			if ($user->fetch()) {
					$user->delete();
						$response->withJSON($user);
							} else {
									echo json_encode(array(
										'status' => false,
										'message' => "Couldn't Find ID",
							 ));
							}
							$response->withHeader('Content-Type', 'application/json');
							return $response;
});
$app->get('/current', function ($request, $response) use ($app, $db, $server, $pdo) {
		$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
    $Agents = $db->Agents()->where('User_ID', $user->fetch()['id']);
    $response->withHeader('Content-Type', 'application/json');
    $response->withJSON($Agents);
    return $response;
});
$app->get('/current/DailyNumbers', function ($request, $response) use ($app, $db, $server, $pdo) {
 		$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
    $user = $db->uc_users->where('user_name', $token['user_id']);
	 	$Agents = $db->Agents()->where('User_ID', $user->fetch()['id']);
	 	//$AgentsNumbers = $db->Agents_DailyNumbers()->where('Agent_ID', $Agents->fetch()['Agent_ID']);
		$sth = $pdo->prepare("SELECT `id`
															 , :agentid as `Agent_ID`
															 , Fact_Finders
															 , New_Appointments
															 , Quotes
															 , Close
															 , Referrals_AskedFor
															 , Referrals_Recieved
															 , Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY) as Date
															 , week(Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY),0) as week
															 , month(Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY)) as month
															 , YEAR(NOW()) as year
												FROM generator_4k a
 														left join `Agents_DailyNumbers` b on b.Date = Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY) and b.Agent_ID = :agentid
												WHERE n<365 and DAYOFWEEK(Date_ADD(concat(YEAR(NOW()),'-01-01'), INTERVAL n DAY)) != 1");
    
    $sth->bindValue(':agentid', $Agents->fetch()['Agent_ID']);
    $sth->execute();
    $numbers = $sth->fetchAll(PDO::FETCH_ASSOC);
    $response->withJSON($numbers);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});
$app->patch('/current/DailyNumbers', function ($request, $response) use ($app, $db, $server, $pdo) {
		     $post = $request->getParsedBody();
        $agent = $db->Agents_DailyNumbers()->where('Date', $post['Date']);
        $agent_structue = $db->Agents_DailyNumbers()->limit(1);
        $agentArray = json_decode(json_encode($agent_structue->fetch()), true);
        $update = array_intersect_key($post, $agentArray);
        if ($agent->fetch()) {
                unset($update['id']);
								echo 'here';
                $result = $agent->update($update);

        } else {
 						echo 'there';
            $result = $db->Agents_DailyNumbers()->insert($update);
        }
    $response->withJSON($result);
    $response->withHeader('Content-Type', 'application/json');
    return $response;
});
?>