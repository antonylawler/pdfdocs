<?php
session_start();

function sqlmulti($stmt) {
 $success = true;
 $dblink = getconn();
 if (mysqli_multi_query($dblink, $stmt)) {
  $i = 0;  do {$i++; } while ($dblink->next_result());
 } else {$success = false;}
 mysqli_close($dblink);
 return $success;
}

function sqldelete($table,$arr) {
 $dblink = getconn();
 $arr[2] = '99999';
 $arr[1] = 'DELETED';
 $stmt = "insert into {$table}_hist values (";
 for ($i = 0; $i < sizeof($arr) - 1; $i++) {
  $stmt .= "'".mysqli_real_escape_string($dblink,$arr[$i])."',";
 }
 $stmt .= "'".mysqli_real_escape_string($dblink,$arr[sizeof($arr)-1])."')";
 $result = mysqli_query($dblink,$stmt);
 $stmt2 = '';
 if ($result) {
  $stmt2 = "delete from $table where itemid = '$arr[0]'";
  $result = mysqli_query($dblink,$stmt2);
 }
 $ans = array($result,$stmt,$stmt2,$dblink->affected_rows);
 mysqli_close($dblink);
 return $ans;
}

function sqlwrite($table,$arr) {
 $dblink = getconn();

 $stmt = "insert into {$table}_hist values (";
 for ($i = 0; $i < sizeof($arr) - 1; $i++) {
  $stmt .= "'".mysqli_real_escape_string($dblink,$arr[$i])."',";
 }
 $stmt .= "'".mysqli_real_escape_string($dblink,$arr[sizeof($arr)-1])."')";

 $result = mysqli_query($dblink,$stmt);
 $stmt = "replace into $table values (";
 for ($i = 0; $i < sizeof($arr) - 1; $i++) {
  $stmt .= "'".mysqli_real_escape_string($dblink,$arr[$i])."',";
 }
 $stmt .= "'".mysqli_real_escape_string($dblink,$arr[sizeof($arr)-1])."')";
 $result = mysqli_query($dblink,$stmt); 
 $ans = array($result,$stmt,$dblink->insert_id);
 mysqli_close($dblink);
 return $ans;
}

function sqlreadarray($stmt) {
 $dblink = getconn();
 $results = array();
 $result = mysqli_query($dblink,$stmt);
 if ($result) {
  while($row = mysqli_fetch_row($result)) $results[]=$row;
 } else {
  xd("Failed ".mysqli_error($dblink));
 }
 mysqli_close($dblink);
 return $results;
}

function sqlreadarrayincmeta($stmt) {
 $dblink = getconn();
 $results = "";
 $result = mysqli_query($dblink,$stmt);
 $metadata = @mysqli_fetch_fields($result);
 
 if ($result) {
  while($row = mysqli_fetch_array($result,MYSQLI_NUM)){
   $results[]=$row;
  }
 }
 mysqli_close($dblink);
 return array($results,$metadata);
}

function sqlreadkeyarray($stmt) {
// Assumes unique id in the first column
 $dblink = getconn();
 $results = array();
 $result = mysqli_query($dblink,$stmt);
 if ($result) {
  while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
   $results[$row[0]] = $row;
  }
 }
 mysqli_close($dblink);
 return $results;
}

function sqlreadkeyarrayconsol($stmt) {
//Doesn't assume unique id in the first column
 $dblink = getconn();
 $results = array();
 $result = mysqli_query($dblink,$stmt);
 if ($result) {
  while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
   $results[$row[0]][] = $row;
  }
 }
 mysqli_close($dblink);
 return $results;
}

function sqlread($stmt) {
 $dblink = getconn();
 $result = mysqli_query($dblink,$stmt);
 if ($result) {
  $row = mysqli_fetch_array($result,MYSQLI_NUM);
 } else {
  $row = "";
 }
 mysqli_close($dblink);
 return $row;
}



/*****************************************************
* Connection functions
*****************************************************/
function getconn() {
 $s = @$_SESSION['schema'] ;
 if (strlen($s) > 0 && $dblink = mysqli_connect("127.0.0.1", "root", "password", $s)) return $dblink;
 if (authenticate()) {
  $s = @$_SESSION['schema'] ;
  if (strlen($s) > 0 && $dblink = mysqli_connect("127.0.0.1", "root", "password", $s)) return $dblink;
 }
 terminal_error("Serious connect err ");
}

function getcounter($type,$section) {
 $dblink = getconn();
 $comm = "call uniqueid('".$type."','".$section."')";
 $result = mysqli_query($dblink,$comm);
 if (!$result) terminal_error("Serious counters error $type $section");
 $row = mysqli_fetch_array($result);
 if (!$row) terminal_error("Missing counters error ".$type." ".$section);
 mysqli_close($dblink);
return $row[0];
/*
for mysql:
Needs mysql 5.0 or later
CREATE DEFINER=`root`@`localhost` PROCEDURE `uniqueid`(doctype char(3),unit char(4))
BEGIN
 start transaction ;
 select concat(prefix,cnt) from counters where doctype = counters.doctype and unit = counters.unit for update;
 update counters set cnt=cnt+1 where doctype = counters.doctype and unit=counters.unit      ;
 commit;
END
create table counters doctype chsr(3) , unit mediumint(9), prefix char(10)< cnt mediumint(9)
*/
}
/*****************************************************
* Authentication functions
*****************************************************/
function deobfuscate($obf) {
 $json = json_decode(decrypt(urldecode($obf)));
 $date = @$json->date;
 if (isotojd('')-$date > 60) terminal_error("The link has expired");
 $e = @$json->email;
 if ($e == '') terminal_error("The link does not identify a source");
 return $json;
}

function obfuscate($array) {
 $ans = $array ;
 $ans['date'] = isotojd('');
 $ans = urlencode(encrypt(json_encode($ans)));
 return $ans;
}

function authenticate($prompt = false) {
 if (isset($_SESSION["user"])) return $_SESSION["user"];
 // Get credentials from elsewhere
 if (isset($_REQUEST["userid"]) && isset($_REQUEST["password"])) {
  return dbauthup($_REQUEST["userid"],$_REQUEST["password"]);
 } elseif (isset($_COOKIE["_u"])) { 
  return dbauthcookie();
 } elseif ($prompt) {  
 }
return false; 
}

function isauthorised($group) {
 // Passively confirms whether user is currently authorised
 $groups = @$_SESSION["groups"];
 if (isset($groups[$group])) return true;
 if (isset($groups['ALL'])) return true;
 return false ;
}

function dbauthcookie() {
 if (!($cookie =$_COOKIE["_u"])) return false;
 $dblink = mysqli_connect("127.0.0.1", "root", "password", "cubesecurity");
 if (!$dblink) terminal_error("E01 : Serious connect err ");
 $stmt = "select registereduser.itemid,registereduser.schemaname,registereduser.grp,registereduser.username,registereduser.useremail from registereduser,cookie where isactive = 'Y' and cookie.itemid = '$cookie'";
 if ($result = mysqli_query($dblink,$stmt)) {
  if ($result->num_rows == 1) {
   $row = mysqli_fetch_row($result);     
   $_SESSION['user'] = $row[0];
   $_SESSION['schema'] = $row[1];
   $_SESSION['groups'] = $row[2];
   $_SESSION['username'] = $row[3];
   $_SESSION['useremail'] = $row[4];
   mysqli_close($dblink);
   return $row[0];
  }
 }
 mysqli_close($dblink);
 return false; 
}

function dbauthup($userid, $password) {
 if ($password == "") terminal_error("No password ");
 $dblink = mysqli_connect("127.0.0.1", "root", "password", "cubesecurity");
 if (!$dblink) terminal_error("E01 : Serious connect err ");
 $userid = mysqli_real_escape_string($dblink, $userid);
 $password = mysqli_real_escape_string($dblink, $password);
 $stmt = "select itemid,schemaname,grp,username,useremail from registereduser where isactive = 'Y' ";
 $stmt .= " and password = binary md5(concat('$userid',':REALM:','$password'))";
 $stmt .= " and useremail = '$userid'";
 if ($result = mysqli_query($dblink, $stmt)) {
  if ($result->num_rows == 1) {
   $row = mysqli_fetch_row($result);     
   $_SESSION['user'] = $row[0];
   $_SESSION['schema'] = $row[1];
   $_SESSION['groups'] = $row[2];
   $_SESSION['username'] = $row[3];
   $_SESSION['useremail'] = $row[4];
   if (isset($_REQUEST['cookie'])) {
    $uid = uniqid();
    $sql = "insert into cookie values ('$uid','$row[0]',SYSDATE())";
    if ($ans = mysqli_query($dblink,$sql)) {
     setcookie("_u",$uid,time()+60*60*24*30);
    }
   }
   mysqli_close($dblink);
   return $row[0];
  }
 }
 mysqli_close($dblink);
 return false;
}

function xd($x) {
 print "<pre>";
 print "\n\n\n\n\n\n\n\n";
 print_r($x);
}

function encrypt($word) {
 return strrev(base64_encode($word));
}

function decrypt($word) {
 return base64_decode(strrev($word));
}
function terminal_error($err) {
 echo "<h1>A terminal error has occurred</h1>";
 echo $err;
 exit;
}

/*
 Auth in principle:
  Nothing can get read from the database unless there is a $_SESSION['schema'] is set.
  The $_SESSION['schema'] can be set by:
  - valid user passed in with userid/password pair, validated against cubesecurity.registereduser table
  - valid cookie passed in from $_COOKIE["_u"], mapped from cubesecurity.cookie table
  Whenever $_SESSION['schema'] is set, so is ['user'],['useremail']['groups'], and so is the cookie if requested
 Auth can be active or passive
  - A passive auth will try the cookie and read user info if it is available.
  - An active auth will redirect to a logon screen
*/


?>
