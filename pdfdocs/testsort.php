<pre>
<?php
 $a = [[13,2],[1,1],[4,2],[1,1]];
 asort($a);
 foreach ($a as $id=>$item) {
  print($item[0].' '.$item[1]."<br>");
 }
?>