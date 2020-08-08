<?php
 require_once ("include.php");
 $userid = authenticate(1);
 $username = @$_SESSION['username'];
?>
<html>
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>MAIN MENU</title>
</head>
<body style="color:white;" onload='doonload()'>

<div class="w3-bar w3-green">
 <a class='w3-bar-item'>MAIN MENU</a>
 <div id=usermenu class="w3-dropdown-hover w3-right w3-indigo">
  <button id=username class="w3-button"><?php echo $username;?>  </button>
  <div class="w3-dropdown-content w3-bar-block w3-card-4-4">
   <a href="ulogout.php" class="w3-bar-item w3-button">LOGOUT</a>
  </div>
 </div>
</div>

<script>
 groups = <?php echo(json_encode(@$_SESSION['groups']))?>;
 if (!groups) {groups = 'ANON'};
 qm =  localStorage.getItem('QuickMenu') ? JSON.parse(localStorage.getItem('QuickMenu')) : [];

 for (var i=0;i<qm.length;i++) {
   if (qm[i][1].search('aplist.php') != -1 || qm[i][1].search('manage.php') != -1 ) {qm.splice(i,1); i -=1 ;}
 }
 
 localStorage.setItem('QuickMenu',JSON.stringify(qm));


// menu {[Auth level,link,Wording]}

var menu = {
 'Quick Menu': qm,
 'ACCOUNTS':[
  ['OTHER','apsplit.php','Split Docs'],
  ['OTHER','callglreport.php','Input GL Report Code'],
  ['OTHER','callglchart.php','Input GL Chart Code'],
  ['OTHER','callpledger.php','Input AP']
 ]

}

var dv = null ;

function doonload(){ 

 for (id in menu) {

  var fs = document.createElement('fieldset');
  fs.id = id;
  fs.setAttribute('class','w3-round-large');
  var fsn = document.createElement('legend');
  fsn.setAttribute('class','w3-small w3-teal w3-round w3-card-4 w3-padding');
  fsn.innerHTML = id;
  fs.appendChild(fsn);
  fs.ondragover = function() {event.preventDefault();};
  fs.ondrop= function(ev) {
   if (menu[ev.target.id]) {
   ev.preventDefault() ;
   ev.target.appendChild(dv);
   var qm = [];
   var li = document.getElementById('Quick Menu').childNodes;
   for (i=1;i<li.length;i++) {
    if (li[i].parentNode.id == 'Quick Menu') {
     var ni = [li[i].name,li[i].href,li[i].innerHTML]; 
     qm.push(ni);
    }
   }
   localStorage.setItem('QuickMenu',JSON.stringify(qm));
   }
  }

  for (i=0;i<menu[id].length;i++) {	
   if (groups.indexOf(menu[id][i][0]) || menu[id][i][0] == 'ANON' || groups.indexOf['ALL']) {
   var a = document.createElement("a");
   a.setAttribute('class','w3-button  w3-card-2 w3-green w3-round');
   a.setAttribute('style','padding:5px;margin:5px;');
   a.setAttribute('href',menu[id][i][1]);
   a.innerHTML = menu[id][i][2];
   a.name = menu[id][i][0];
   a.ondragstart = function() {dv = this;};

   fs.appendChild(a);
   }
  }
  var col = document.createElement('div');
  col.setAttribute('class','w3-col s2 w3-small');
  col.appendChild(fs);
  document.getElementById('middles').appendChild(col);
  
 }

}



</script>
<h1 id="thisprog"></h1>
<div id='middles' class='w3-row'>
</div>
</body>

</html>