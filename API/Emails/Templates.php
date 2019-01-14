<?php
$app->post('/Template', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
  $guid = GUID();
  $post = $request->getParsedBody(); 
  $post['EmailData']['GUID'] = $guid;
  $html = sendCustomTemplate($post['Email'],$post['Subject'],$post['EmailData'],$post['Template'],'','');
  $newEmail = array("Recruit_ID"=>$post['RecruitID'],"Email_ID"=>$guid,"HTML"=>$html,'Date_Sent'=>gmdate("Y-m-d\TH:i:s\Z"));
  $result = $db->Email_Sent()->insert($newEmail);
  $response->withJSON($result);
});
$app->post('CustomTemplate', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
   //sendTemplate($result['email'],'Task Due: '.$result['Title'],$result,5,$db);
});
$app->post('/PreviewTemplate', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
   //sendTemplate($result['email'],'Task Due: '.$result['Title'],$result,5,$db);
    $post = $request->getParsedBody();  
    echo BuildEmail($post['Template'],$post['EmailData']);
});
$app->post('/ContactCard', function ($request, $response, $args) use ($app, $db, $server, $pdo) {
    $post = $request->getParsedBody();
    $mail = new PHPMailer;
    $send=true;
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = "johnsonfinancialservicewebsite@gmail.com";
    $mail->Password = "skiutah4969";
    $mail->setFrom('scott@anpac.net', 'Scott Johnson');
    $mail->addAddress($post['Email']['To']);
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Header = 
    $mail->Subject = 'Here Is My Contact Info';

     
     $Template = $db->Email_Templates()->where('Template_ID', 2);
      $HTML = $Template[0]['Template'];
     //$response->withJSON($Template);
    
   // $message = $template->render(array('name' => 'Fabien'));
    $loader = new Twig_Loader_Array(array(
        'template' => $HTML
    ));
    $twig = new Twig_Environment($loader);

    $message = $twig->render('template', array('FNAME' => $post['Email']['FNAME']));
    $mail->msgHTML($message);
    $mail->addAttachment("/srv/jfsapp/public_html/CScottJohnson.vcf", 'Contact Info.vcf');
    $mail->addAttachment("/srv/jfsapp/public_html/Map.jpg", 'Map to Office.jpg');
    //send the message, check for errors
    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Message sent!";
    }
});
?>