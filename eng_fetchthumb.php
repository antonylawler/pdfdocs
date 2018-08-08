<?php
 header('Content-Type: image/png');
 $fname=@$_REQUEST['fname'];
 isset($_REQUEST['page']) ? $page=@$_REQUEST['page']+1 : $page = 1;
 isset($_REQUEST['resolution']) ? $res=$_REQUEST['resolution'] : $res = 300;
 $cmd = "magick -monochrome -density $res $fname"."[".($page-1)."] jpeg:-";

 passthru($cmd);
?>