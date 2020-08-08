<?php
 require_once ("includemenu.php");
 $userid = authenticate();
 if ($userid == '') $userid = 'ANON';
 authorise('OTHER');
 $groups = $_SESSION["groups"];

 if (isset($_REQUEST['itemid'])) {
  // Implementation Specific
  $sql = "select * from glreport where itemid = '{$_REQUEST['itemid']}'";
  $ans = sqlread($sql);
 }
 
 if (!isset($_REQUEST['itemid']) || $ans == '') {
  // Implementation specific
  $ans = array_fill(0,7,"");
  $ans[2] = '0';
 }
/*
0	itemid
1	name
2 editversion
3 editdate
4 editby
5 discontinued
*/
?>
<style>
input:focus {background:#e0ffe0;}
</style>
<div id='bodydiv'></div>
</body>
</html>

<script>
function doctodom() {
var o = "";

o += "<div class='w3-black'>";
o += "<table class='w3-table-border w3-small'>";

o += "<tr class='w3-padding-0 w3-margin-0'><td class='w3-green'>Id</td>";
o += "<td>";
o += "<input id=f_0 value='"+doc[0]+"'>";
o += "</td>";
o += "</tr>";

o += "<tr class='w3-padding-left w3-margin-0'>";
o += "<td class='w3-green'>Description</td>";
o += "<td>";
o += "<input id=f_1 value='"+doc[1]+"'>";
o += "</td>";
o += "</tr>";

o += "<tr class='w3-padding-left w3-margin-0'>";
o += "<td class='w3-green'>Discontinued</td>";
o += "<td>";
o += "<input id=f_5 value="+doc[5]+">";
o += "</td>";
o += "</tr>";

o += "</table>";

o += "<button accesskey=S class='w3-small w3-button w3-green'  onclick='saveit()' id=savebut>Submit</button>";
o += "<button class='w3-small w3-button w3-red' id='cancelbut' onclick='cancelit()'>Cancel</button>";

o += "</div>";
document.getElementById('bodydiv').innerHTML = o;
}

function doevents() {

 var d = document.getElementById('f_0'); // Description
 if (doc[2] == '0') {
  d.onblur    = function() {
   if (localread('glreport',this.value)[1] != '') {
    alert("That ID is already being used");
    this.value = '';
   } else {
    doc[0] = this.value;
   }
  }
 } else {
  d.disabled = true;
 }

 var d = document.getElementById('f_1'); // Description
 d.onblur    = function() {doc[1] = this.value;}

 var d = document.getElementById('f_5'); // Discontinued
 d.onblur    = function() {doc[5] = this.value;}


}

function saveit() {
 document.getElementById('savebut').disabled = true;
 var valid = true;
 
 if (valid) {
  // Implementation Specific
  ajaxsend('ajaxwriteglreport.php',doc,savedone);
 } else {
  document.getElementById('savebut').disabled = false;
  document.getElementById('f_1').focus();
 }  
}

function savedone(response) {
 console.log(response);
 try {
  bits = JSON.parse(response);
  if (bits.status == 'OK') {
   msg = '<h1>'+bits.itemid+' '+bits.message+'</h1>';
   msg += "<form name=form onsubmit='return false;'>";
   // Implementation Specific
   msg += "<button accesskey=C class='w3-button w3-green' onclick='top.location.href=\"callglreport.php\"'>Return To Input</button><br><br>" ;
   msg += "</form>";
   document.getElementById('bodydiv').innerHTML = msg;
  } else {
   alert(response);
   document.getElementById('savebut').disabled = false;   
   console.log(response);
  }
 } catch (e) {
  alert(response);
  document.getElementById('savebut').disabled = false;
  console.log(response);
 }

}

function cancelit() {
 msg = '<h2>Changes Cancelled as requested</h2>';
// Implementation Specific
 msg += "<br><a accesskey='C' class='w3-button w3-green' href=\"callglreport.php\"'>Return to input</a></form>";

 document.getElementById('bodydiv').innerHTML = msg;
}

function ajaxsend(url,doc,whendone) {
 var fd = new FormData();

 fd.append('writeaway',JSON.stringify(doc).replace("'"," ")) ; // This is the doc

 // Add any input images uploaded, as long as they are also displayed.
 // Remembering that we can't delete them from the form.

 var d = document.querySelectorAll('input[type=file]');
 for (var i=0;i<d.length;i++) {
  if (d[i].files[0]) {
   for (var j=0;j<d[i].files.length;j++) {
    if (document.getElementById(d[i].id+'_'+j)) {
     fd.append(d[i].id+'_'+j, d[i].files[j],d[i].id+'_'+j);
    }
   }
  }
 }

 x = new XMLHttpRequest();
 x.open("POST", url);
 x.onreadystatechange = function () {
  if (x.readyState == 4 && x.status == 200) {
   whendone(x.response);
  }
 }
 x.send(fd);
}

function doonload() {
 doctodom();	
 doevents();
 if (doc[2] == 0) {
  document.getElementById('f_0').focus();
  readinlookuptable('glreport',0);
 } else {
  document.getElementById('f_1').focus();
 }
}

function readinlookuptable(tablename, maxage,whendone) {
  // Get a file from the cache or refresh if too old.
  var dt = new Date();
  var age = localStorage.getItem(tablename + '_age');
  var tabledata;
  if (age == null || dt.getTime() - age > 1000 * maxage) {
    var x = new XMLHttpRequest();
    x.open("GET", 'ajaxgettable.php?table=' + tablename);
    x.onreadystatechange = function () {
      if (x.readyState == 4 && x.status == 200) {
        try {
          tabledata = JSON.parse(x.responseText);
          localStorage.setItem(tablename, x.responseText);
          localStorage.setItem(tablename + '_age', dt.getTime());
          if (whendone) whendone();
        } catch (e) {
          alert(e+x.responseText);
          console.log(x.responseText)
        }
      }
    }
    x.send();
  } else {
    try {
      tabledata = JSON.parse(localStorage.getItem(tablename));
    } catch (e) {
      alert(e + tablename + " Unable to use stored data");
      localStorage.removeItem(tablename);
      console.log(localStorage.getItem(tablename));
    }
//   if (whendone) whendone();
  }
//  return tabledata;
}

function localread(file,id) {
 try {var item = JSON.parse(localStorage[file])[id];} catch (e) {};
 if (!item) item = ['',''];
 return item;
}


doc = <?php echo(json_encode($ans));?>;

doonload();

document.getElementById('progname').innerHTML = 'GL Report Manager';
</script>
