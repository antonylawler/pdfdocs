<!DOCTYPE html>
<link rel="stylesheet" href="css/w3.css">
<head>
 <title>AQUATEC</title>
 <script src="js/bpif.js"></script>
</head>
<body style="background:black;color:white;" onload="doonload()">

<div class="w3-bar w3-green">
 <div class='w3-bar-item'>AQUATEC</div>
 <a id=signin href="login.php" class="w3-bar-item w3-button">SIGN IN</a>
 <div id=usermenu class="w3-dropdown-hover w3-right w3-indigo">
  <button id=username class="w3-button">X</button>
  <div class="w3-dropdown-content w3-bar-block w3-card-4-4">
   <a href="ulogout.php" class="w3-bar-item w3-button">LOGOUT</a>
  </div>
 </div>
</div>


<script>
var qm =  localStorage.getItem('QuickMenu') ? JSON.parse(localStorage.getItem('QuickMenu')) : [];
var menu = {
 'Quick Menu': qm,
 'Input':[
  ['WRITE.ACCOUNTMANAGER','flatcall.php?THISPROG=WRITE.ACCOUNTMANAGER','Account Manager'],
  ['WRITE.PROJECTMANAGER','flatcall.php?THISPROG=WRITE.PROJECTMANAGER','Project Manager'],
  ['WRITE.CURRENCY','flatcall.php?THISPROG=WRITE.CURRENCY','Currency Manager'],
  ['WRITE.JOB','flatcall.php?THISPROG=WRITE.JOB','Job Manager'],
  ['WRITE.BRANCH','flatcall.php?THISPROG=WRITE.BRANCH','Branch Manager'],
  ['WRITE.CUSTOMER','flatcall.php?THISPROG=WRITE.CUSTOMER','Customer Manager'],
  ['WRITE.GL.CHART','flatcall.php?THISPROG=WRITE.GL.CHART','GL Chart Manager'],
  ['WRITE.GST','flatcall.php?THISPROG=WRITE.GST','GST Manager'],
  ['WRITE.JOURNAL','flatcall.php?THISPROG=WRITE.JOURNAL','GL Journal'],
  ['WRITE.SUPPLIER','flatcall.php?THISPROG=WRITE.SUPPLIER','Supplier'],
  ['WRITE.PROSPECT','flatcall.php?THISPROG=WRITE.PROSPECT','Prospect'],
  ['WEBINPUT.PGROUP','flatcall.php?THISPROG=WEBINPUT.PGROUP','Product Group Manager'],
  ['WEBINPUT.PGSECTOR ','flatcall.php?THISPROG=WEBINPUT.PGSECTOR','Product Sector Manager']
 ],
 'Enquiries':[
  ['GET.SALES','sales.php','Company Sales'],
  ['GET.CUSTOMERSALES','customersales.php','Customer Sales'],
  ['GET.OPERATORSALES','operatorsales.php','Operator Sales'],
  ['GET.REPSALES','repsales.php','Rep Sales'],
  ['GET.PGRANGE','pgrange.php','Product Range Sales'],
  ['GET.PGROUP','pgroup.php','Product Group Sales'],
  ['GET.QCOLLECTION','qcollection.php','Quarterly Collection'],
  ['SHOW.TB','showtb.php?THISPROG=SHOW.TB','Show TB'],
  ['WRITE.PLEDGER','inputpledger.php','Supplier Invoice'],
  ['SHOW.PROSPECT','showprospect.php','Show prospect tree'],
  ['SHOW.PROSPECTTABLE','showprospecttable.php','Show prospect table'],
  ['ENG.AUDIT.DISCOUNT','showdiscountaudit.php','Show discount audit'],
  ['HTMLSTATEMENT','showstatement.php?THISPROG=HTMLSTATEMENT','Customer Statement'],
  ['SHOW.EDI.ERRORS','flatcallnoselect.php?THISPROG=SHOW.EDI.ERRORS','Show EDI Errors'],
  ['SHOW.PLEDGER.QUEUE','flatcallnoselect.php?THISPROG=SHOW.PLEDGER.QUEUE','Show what is currently in the purchase invoice queue']
 ]
}

var dv = null ;

function localdodefault(){
 menulist = sessionStorage.u.split('\x14')[3];
 for (id in menu) {
  var fs = document.createElement('fieldset');
  fs.id = id;
  fs.setAttribute('class','w3-round-xlarge');
  var fsn = document.createElement('legend');
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
   if (menulist && menulist.indexOf(menu[id][i][0]) != -1) {
   var a = document.createElement("a");
   a.setAttribute('class','w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round');
   a.setAttribute('href',menu[id][i][1]);
   a.innerHTML = menu[id][i][2];
   a.name = menu[id][i][0];
   a.ondragstart = function() {dv = this;};
   fs.appendChild(a);
   }
  }
  document.getElementById('middles').appendChild(fs);
 }
}

</script>
<h1 id="thisprog"></h1>
<div id='middles' class='w3-green'>
</div>
<div></div>
<div id='middles' class='w3-green'>
<a class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href='uploadfile.php'>Upload A File</a>
<a class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href='downloads'>Download an exported File</a>
</div>
<div class='w3-green'>
 <a class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href='SALES.HTML'>Sales Totals</a>
 <a class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href='EMAILFAILS.HTM'>Fax+Email Fails</a>
 <a class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href='apsplit.php'>Purchase Invoices Need Splitting</a>
 <a class='w3-button w3-border w3-card-4 w3-margin-top w3-margin-left w3-light-gray w3-round' href='apclassify.php'>Purchase Invoice Check</a>
 
</div>
</body>
</html>