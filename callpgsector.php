<?php require_once('includemenu.php'); ?>
<br>
<body class="w3-black">
<div id='topsearch'>
<div class="w3-row"><div class="w3-col m10">Start typing to search (Space for all) <input autofocus id=chooser onkeyup=research()>
<button class="w3-button w3-green" accesskey=N onclick=newitem()><u>N</u>ew Item</button></div></div>
<br/>
<div id='bodydiv' >
</div>
</body>
<?php
// Implementation Specific
$sql = "select itemid,name from pgsector where name is not null";
$ans = sqlreadarray($sql);
?>
<script>

var l = <?php echo json_encode($ans);?>;
localStorage.setItem('select', JSON.stringify(l));
function newitem() {
// Implementation Specific
 top.location.href = 'inputpgsector.php';
}
// Implementation Specific
document.getElementById('progname').innerHTML = 'Sector Manager';
document.getElementById('chooser').onkeyup   = function() {searchkup('select','selectdone');}
document.getElementById('chooser').onkeydown = function() {selectsearch();}

function selectdone(itemid) {
// Implementation Specific
 top.location.href = 'inputpgsector.php?itemid='+itemid;
}

function selectsearch() {
 // Handles navigation in case a select list is present.
 var k = event.keyCode;
 if (document.getElementById('nextpage') && (k == 39 || k == 34)) { // Right
  document.getElementById('nextpage').click();
  event.preventDefault();
  event.stopPropagation();    
  downdone = true;
 } else if (document.getElementById('prevpage') && (k == 37 || k == 33)) { // Left
  document.getElementById('prevpage').click();
  event.preventDefault();
  event.stopPropagation();    
  downdone = true;
 } else if (document.getElementById('SELECTLIST') && (k == 39 || k == 34 || k == 37 || k == 33)) { 
  downdone = true;
  event.preventDefault();
  event.stopPropagation();    
 } else if (document.getElementById('SELECTLIST') && k == 38) { // Up
  var s = document.getElementById('selected');
  if (s) {
   if (s.previousSibling) {
    if (s.rowIndex>1) {
     s.id = '';
     s.setAttribute('class','w3-black');
     s.previousSibling.setAttribute('class','w3-grey');
     s.previousSibling.id = 'selected';
    }
   }
  }
  event.preventDefault();
  event.stopPropagation();    
  downdone = true;
 } else if (document.getElementById('SELECTLIST') && k == 40) { // Down
  var s = document.getElementById('selected');
  if (s) {
   if (s.nextSibling) {
    s.id = '';
    s.setAttribute('class','w3-black');
    s.nextSibling.setAttribute('class','w3-grey');
    s.nextSibling.id = 'selected';
   }
  }
  event.preventDefault();
  event.stopPropagation(); 
  downdone = true;
 } else if (k == 27) { // Escape
  removesel();
  downdone = true;
 } else if ((k == 9 || k == 13) && document.getElementById('selected')) { // Tab or CR
  var s = document.getElementById('selected').firstChild.innerText;
  if (s) {
   document.getElementById('selected').click(s,event.target.id);
   event.preventDefault();
   event.stopPropagation();    
   downdone = true;
  }  
 }
}

function searchkup(searchtablename,whenclicked) {
 if (!downdone) {
  downdone = false;
  if (event.target.value == '') {
   removesel()
  } else {
   var searchtable  = JSON.parse(localStorage.getItem(searchtablename));
   filtertable(event.target.value,searchtablename,whenclicked,event.target.id);
  }
 }
 downdone = false;
}

function removesel() {
 var o  = document.getElementById('SELECTLIST');
 if (o) o.parentNode.removeChild(o);
}

function filtertable(searchfor,searchtablename,oc,targetid,page) {
 if (!page) page  = 0;
 var searchtable  = JSON.parse(localStorage.getItem(searchtablename));
 var o  = document.getElementById('SELECTLIST');
 if (!o) {
  var o = document.createElement('table');
  o.id               = 'SELECTLIST';
  o.className        = 'w3-table w3-bordered w3-black';
 }
 o.innerHTML = '';
 var fl = document.createElement('tr');
 // Implementation Specific
 fl.className = 'w3-green';
 fl.innerHTML = '<td>ID</td><td>Description</td></td>';
 o.appendChild(fl);
 var words = searchfor.toUpperCase().split(' ');
 showcount = 0;
 keepcount = 0;
 gotid     = false;
 for (var i in searchtable) {
  var keep  = true ;
// Implementation Specific
  var srch  = searchtable[i][0].toUpperCase() + ' ' + searchtable[i][1].toUpperCase();
  for (var j = 0;j<words.length;j++) {
   if (srch.indexOf	(words[j]) == -1) {keep = false; break;}
  }
  if (keep) {
   keepcount +=1 ;
   if (keepcount>page*10) {
    showcount += 1;
    if (showcount > 10) break;
    var c       = document.createElement('tr');
// Implementation Specific
    var tds     = '<td>'+searchtable[i][0]+'</td>';
    tds        += '<td>'+searchtable[i][1]+'</td>';
    c.innerHTML = tds;
    c.setAttribute('class','w3-black');
    if (showcount == 1) {
     c.id = 'selected';
     c.setAttribute('class','w3-gray');
    }
    c.onclick = function() {window[oc](this.firstChild.innerText,targetid)};    
    c.onmouseover = function() {
     var s = document.getElementById('selected');
     if (s != this && s.rowIndex != 0) {s.id = '';s.setAttribute('class','w3-black');}
     this.setAttribute('class','w3-gray');
     this.id = 'selected';
    }
    o.appendChild(c);
   }
  }
 }


 if (page > 0 || showcount > 10) {
  var tr = document.createElement('tr');
  var td1 = document.createElement('td');
  var td2 = document.createElement('td');
  if (page > 0) {
   td1.innerText = "Previous Page";
   td1.id = 'prevpage';
   td1.onclick  = function() {
    document.getElementById(targetid).focus();
    filtertable(searchfor,searchtablename,oc,targetid,page-1);
   }
  }
  if (showcount > 10) {
   td2.innerText = "Next Page";
   td2.id = 'nextpage';
   td2.onclick  = function() {
    document.getElementById(targetid).focus();
    filtertable(searchfor,searchtablename,oc,targetid,page+1);
   }
  }
  tr.appendChild(td1);
  tr.appendChild(td2);
  tr.setAttribute('class','w3-black');
  o.appendChild(tr);
 }
 if (o) {
   document.getElementById('bodydiv').appendChild(o); 
 } else {
   removesel();
 }
 return o;
}

function isotoout(d) {
 // 2014-09-31 -> 31-09-14
 // 2014-09-31 16:00:00 -> 31-09-14
 d = d.split(' ')[0];
 d = d.split('-');
 d = d[2]+'-'+d[1]+'-'+d[0];
 return d;
}

downdone = false;

</script>