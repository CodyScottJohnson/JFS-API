<?php
$app->group('/Task', function ($request, $response, $args) use ($app, $db, $server, $pdo,$storage) {
$app->post('/', function ($request, $response) use ($app, $db, $server) {

	$response->withHeader('Content-Type', 'application/json');
    $task = $request->getParsedBody();
    $result = $db->Tasks()->insert($task);
		$newRow =$db->Tasks()->where('Task_ID',$result['id']);
    echo json_encode($newRow->fetch());
   
});
$app->patch('/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
     $post = $request->getParsedBody();
     $task = $db->Tasks()->where('Task_ID', $post['Task_ID']);
		
    if ($taskArray = $task->fetch()) {
				$update = array_intersect_key($post, array_flip([
																													'User_ID',
																													'Recruit_ID',
																													'AssignedBy_ID',
																													'Reminder_Date',
																													'Due_Date',
																													'Completed_Date',
																													'Completed',
																													'Priority',
																													'Title',
																													'Level',
																													//'Created_Date',
																													'Status',
																													'Detail',
																													//'Group',
																												]));
        $result = $task->update($update);
			echo json_encode($update);
				
    }
    if(isset($post['More_Detail'])){
    $task_detail = $db->Task_Detail()->where('Task_ID', $post['Task_ID']);
    if ($task_detail->fetch()) {
      $Info=array('More_Detail'=>json_encode($post['More_Detail']));
      $result = $task_detail->update($Info);
    }
   else{
      $Info=array('Task_ID'=> $post['Task_ID'],'More_Detail'=>json_encode($post['More_Detail']));
      $db->Task_Detail()->insert($Info);
    }
  }
});
$app->get('/{taskid}/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
        $sth = $pdo->prepare("select a.*, r.*,u.display_name as 'FlaggedBy_DisplayName', u.display_photo as 'FlaggedBy_DisplayPhoto'
																				,u2.display_name as 'Assigned_DisplayName', u2.display_photo as 'Assigned_DisplayPhoto'
													from Tasks a
															left join uc_users u on u.id=a.AssignedBy_ID
															left join uc_users u2 on u2.id=a.User_ID
															left join ReqruitingSummary r on r.INDV_ID = a.Recruit_ID
													where a.Task_ID = :taskid
													");
	      $sth->bindValue(':taskid', $args['taskid']);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
	 			$TaskInfo = $db->Task_Detail->select('More_Detail')->where('Task_ID', $args['taskid']);
      	$Info = json_decode($TaskInfo->fetch()['More_Detail']);
			  $results[0]['More_Detail']=$Info;
        $response->withJSON($results);

});
  $app->get('/Recruit/{recruitid}/', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
            $sth = $pdo->prepare("select a.*, r.*,u.display_name as 'FlaggedBy_DisplayName', u.display_photo as 'FlaggedBy_DisplayPhoto'
																				,u2.display_name as 'Assigned_DisplayName', u2.display_photo as 'Assigned_DisplayPhoto'
													from Tasks a
															left join uc_users u on u.id=a.AssignedBy_ID
															left join uc_users u2 on u2.id=a.User_ID
															left join ReqruitingSummary r on r.INDV_ID = a.Recruit_ID
                          where a.Recruit_ID = :recruitid
													");
        $sth->bindValue(':recruitid', $args['recruitid']);
        $sth->execute();
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
				
        $response->withJSON($results);

});

});
  
?>