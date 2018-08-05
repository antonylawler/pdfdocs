<?php
 $fname = @$_REQUEST['fname'];
 $todoid = @$_REQUEST['todoid'];
 $cmd = "pdftotext -bbox pdfdocs/$fname -";
 exec($cmd,$ans);
 $ans = implode($ans,"");
 echo($todoid." ".$ans);
?>