<?php
$call   = @$_REQUEST['call'];
$resp   = json_decode($call);
$conn   = new mysqli("127.0.0.1","root","password","docs");
$stmt   = "update apdocs set supplierid = '{$resp[12]}' where itemid = ".$resp[0];
$result = mysqli_query($conn,$stmt); 
mysqli_close($conn);
return $result;

?>