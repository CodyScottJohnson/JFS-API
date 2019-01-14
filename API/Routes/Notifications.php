<?php

$app->group('/Notifications', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
    $app->get('/Priority', function ($request, $response, $args) use ($app, $db, $server, $pdo, $storage) {
      $Notifications = $db->Notifications_Important();
      foreach ($Notifications as $key => $value) {
          $Notifications[$key]['Data'] = json_decode($Notifications[$key]['Data']);
      }
      $response->withHeader('Content-Type', 'application/json');
      $response->withJSON($Notifications);
    });
});
