<!DOCTYPE html>
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/local.css">
<script src="js/bpif.js"></script>
<script src="js/sortable.js"></script>
<style>
 .h12{height:12px;}
 h1 {color:white;}
</style>
<?php
 error_reporting(E_ALL);
 ini_set('display_errors', 1);
  $dblink = new mysqli("127.0.0.1","root","password","docs");
 $stmt     = "select * from apdocs order by itemid";
 $wordset  = array();
 $result   = mysqli_query($dblink,$stmt);

 while($row = mysqli_fetch_row($result)) {$docs[]=$row;}

 mysqli_close($dblink);
?>
<head>
 <title>All Downloaded Emails</title>
</head>
<body style='background:black'>
<h1>All downloaded PDF documents</h1>
<table id="sorttable" class="w3-small sortable" style="width:100%">
<thead>
 <tr class='w3-gray'>
  <th>ID</th><th>Subject</th><th>PDF</th><th>Supplier ID</th><th>Document Type</th><th></th>
  <th>Invoice No.</th><th>Purchase Order</th><th>Date</th><th>Goods</th><th>VAT</th><th>Total</th>
  <th>Posted</th>
 </tr>
</thead>
<?php
 foreach ($docs as $id=>$item) {
  $o = '<tr class="w3-green">';
  $o .= "<td>$item[0]</td>";
  $o .= "<td>$item[1]</td>";
  $o .= "<td><a href=pdfdocs/$item[6]>$item[6]</a></td>";
  $o .= "<td>$item[12]</td>";
  $o .= "<td>$item[13]</td>";
  $o .= "<td>$item[14]</td>";
  $o .= "<td>$item[15]</td>";
  $o .= "<td>$item[16]</td>";
  $o .= "<td>$item[17]</td>";
  $o .= "<td class=r>$item[18]</td>";
  $o .= "<td class=r>$item[19]</td>";
  $o .= "<td class=r>$item[20]</td>";
  $o .= "<td class=r>$item[22]</td>";
  $o .= '</tr>';
  echo($o);
 }
?>
</table>
</body>
</html>