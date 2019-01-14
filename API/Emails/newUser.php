<?php 
$post = $request->getParsedBody();
    $mail2 = new PHPMailer;
    $mail2->SMTPDebug = 0;
    $send=true;
    $mail2->isSMTP();
    $mail2->Debugoutput = 'html';
    $mail2->Host = 'smtp.gmail.com';
    $mail2->Port = 587;
    $mail2->SMTPSecure = 'tls';
    $mail2->SMTPAuth = true;
    $mail2->Username = "johnsonfinancialservicewebsite@gmail.com";
    $mail2->Password = "skiutah4969";
    $mail2->setFrom('scott@anpac.net', 'Scott Johnson');
    $mail2->addAddress($post['email']);
    $mail2->isHTML(true);                                  // Set email format to HTML
    $mail2->Subject = 'New Account';

     
     $Template = $db->Email_Templates()->where('Template_ID', 3);
      $HTML = $Template[0]['Template'];
     //$response->withJSON($Template);
    
   // $message = $template->render(array('name' => 'Fabien'));
    $loader = new Twig_Loader_Array(array(
        'template' => $HTML
    ));
    $twig = new Twig_Environment($loader);

    $message = $twig->render('template', array('FNAME' => $post['firstname'],'Username'=>$post['user_name'],'Password'=>$post['password']));
    $mail2->msgHTML($message);

    //send the message, check for errors
    if (!$mail2->send()) {
        //echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        //echo "Message sent!";
    }
?>