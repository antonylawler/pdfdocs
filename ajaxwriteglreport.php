<?php
include('include.php');
$v = @json_decode($_REQUEST['writeaway']);
$v[2] = $v[2]*1 + 1; // Version number
$v[3] = @date("Y-m-d H:i:s") ;
$v[4] = authenticate(false); // User

list($ans,$sql,$itemid) = sqlwrite("glreport",$v);

if ($ans ==1) {
 $ans = array('status'=>'OK','itemid'=>$v[0],'message'=>'Filed');
 echo json_encode($ans);
} else {
 $ans = array('status'=>'Failed to write','id'=>$ans,'counter'=>$v[0],'auth'=>authenticate(false),'response'=>$ans);
 echo json_encode($ans);
}

?>