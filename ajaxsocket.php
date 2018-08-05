<?php
// We can do any filtering here. Note that we completely dispense with the standard name-value request model
// Cookie]Session]Call;
  $client = @stream_socket_client("tcp://127.0.0.1:40001", $errno, $err,1);
  isset($_REQUEST['_u']) ? $cookie = $_REQUEST['_u'] : $cookie = @$_COOKIE['_u'];
  if ($client != '') {
   fwrite($client,@$cookie.CHR(20).@$_REQUEST['session'].CHR(20).@strip_tags($_REQUEST['call']));
   $ans = stream_get_contents($client);
  } else {
   $client = stream_socket_client("tcp://127.0.0.1:40002", $errno, $err,1);
   if ($client) {
    fwrite($client,@$cookie.CHR(20).@$_REQUEST['session'].CHR(20).@$_REQUEST['call']);
    $ans = stream_get_contents($client);
   } else {
    $ans = $err;
   }
  }
  fclose($client);
  echo $ans;
?>