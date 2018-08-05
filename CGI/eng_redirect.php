<?php
$path = '..\\..\\';
$path = '\\.';
$list = scandir($path);

foreach ($list as $id=>$src) {
echo $dest;
 $dest = str_replace('%S','\\',$src);
 $dest = str_replace('\\\\','\\',$dest);
 if ($dest != $src) {
  
  rename($path.$src,$path.$dest);
 }
}

?>
