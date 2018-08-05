<?php
 header('Content-Type: image/jpeg');
 $fname=@$_REQUEST['fname'];
 isset($_REQUEST['page']) ? $page=@$_REQUEST['page'] : $page = 0;
 isset($_REQUEST['resolution']) ? $res=$_REQUEST['resolution'] : $res = 100;
 $cmd = "magick -density ".$res." $fname"."[".$page."] jpeg:-";
 passthru($cmd);
?>