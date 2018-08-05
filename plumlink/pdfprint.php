<?php
$filename = $_REQUEST['filename'];
$dest = $_REQUEST['dest'];
$filename = str_replace('/','\\',$filename);
//$cmd = 'copy '.$filename.' \\\127.0.0.1\\'.$dest ;
$cmd = 'lpr -S '.$dest.' -P "" '.$filename;

exec($cmd);
echo $cmd;
?>
