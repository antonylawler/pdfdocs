<?php

$itemid = @$_REQUEST['itemid'];
$pagesets = array_values(json_decode(@$_REQUEST['pagesets']));
$dblink = new mysqli("127.0.0.1","root","password","docs");
$stmt = "select * from apdocs where itemid = $itemid";
$docsitem = array();
$result = mysqli_query($dblink,$stmt);
if ($result) {while($row = mysqli_fetch_row($result)) $docsitem=$row;}
mysqli_close($dblink); 

$pagewords       = explode("\f",$docsitem[9]);
$poswords        = json_decode($docsitem[24]);

$pages           = $docsitem[7];
$newdocsitem     = $docsitem;
$newdocsitem[9]  = '';
$newdocsitem[24] = '';
$newdocsitem[10] = 1; // PageFrom
// e.g. [1,2] on [1,2,3,4,5] results in [1] [2] [3,4,5]
// e.g. [] no split
// Read as "Split everything after x"

if ($pagesets == array() || $pages == 1) {
 markdone($itemid);
} else {
 for ($i=1;$i<=$pages;$i++) {
  $newdocsitem[9][] = $pagewords[$i-1];
  $newdocsitem[24][] = $poswords[$i-1];
  if (in_array($i,$pagesets)) {
   echo(writeit($newdocsitem));
   $newdocsitem[9]  = [];
   $newdocsitem[10] = $i+1;
   $newdocsitem[24] = [];
  }
 }
 if ($newdocsitem[10] <= $pages) {
  echo(writeit($newdocsitem));
 }
 deleteit($itemid);
}

function deleteit($itemid) {
 $conn = new mysqli("127.0.0.1","root","password","docs");
 $stmt = "delete from apdocs where itemid = ".$itemid;
 $result = mysqli_query($conn,$stmt);
 mysqli_close($conn);
}

function writeit($newdocsitem) {
 $newdocsitem[11] = $newdocsitem[10]-1+sizeof($newdocsitem[9]);
 $newdocsitem[9]  = implode("\f",$newdocsitem[9]);
 $newdocsitem[0]  = 0;
 $newdocsitem[1]  = 'Paged';
 $newdocsitem[24] = json_encode($newdocsitem[24]);
 $conn = new mysqli("127.0.0.1","root","password","docs");

 $stmt = "replace into apdocs values (";
 for ($i = 0; $i < sizeof($newdocsitem) - 1; $i++) {$stmt .= "'".mysqli_real_escape_string($conn,$newdocsitem[$i])."',";}
 $stmt .= "'".mysqli_real_escape_string($conn,$newdocsitem[sizeof($newdocsitem)-1])."')";
 $result = mysqli_query($conn,$stmt); 

 $newdocsitem[0] =  $conn->insert_id;
 $stmt = "insert into apdocs_hist values (";
 for ($i = 0; $i < sizeof($newdocsitem) - 1; $i++) {$stmt .= "'".mysqli_real_escape_string($conn,$newdocsitem[$i])."',";}
 $stmt .= "'".mysqli_real_escape_string($conn,$newdocsitem[sizeof($newdocsitem)-1])."')";
 $result = mysqli_query($conn,$stmt) or die(mysqli_error($conn));

 mysqli_close($conn);
 return $result;
}
function markdone($itemid) {
 $conn = new mysqli("127.0.0.1","root","password","docs");
 $stmt = "update apdocs set pagefrom = 1, pageto = pages where itemid = ".$itemid;
 $result = mysqli_query($conn,$stmt); 
 mysqli_close($conn);
 return $result;
}

?>