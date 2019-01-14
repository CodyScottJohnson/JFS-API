<?php

$app->group('/v2/Recruits', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
  $app->get('/', function ($request, $response, $args) use ($app, $db, $pdo) {
        $response->withHeader('Content-Type', 'application/json');
        $post = $request->getParsedBody();
        $sth = $pdo->prepare("SELECT *
												FROM ReqruitingSummary
 												");
    
        //$sth->bindValue(':agentid', $args['Agent_ID']);
        $sth->execute();
        $recruits = $sth->fetchAll(PDO::FETCH_ASSOC);
 
        // Setup eager list of IDs
        $ids = [];
        foreach($recruits as $recruit) {
          $ids[] = (int) $recruit['INDV_ID'];
        }
        $recruit_ids = implode(',', $ids);

        // Get all books and group them
        //var_dump($recruit_ids);
        $grouped_tags = [];
        $sth = $pdo->prepare("SELECT a.id,Name,Recruit_ID
												FROM Tags a
                          inner join Recruit_Tag t on t.Tag_ID = a.id
                          where Recruit_ID in ({$recruit_ids})
 												");
        $sth->execute();
        $tags = $sth->fetchAll(PDO::FETCH_ASSOC);
        //var_dump($tags);
        foreach($tags as $tag) {
        $grouped_tags[$tag['Recruit_ID']][] = $tag;
        }

        // Final loop
        foreach ($recruits as &$recruit) {
          $recruit['Tags'] = $grouped_tags[$recruit['INDV_ID'] ];
        }
      $response->withHeader('Content-Type', 'application/json');
      $response->withJSON($recruits);
      return $response;
    });
		$app->get('/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
			$recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id'])->fetch();
			$recruitInfo = $db->RecruitInfo->select('Info')->where('RecruitID', $args['id']);
			 $sth = $pdo->prepare("SELECT a.id,Name,Recruit_ID
														FROM Tags a
                          		inner join Recruit_Tag t on t.Tag_ID = a.id
                          	where Recruit_ID = {$args['id']}
 													");
        $sth->execute();
        $tags = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			$Info = json_decode($recruitInfo->fetch()['Info']);
			$recruit['Info'] = $Info;
			$recruit['Tags'] = $tags;
			$response->withHeader('Content-Type', 'application/json');
			$response->withJSON($recruit);
	});
    $app->patch('/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
        $response->withHeader('Content-Type', 'application/json');
        $post = $request->getParsedBody();
        $recruit = $db->RecruitInfo()->where('RecruitID', $args['id']);
        if ($recruit->fetch()) {
            if (isset($post['Info'])) {
                $Info = array('Info' => json_encode($post['Info']));
                $result = $recruit->update($Info);
            }
        } else {
            $Info = array('RecruitID' => $args['id'], 'Info' => json_encode($post['Info']));
            $db->RecruitInfo()->insert($Info);
        }
        $recruit = $db->ReqruitingSummary()->where('INDV_ID', $args['id']);
        if ($recruitArray = $recruit->fetch()) {
            $recruitArray = json_decode(json_encode($recruitArray), true);
            $update = array_intersect_key($post, $recruitArray);
            $result = $recruit->update($update);
        }
    });
    $app->post('/{id}/Tag/{tagid}', function ($request, $response, $args) use ($app, $db, $pdo) {
        $response->withHeader('Content-Type', 'application/json');

        $recruit = $db->Recruit_Tag()->where(array('Recruit_ID'=> $args['id'],"Tag_ID"=> $args['tagid']));
        if ($recruit->fetch()) {
            $result = array('Error' => "tag already exists");
        } else {
            $Tag = array('Recruit_ID' => $args['id'], 'Tag_ID' => $args['tagid']);
            $db->Recruit_Tag()->insert($Tag);
        }
    });
		$app->delete('/{id}/Tag/{tagid}', function ($request, $response, $args) use ($app, $db, $pdo) {
         $tag = $db->Recruit_Tag()->where(array('Recruit_ID'=> $args['id'],"Tag_ID"=> $args['tagid']));
        if ($tag->fetch()) {
            $tag->delete();
        } else {
            echo json_encode(array(
          'status' => false,
          'message' => "Couldn't Find ID",
            ));
        }
    });
    $app->delete('/{id}/', function ($request, $response, $args) use ($app, $db, $pdo) {
        $test = $db->ReqruitingSummary->where('INDV_ID', $args['id']);
        if ($test->fetch()) {
            $test->delete();
        } else {
            echo json_encode(array(
          'status' => false,
          'message' => "Couldn't Find ID",
            ));
        }
    });
});
