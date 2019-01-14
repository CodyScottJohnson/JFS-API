<?php
//Validate Request
if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
    $server->getResponse()->send();
    die;
}
$app->group('/UserManagement', function ($request, $response, $args) use ($app, $db,$db2, $server, $pdo,$storage) {

    // Library group
    $app->group('/', function ($request, $response, $args) use ($app, $db,$db2, $server, $pdo,$storage) {

        // Get List Of Users
        $app->get('', function ($request, $response, $args) use ($app, $db, $server, $pdo) {

            $users = $db->uc_users->select('display_name,display_photo,email,user_name,active,title');
            $response->withHeader('Content-Type', 'application/json');
            $response->withJSON(array_map('iterator_to_array', iterator_to_array($users)));

            return $response;
        });
				$app->delete('Remove/{user_id}/', function ($request, $response,$args) use ($app, $db,$db2, $server, $pdo) {
							$user = $db->uc_users->where('id', $args['user_id']);
							if ($user2 = $user->fetch()) {
									$oauth = $db2->oauth_users->where('username', $user2['user_name']);
									$oauth->delete();
									$user->delete();
									$sth = $pdo->prepare("update Agents
																				set User_ID = null
													where User_ID=:userid");
    
    							$sth->bindValue(':userid', $args['user_id']);
    							$sth->execute();
									$sth = $pdo->prepare("delete from `uc_user_permission_matches`
																				where User_ID not in (select id from `uc_users`)");
    							$sth->execute();
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
        // Update book with ID
        $app->post('user/', function ($request, $response, $args) use ($app, $db, $server, $pdo,$storage) {
             $post = $request->getParsedBody();
							$displayname = $post['firstname'].' '.$post['lastname'];
							$user = new User($post['user_name'], $displayname, $post['password'], $post['email']);
									//Checking this flag tells us whether there were any errors such as possible data duplication occured
									if (!$user->status) {
											if ($user->username_taken) {
												$errors[] = lang('ACCOUNT_USERNAME_IN_USE', array($username));
											}
											if ($user->displayname_taken) {
													$errors[] = lang('ACCOUNT_DISPLAYNAME_IN_USE', array($displayname));
											}
											if ($user->email_taken) {
													$errors[] = lang('ACCOUNT_EMAIL_IN_USE', array($email));
											}
									} else {
											//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
											if (!$user->userCakeAddUser()) {
													if ($user->mail_failure) {
															$errors[] = lang('MAIL_ERROR');
													}
													if ($user->sql_failure) {
															$errors[] = lang('SQL_ERROR');
													}
											}
									}
							if (count($errors) == 0) {
									$storage->setUser($post['user_name'], $post['password'], $post['firstname'], $post['lastname'],$user->newUser['User_ID'],$user->getNewPassword());
									require 'Emails/newUser.php';
									$userdetails = fetchUserDetails($post['user_name']);
									$hooks = array(
																	"searchStrs" => array("#GENERATED-PASS#","#USERNAME#"),
																		"subjectStrs" => array($post['password'],$post['firstname'])
																	);
									$type = $request->getQueryParam('Type');
									if(isset($type) & $type=='admin'){
										$user = $db->uc_users->where('id', $user->newUser['User_ID']);
										$array = array("title"=>"Administrator");
										$user->update($array);
										$user->newUser['title'] = "Administrator";
									}
									echo json_encode($user->newUser);
							}
							else{
								$response->withJSON($errors);
								$newResponse = $response->withStatus(400);
								return $newResponse;
							}

        });
				$app->post('user/changepassword', function ($request, $response, $args) use ($app, $db, $server, $pdo,$storage) {
						$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());
            $user = $db->uc_users->where('user_name', $token['user_id']);
            $post = $request->getParsedBody();
						$uc_pass = generateHash($post['password']);
						$oauth_pass = sha1($post['password']);
						$change = array('password'=>$uc_pass,'password_reset'=>0);
					 	if ($user = $user->fetch()) {
        				$result = $user->update($change);
						 		//$storage->setUser($user->fetch()['user_name'], $post['password'], NULL, NULL,NULL,$uc_pass);	
						 		$storage->setUser($user['user_name'], $post['password'], NULL, NULL,NULL,$uc_pass);
						  	echo json_encode($change);
    				}		 
					
        });

        // Delete book with ID
        $app->delete('/books/:id', function ($request, $response, $args) {

        });

    });
  $app->group('/{username}/user/', function () use ($app, $db, $server, $pdo,$storage) {
     $app->delete('', function ($request, $response, $args) use ($app, $db, $server, $pdo,$storage) {
          echo "here";
        });
    $app->post('resetpassword/', function ($request, $response, $args) use ($app, $db, $server, $pdo,$storage) {
          
          $userdetails = fetchUserDetails($args['username']);
      $token = $userdetails["activation_token"];
			$rand_pass = getUniqueCode(15); //Get unique code
			$secure_pass = generateHash($rand_pass); //Generate random hash
			$userdetails = fetchUserDetails(NULL,$token); //Fetchs user details
			$mail = new userCakeMail();		

			//Setup our custom hooks
			$hooks = array(
			"searchStrs" => array("#GENERATED-PASS#","#USERNAME#"),
			"subjectStrs" => array($rand_pass,$userdetails["display_name"])
			);
			if(!$mail->newTemplateMsg("your-lost-password.txt",$hooks))
			{
        echo 'here';
			$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
			}
			else
			{	
			if(!$mail->sendMail($userdetails["email"],"Your new password"))
			{
			$errors[] = lang("MAIL_ERROR");
			}
			else
			{
			if(!updatePasswordFromToken($secure_pass,$token))
			{
			$errors[] = lang("SQL_ERROR");
			}
			else
			{	
			if(!flagLostPasswordRequest($userdetails["user_name"],0))
			{
			$errors[] = lang("SQL_ERROR");
			}
			else {
        $storage->setUser($args['username'], $rand_pass, NULL, NULL,NULL,$secure_pass);
				$successes[]  = lang("FORGOTPASS_NEW_PASS_EMAIL");
			}
      }
      }
      }
        });
    
  });

});
?>