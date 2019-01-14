<?php
$app->patch('/MarkConversation/{id}/', function ($request, $response,$args) use ($app, $db, $server,$pdo) {
    $sth = $pdo->prepare("update Incoming_Texts i
                         set i.status = 1
                         where i.id_number = :id;
													");
    $sth->bindValue(':id', $args['id']);
    $sth->execute();
    return $response;
});
$app->delete('/DeleteConversation/{id}/', function ($request, $response,$args) use ($app, $db, $server,$pdo) {
    $sth = $pdo->prepare("delete from Incoming_Texts
                         where id_number = :id;
													");
    $sth->bindValue(':id', $args['id']);
    $sth->execute();
    return $response;
});
?>