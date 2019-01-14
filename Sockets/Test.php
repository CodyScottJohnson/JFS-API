<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
$startedAt = time();
//do {
  // Cap connections at 10 seconds. The browser will reopen the connection on close
 // if ((time() - $startedAt) > 60) {
  //  die();
 // }
$time = date('r');

function f($redis, $chan, $msg) {
    switch($chan) {
        case 'chan-1':
            echo "data: {$chan}\n\n";
          
            break;
        case 'chan-2':
            echo "data: {$chan}\n\n";
      
            break;
        case 'chan-3':
            break;
    }
   //ob_end_flush();     // Strange behaviour, will not work
   flush();
   sleep(3);
   die();
    
   
}
 
ini_set('default_socket_timeout', -1);
 
$redis = new Redis();
$redis->pconnect('127.0.0.1',6379);
$redis->subscribe(array('chan-1', 'chan-2', 'chan-3'), 'f');
print "\n";
?>
 