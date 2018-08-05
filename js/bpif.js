function doonload() { // Initial load
  if (sessionStorage.u)  {  
  var cachedresponse = 'CACHE\x14'+sessionStorage.u;
  whoamiresponse(cachedresponse.split("\x14"));
 } else {
  callserver('Whoami',whoamiresponse); // Checks cookie only
 }
}
//TODO. Review whether we should ever add events to individual lines. They always exist inside a table.

function whoamiresponse(response) {
 if (response == 'Connection refused') {
  alert(response);
  return false;
 } else if (response[0] == 'NOTLOGGEDON') {
  document.getElementById('signin').style.visibility = 'visible';
  document.getElementById('usermenu').style.visibility = 'hidden';
  document.getElementById('username').style.visibility = 'hidden';
 } else {
  document.getElementById('username').innerText = response[3];
  document.getElementById('username').style.visibility = 'visible';
  document.getElementById('signin').style.visibility = 'hidden';
  storeuser(response);
 }
 window.localdodefault ? localdodefault(response) : dodefault(response);
}

function dodefault() { // After validating user. Can override with localdodefault
 if (sessionStorage.u) callserver(thisprog+'\x14LIST',listresponse);
}

function listresponse(response) {
 storecachetable(response);
 var o = '<h2>'+thisprog.split('.')[1]+'</h2>';
 o += '<div class="w3-row"><div class="w3-col m10">Choose From List <input name='+thisprog+' id=chooser>';
 var cs = "callserver(thisprog+'\x14NEW\x14',showresponse)";
 o += '<button accesskey=N onclick='+cs+'><u>N</u>ew Item</button></div></div>';
 o += '<div id=SELECTDIV></div>';
 document.getElementById('middles').innerHTML = o;
 document.getElementById('chooser').focus();
 attachevents();
}

function storecachetable(r) {
 r[4] ? tablename = r[4] : tablename = r[2];
 var dt = new Date();
 var li = encodeURI(r[3]);
 // Note. This is good for circa 100,000 records, browser dependent.
 // Options : Compression could increase this to 1,000,000.
 //         : Custom search deferred to the server for certain tables only
 //         : Use the browser's inbuilt database.
 try {
  localStorage.setItem(tablename, li);
  localStorage.setItem(tablename + '_age', dt.getTime());
 } catch (e) {
  console.log("Failed to store " + tablename);
  console.log(e);
 }
}

function storeuser(response) {
 if (response[0] == 'SETCOOKIE') {
  document.cookie = '_u='+response[1] +';expires=Fri, 31 Dec 9999 23:59:59 GMT;path=/' ;
  sessionStorage.u  = response[1]+"\x14"+response[2]+"\x14"+response[3]+'\x14'+response[4]; // Session ID. Customer ID. Customer Name. Menu
 }
 if (response[0] == 'SETSESSION') {
  sessionStorage.u  = response[1]+"\x14"+response[2]+"\x14"+response[3]+'\x14'+response[4]; // Session ID. Customer ID. Customer Name. Menu
 }
}

function callserver(sub,callback) {
 var x = new XMLHttpRequest();
 x.open("POST", 'ajaxsocket.php', true);
 x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 x.onreadystatechange = function () {
  if (x.readyState == 4 && x.status == 200) {
   var response = x.response.split("\x14");   
   if (callback) callback(response);
  }
 }
 if (sessionStorage.u) {
  var ssu = sessionStorage.u.split("\x14")[0] ;
 } else {
  var ssu = '';
 }
// var url = 'call='+encodeURIComponent(sub)+'&session='+ssu+'&rnd='+Math.random() ;
 sub = sub.replace('&','%26');
 var url = 'session='+ssu+'&call='+sub;
 x.send(url);
}

function topick(ld,level) {
 for (var i = 0;i<ld.length;i++) ld[i].constructor === Array ? ld[i] = topick(ld[i],level+1) : null ;
 return ld.join(String.fromCharCode(20-level));
}

function disableinputs() {
 var l = document.body.getElementsByTagName("INPUT");
 for (i in l) l[i].disabled = 'disabled';
 var l = document.body.getElementsByTagName("TEXTAREA");
 for (i in l) l[i].disabled = 'disabled';
}

function sendinputs(callback) {
 var con = '';

 var l = document.body.getElementsByTagName("input");
 for (var i = 0;i<l.length;i++) {
  if (l[i].id.substring(0,5) == 'orig_') {
   if (l[i].type == 'checkbox') {l[i].checked ? v = 'Y' : v = 'N';} else {v = l[i].value;}
   bits = l[i].id.split('_').slice(1);
   q = parseInt(bits[0])-(con.match(/\x14/g)||[]).length-1;
   if (q>0) con += Array(q+1).join('\x14');
   con = con.split('\x14');
   if (bits[1] && bits[0] == parseInt(bits[0]) && bits[1]>0) {
    sv = con[bits[0]-1];
    q = bits[1]-(sv.match(/\x13/g)||[]).length-1;
    if (q>0) sv += Array(q+1).join('\x13');
    sv = sv.split('\x13');
    sv[bits[1]-1] = v;
    sv = sv.join('\x13');
    con[bits[0]-1] = sv;
   } else {
    con[bits[0]-1] = v;
   }
   con = con.join('\x14');
  }
 }

 var l = document.body.getElementsByTagName("*");
 for (var i = 0;i<l.length;i++) {
  if (l[i].id.substring(0,2) == 'f_') {
   if (l[i].type == 'checkbox') {l[i].checked ? v = 'Y' : v = 'N';} else {v = l[i].value;}
   bits = l[i].id.split('_').slice(1);
   q = parseInt(bits[0])-(con.match(/\x14/g)||[]).length-1;
   if (q>0) con += Array(q+1).join('\x14');
   con = con.split('\x14');
   if (bits[1] && bits[0] == parseInt(bits[0]) && bits[1]>0) {
    sv = con[bits[0]-1];
    q = bits[1]-(sv.match(/\x13/g)||[]).length-1;
    if (q>0) sv += Array(q+1).join('\x13');
    sv = sv.split('\x13');
    sv[bits[1]-1] = v;
    sv = sv.join('\x13');
    con[bits[0]-1] = sv;
   } else {
    con[bits[0]-1] = v;
   }
   con = con.join('\x14');
  }
 }


 id =  document.getElementById('f_0')? document.getElementById('f_0').value : '';

 var s = thisprog+'\x14'+event.target.value+'\x14'+id+'\x14'+con;
 callserver(s,callback);
}

function showcon(con) {
 var a = con.replace(/\x14/g,']');
 a = a.replace(/\x13/g,'\\');
 console.log(a);
}

function menudrop() {
 var x = document.getElementById("Demo");
 x.className.indexOf("w3-show") == -1 ? x.className += " w3-show" :  x.className = x.className.replace(" w3-show", "");
}

function filtertable(searchfor,searchtable,oc,targetid,page) {
 if (!page) var page = 0;
 var o  = document.getElementById('SELECTLIST');
 if (!o) {
  var o = document.createElement('table');
  o.id               = 'SELECTLIST';
  o.style.position   = 'absolute';
 }
 o.innerHTML = '';
 var words = searchfor.toUpperCase().split(' ');
 showcount = 0;
 keepcount = 0;
 gotid     = false;
 for (var i=0;i<searchtable[0].length;i++) {
  var keep  = true ;
  var srch  = searchtable[0][i].toUpperCase() + ' ';
  for (cnt=1;cnt<searchtable.length;cnt++) {
   if (srv = searchtable[cnt][i]) {
    srch = srch + srv.toUpperCase() ;
   }
  }
  if (words[0].substring(0,1) == '+') {
   if (words[0].substring(1,30) == searchtable[0][i]) {
    gotid = true ; keep = true;
   } else {
    keep = false;
   }
  } else {
    for (var j = 0;j<words.length;j++) {
     var word  = words[j];
     if (srch.indexOf	(word) == -1) {keep = false; break;}
    }
  }


  if (keep) {
   keepcount +=1 ;
   if (keepcount>page*6) {
    showcount += 1;
    if (showcount > 6) break;
    var c       = document.createElement('tr');
    var tds     = ''
    if (searchtable.length>1) {
     for (cnt=0;cnt<searchtable.length;cnt++) {tds = tds+ '<td>'+searchtable[cnt][i] + '</td>';}
    } else {
     tds = tds +'<td colspan=2>'+searchtable[0][i]+'</td>';
    }
    c.innerHTML = tds;
    c.name      = searchtable[0][i];
    c.setAttribute('class','w3-black');
    if (showcount == 1) {
     c.id = 'selected';
     c.setAttribute('class','w3-green');
    }
    if (oc && targetid) c.onclick = function() {window[oc](targetid)};
    c.onmouseover = function() {
     var s = document.getElementById('selected');
     if (s != this) {s.id = '';s.setAttribute('class','w3-black');}
     this.setAttribute('class','w3-green');
     this.id = 'selected';
    }
    o.appendChild(c);
    if (gotid) break;
   }
  }

 }

 if (page > 0 || showcount > 6) {
  var tr = document.createElement('tr');
  var td1 = document.createElement('td');
  var td2 = document.createElement('td');
  if (page > 0) {
   td1.innerText = "Previous Page";
   td1.id = 'prevpage';
   td1.onclick  = function() {
    document.getElementById(targetid).focus();
    filtertable(searchfor,searchtable,oc,targetid,page-1);
   }
  }
  if (showcount > 6) {
   td2.innerText = "Next Page";
   td2.id = 'nextpage';
   td2.onclick  = function() {
    document.getElementById(targetid).focus();
    filtertable(searchfor,searchtable,oc,targetid,page+1);
   }
  }
  tr.appendChild(td1);
  tr.appendChild(td2);
  tr.setAttribute('class','w3-black');
  o.appendChild(tr);
 }
 
 return o;
}

function checkfree() {
 
 // Use only on forms to see if ID already exists where user is allowed to enter the ID
 var v = event.target.value;
 var tablename = event.target.name ;
 var w = decodeURI(localStorage.getItem(tablename));
 if (w.split('\x13')[0].split('\x12').indexOf(v) >= 0) {
  document.getElementById('subbutt').disabled = true;
  event.target.nextSibling.innerHTML = 'ID already in use';
 } else {
  document.getElementById('subbutt').disabled = false;
  event.target.nextSibling.innerHTML = '';
 }
}
//TODO Attach name fields to all checkfree classes
//Remove remaining onblur events embedded with html presentation into the js wrapper

function showresponse(response) {
 document.getElementById('middles').innerHTML = response[3];
 attachevents() ;
}

function attachevents() {
 if (document.getElementById('editable')) {
  markdates();
  markcurrency();
  marksearch();
  markfocus();
  markaddress();
  marklineevents();
  markcheckfree();
  getsupporttables() ;
 } else if (d = document.getElementById('chooser')) {
  d.onkeydown = function() {keydsearch();}
  d.onkeyup   = function() {keyupsearch(d.name,'clickdone');}
  getsupporttables() ; 
 } else {
  disableinputs();
 }
 if(window.localdoafterload) localdoafterload() ;
}

function getsupporttables() {
 var d = document.getElementsByClassName('search');
 for (var i = 0;i<d.length;i++) {
  var tablename = d[i].getAttribute('name');
  var dt = new Date();
  var age = localStorage.getItem(tablename+'_age');
  if (age == null || dt.getTime() - age > 1000) {
   callserver(tablename+'\x14LIST',storecachetable);
  }
 } 
}

function markaddress() {
 var d = document.getElementsByClassName('address');
 for (var i = 0;i<d.length;i++) {
  d[i].onblur = function() {
   var lns = this.value.split('\n');
   if (lns.length > 6) {
    this.nextSibling.innerHTML = 'Too many lines';
   } else {
    this.nextSibling.innerHTML = '';
    // And poss some kind of a google address correction
   }
  }
 }
}

function markcheckfree() {
 var d = document.getElementsByClassName('checkfree');
 for (var i = 0;i<d.length;i++) {
  d[i].onblur = checkfree;
 }
}

function markfocus() {
 var d = document.getElementsByTagName('input');
 donefocus = false;
 for (var i = 0;i<d.length;i++) {
  if (d[i].type == 'text' && !d[i].disabled) {
   if (!donefocus) {d[i].focus();donefocus=true;}
   if (d[i].nextSibling && d[i].nextSibling.nextSibling && d[i].nextSibling.nextSibling.innerText != '') { 
    d[i].focus(); d[i].select();break;
   }
  }
 }
}

function markdates() {
 // Attach date dropdown for date class
 var d = document.getElementsByClassName('date');
 for (var i=0;i<d.length;i++) {
  if (!d[i].onblur) {
  var bits = d[i].id.split('_'); 
  d[i].onblur = checkdate;
  var ne = document.createElement('span');
  ne.id = 'df_'+d[i].id.substring(2,4);
  ne.innerHTML = '[+]';
  ne.className = 'w3-tag';
  ne.onclick = function() {
   var bits = this.id;
   bits = bits.split('_');
   showChooser(this, 'f_'+bits[1], 'dtbox_'+bits[1], 2006, 2020, 'd/m/Y', false);
  }
  d[i].parentNode.insertBefore(ne,d[i].nextSibling);
  var nd = document.createElement('span');
  nd.id = 'dtbox_'+d[i].id.substring(2,4);
  nd.style.fontSize = '10px';
  d[i].parentNode.insertBefore(nd,ne.nextSibling);  
 }
 }
}

function markcurrency() {
 // Attach calculator if class is currency. We can consider a pop-up later
 var d = document.getElementsByClassName('currency');
 for (var i = 0;i<d.length;i++) {
  d[i].onkeyup = function() {   
   if ((event.keyCode === 187 && !event.shiftKey) || (event.keyCode === 13 && event.location === 3 && event.shiftKey) ) {
    event.preventDefault();
    var v = event.target.value ;
    if (v.endsWith('=')) v = v.substring(0,v.length-1);
    event.target.value = Math.floor(eval(v)*100)/100;
   }
  }
  d[i].onblur = function() {this.value = (this.value*1).toFixed(2);}
 }
}

function marksearch() {
 // Attach search for search class
 var d = document.getElementsByClassName('search');
 for (var i = 0;i<d.length;i++) {
  d[i].onkeydown = function() {keydsearch();} // Tab or CR when there is a select list
  d[i].onkeyup   = function() {keyupsearch(d.name,'clickdone');}
 }
}

function marklineevents() {
 var d = document.getElementById('lines') ;
 if (d) {
  d.onkeydown=function() {linekeydown();}
  d.onkeyup=function() {linekeyup();}
  d.onchange=function() {linechange();}
  document.getElementById('f_1').focus();
 }
}

function linekeyup() {
 var ci = event.target.parentNode.cellIndex ;
 var cl = document.getElementById('lines').rows[0].cells[ci];
 var cn = cl.className.split(' ');
 if (cn.indexOf('search')>=0) {
  var tablename = cl.getAttribute('name');
  keyupsearch(tablename,'clickdone');
 } else if (cn.indexOf('currency')>=0) {
  var v = event.target.value;
  if (v.endsWith('=')) {
   v = v.substring(0,v.length-1);
   event.target.value = eval(v).toFixed(2);
  }

 } else if (cn.indexOf('prodsearch')>=0) {
  var tablename = cl.getAttribute('name');
  keyupsearch(tablename,'prodfilclick');  
 }
}

function linechange() {
 var ci = event.target.parentNode.cellIndex ;
 var cl = document.getElementById('lines').rows[0].cells[ci];
 var ids = event.target.id.split('_');
 var cn = cl.className.split(' ');
 if (cn.indexOf('currency') >=0) {
  var v = event.target.value*1;
  event.target.value = v.toFixed(2);
 }
 if (cn.indexOf('prodline') >=0 && ids[1] == '9') {
  document.getElementById('f_10_'+ids[2]).value = (event.target.value*document.getElementById('f_uprice_'+ids[2]).value).toFixed(2);
 } else if (cn.indexOf('prodline') >=0 && ids[1] == 'uprice') {
  document.getElementById('f_10_'+ids[2]).value = (event.target.value*document.getElementById('f_9_'+ids[2]).value).toFixed(2);
 } else if (cn.indexOf('prodline') >=0 && ids[1] == '10') {
  document.getElementById('f_uprice_'+ids[2]).value = (event.target.value/document.getElementById('f_9_'+ids[2]).value).toFixed(4);
 }
 calctotals();
}

function calctotals() {
 var el = document.getElementById('total');
 var tb = document.getElementById('lines');
 var d = document.getElementsByClassName('totsrc'); // This is a column header
 if (el && tb && d) {
  var ci = d[0].cellIndex ;
  var t = 0;
  for (var r =1; r<tb.rows.length;r++) {  
   var n = tb.rows[r].cells[ci].firstChild.value;
   if (!isNaN(parseFloat(n)) && isFinite(n)) t = t + n*1;
  }
  el.value = t.toFixed(2);
 }
}

function keydsearch() {
 if (event.keyCode == 9 || event.keyCode==13) {
  if (s = document.getElementById('selected')) s.click();
 }
}

function keyupsearch(searchtablename,whenclicked) {
 var k = event.keyCode;
 if (event.target.value == '' && event.target.id != 'chooser') {removesel();return;}
 if (document.getElementById('nextpage') && (k == 39 || k == 34)) { // Right
  document.getElementById('nextpage').click();
 } else if (document.getElementById('prevpage') && (k == 37 || k == 33)) { // Left
  document.getElementById('prevpage').click();
 } else if (document.getElementById('SELECTLIST') && k == 38) { // Up
  event.preventDefault();
  event.stopPropagation();
  var s = document.getElementById('selected');
  if (s) {
   if (s.previousSibling) {
    s.id = '';
    s.setAttribute('class','w3-black');
    s.previousSibling.setAttribute('class','w3-green');
    s.previousSibling.id = 'selected';
   }
  }
  return;
 } else if (document.getElementById('SELECTLIST') && k == 40) { // Down
  event.preventDefault();
  event.stopPropagation();
  var s = document.getElementById('selected');
  if (s) {
   if (s.nextSibling) {
    s.id = '';
    s.setAttribute('class','w3-black');
    s.nextSibling.setAttribute('class','w3-green');
    s.nextSibling.id = 'selected';
   }
  }
  return;
 } else if (k == 27) { // Escape
  removesel();
 } else if (k > 48 || k == 32 || k == 8) {

  if (!searchtablename) searchtablename = event.target.name;
  var searchfor    = event.target.value;
  var searchtable  = decodeURI(localStorage.getItem(searchtablename));
  searchtable      = searchtable.split('\x13');
  searchtable[0]   = searchtable[0].split('\x12');
//  searchtable[1] ? searchtable[1] = searchtable[1].split('\x12') : searchtable[1] = searchtable[0];
  if (searchtable[1]) searchtable[1] = searchtable[1].split('\x12');

  if (!searchtable) {
   //TODO. Handle missing searchtable here
  } else if (event.target.id == 'chooser') {
   for (i=2;i<searchtable.length;i++) {
    searchtable[i] = searchtable[i].split('\x12') ;
   }
   var o = filtertable(searchfor,searchtable,"chooserclick",event.target.id);
   if (o) {
    o.className = 'w3-ul w3-bordered w3-table';
    document.getElementById('SELECTDIV').innerHTML = '';
    document.getElementById('SELECTDIV').appendChild(o);
   }
  } else {  
   var o = filtertable(searchfor,searchtable,whenclicked,event.target.id);
   if (o) {
    o.className = 'w3-ul w3-bordered w3-table w3-small';
    o.style.width   = '400px';
    o.style.border  = '1px solid white';
    var xy          = pos(event.target);
    xy.y            = xy.y+event.target.offsetHeight;
    o.style.top     = (xy.y+2) + 'px';
    o.style.left    = (xy.x+2) + 'px';
    o.style.zIndex  = 10;
    document.body.appendChild(o); // Need to put on page first so we can work out size
    if (xy.y + o.offsetHeight > 600) {
     xy.y          = 600-o.offsetHeight;
     xy.x          = event.target.offsetWidth+xy.x;
     o.style.top   = (xy.y+2) + 'px';
     o.style.left  = (xy.x+2) + 'px';
     document.body.appendChild(o);
    }
   }
  }
 }
}

function chooserclick(targetid) {
 var id = document.getElementById('selected').name ;
 callserver(thisprog+'\x14READ\x14'+id,showresponse);
}

function clickdone(targetid) {
 document.getElementById(targetid).value = document.getElementById('selected').name;
 if (document.getElementById(targetid).nextSibling && document.getElementById('selected').childNodes[1]) {
  document.getElementById(targetid).nextSibling.innerHTML = document.getElementById('selected').childNodes[1].innerText;
 }
 document.getElementById(targetid).focus();
 removesel();
}

function pos(el) {
  for (var lx = 0, ly = 0;
  el != null;
  lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
  return {x: lx,y: ly};
}

function getlocallist(src,listid) {
 var id = document.getElementById(src).value;
 callserver(thisprog+"\x14"+listid+"\x14"+id,storecachetable);
}

function addl(rn) {
 var linesid = event.target.parentNode.parentNode.parentNode.parentNode.id;
 // We can hide columns with #foo.hide2 tr > *:nth-child(2) { display: none;}
 if (!rn) rn = event.target.parentNode.parentNode.rowIndex+1;
 var t  = document.getElementById(linesid);
 if (rn == -1) rn = t.rows.length;
 var nr = t.insertRow(rn); 
 var firstid = null;
 for (var c =0; c < t.rows[1].cells.length; c++) {
  var nc   = nr.insertCell(c);
  if (!t.rows[1].cells[c].firstChild) {
  } else if (c == 0) {
   if (rn == 1) {
    nc.innerHTML = '<button tabindex=-1 onclick=addl()>+</button>';
   } else {
    nc.innerHTML = '<button tabindex=-1 onclick=addl()>+</button><button tabindex=-1 onclick=deletel()>-</button>';
   }
  } else {
   var bits = t.rows[1].cells[c].firstChild.id.split('_');

   var v = '';
   if (bits[0] == 'f' && bits.length == 3) {
    if (!firstid) firstid = bits[1];
    for (var i=t.rows.length-2;i>=rn;i--) {
     document.getElementById('f_'+bits[1]+'_'+i).id = 'f_'+bits[1]+'_'+(i+1)*1;
    }
    v = document.getElementById('spread_'+bits[1]);
    v ? v = v.value :  v = '';
   }
   
   var x = t.rows[1].cells[c].innerHTML;

   x = x.replace(/value=".*"/,'value="'+v+'"');
   x = x.replace(/<em>.*<\/em>/,'<em><\/em>');
   x = x.replace(/<i>.*<\/i>/,'<em><\/em>');

   nc.innerHTML = x.replace('f_'+bits[1]+'_1','f_'+bits[1]+'_'+rn);
   
  }

 }
 return firstid;
}

function deletel(){
 var linesid = event.target.parentNode.parentNode.parentNode.parentNode.id;
 var rn = event.target.parentNode.parentNode.rowIndex;
 var t  = document.getElementById(linesid);
 var fn = t.rows[1].cells[1].firstChild.id;
 if (fn) {
  t.deleteRow(rn);
  for (r=1;r<t.rows.length;r++) {
   var tr = t.rows[r];
   for (c=0;c<tr.cells.length;c++) {
    if (tr.cells[c].firstChild) {
    var rid = tr.cells[c].firstChild.id ;
    if (rid) {
     var bits = rid.split('_');
     if (bits.length ==3 && bits[0] == 'f') {
      t.rows[r].cells[c].firstChild.id = 'f_'+bits[1]+'_'+r;
     }
    }
    }
   }
  }
 }
}

function linekeydown() {
 // On lines
 var k     = event.keyCode;
 var t     = event.target.id;
 var bits  = t.split('_');
 var newid = null;
 if (document.getElementById('SELECTLIST')) {
  keydsearch();
 } else if (k == 40) {
  moveto('f_'+bits[1]+'_'+(bits[2]*1+1));
 } else if (k == 38) {
  moveto('f_'+bits[1]+'_'+(bits[2]*1-1));
 } else if (k == 13) {
  newid = 'f_'+bits[1]+'_'+(bits[2]*1+1);
  if (!document.getElementById(newid)) newid = 'f_'+addl(-1)+'_'+(bits[2]*1+1);
  moveto(newid);
 } else {
  var ci = event.target.parentNode.cellIndex ;
  var cl = document.getElementById('lines').rows[0].cells[ci];
 }

}

function moveto(newid) {
  var d = document.getElementById(newid) ;
  if (d) {
   d.focus();
   d.select();
   event.stopPropagation();
   event.preventDefault();
  }
}

function showaccordion(id) {
 var x = document.getElementById(id);
 x.style.display != 'none' ? x.style.display = 'none' : x.style.display = '';
}

function checkdate() {
 var dt = event.target.value;
 if (dt = dateparse(dt)) {
  var bits = dt.substring(0, 10).split(/-/);
  event.target.value = bits[2] + '/' + bits[1] + '/' + bits[0];
 }
}

function dateparse(dt) {
  // Date input by user. Returns ISO date
  var bits = dt.split(/[.,\/ -]/);
  if (bits.length == 2)  bits[2] = new Date().getFullYear();
  if (bits[2] < 100)  bits[2] = bits[2] * 1 + 2000;
  var d = new Date(bits[2] * 1, bits[1] * 1 - 1, bits[0] * 1);
  var dn = new Date();
  if (d > dn - 1000 * 60 * 60 * 24 * 700 && dn > d - 1000 * 60 * 60 * 24 * 700) {
    var ans = d.getFullYear() + '-' + (d.getMonth() < 9 ? '0' + (d.getMonth() + 1) : (d.getMonth() + 1)) + '-' + (d.getDate() < 10 ? '0' + d.getDate() : d.getDate());
    return ans;
  } else {
    return false;
  }
}

function removesel() {
 var o  = document.getElementById('SELECTLIST');
 if (o) o.parentNode.removeChild(o);
}

function parserequest() {
  var rs = window.location.search.substring(1);
  if (rs) rs = JSON.parse('{"' + decodeURI(rs).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');
  return rs;
}

function prodfil(resp) {
 var replyto = resp[4];
 var d = document.getElementById(replyto);
 var proditem = resp[3].split('\x13');
 var oldq = d.parentNode.parentNode.childNodes[3].childNodes[0].value;
 if (isNaN(parseFloat(oldq)) || !isFinite(oldq)) {
  oldq = 1;
  d.parentNode.parentNode.childNodes[3].childNodes[0].value = '1';
 }
 d.parentNode.parentNode.childNodes[2].childNodes[0].value = proditem[0]; // Description
 d.parentNode.parentNode.childNodes[4].childNodes[0].value = (proditem[12]/10000).toFixed(2); // Unit Value
 d.parentNode.parentNode.childNodes[5].childNodes[0].value = (proditem[12]/10000*oldq).toFixed(2); // Line Value
 calctotals();
}

function prodfilclick(targetid) {
 var id = document.getElementById('selected').name ;
 callserver('WEBINPUT.PRODUCT\x14ITEM\x14'+id+'\x14'+targetid,prodfil);
 document.getElementById(targetid).value = id;
 document.getElementById(targetid).focus();
 removesel();
}

function dopdf(response) {
 var e = document.createElement('a');
 console.log(response[3]);
 e.setAttribute('href', response[3]);

 e.style.display = 'none';
 document.body.appendChild(e);
 e.click();
 document.body.removeChild(e);
}

function reversecheck() {
 var d = document.querySelectorAll('input[type="checkbox"]');
 for (var i in d) d[i].checked = event.target.checked ;
}

function dodownload(response) {
 var e = document.createElement('a');
 e.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(response[3]));
 var fn = new Date().getTime();
 e.setAttribute('download', 'f' + fn + '. csv');
 e.style.display = 'none';
 document.body.appendChild(e);
 e.click();
 document.body.removeChild(e);
 var el = document.getElementById('bacsdownload');
 var pn = el.parentNode;
 pn.removeChild(el);
 pn.innerHTML = "A file has been added to your downloads folder named  f"+fn+". csv";
}

function toiso(t) {
 // Converts almost any date into an ISO short date yyyy-mm-dd
 var mths = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
 var months = ['january','february','march','april','may','june','july','august','september','october','november','december'];
 for (var i in mths) {
  var re = new RegExp(months[i],'i');
  t = t.replace(re,'M'+(i*1+1));
  re = new RegExp(mths[i],'i');
  t = t.replace(re,'M'+(i*1+1));
 }
 t = t.replace(/[^\d|M]/g,' ');
 t = t.replace(/ +/g,' ');
 t = t.split(' ');
 if (t.length !=3) return '0000-00-00';
 if (t[0] == '' || t[1] == '' || t[2] == '') return '0000-00-00';
 if (t[0][0] == 'M') {
  var d = t[1], m=t[0].substring(1), y=t[2];
 } else if (t[1][0] == 'M') {
  var d = t[0], m=t[1].substring(1), y=t[2];
 } else {
  var d = t[0], m=t[1], y=t[2];
 }
 if ((y*1)<2000) y = 2000+y*1;
 var ans = y+'-'+pad(m,2)+'-'+pad(d,2) ;
 return ans ;
}

function isadate(t) {
 var datediff = Math.abs(Date.parse(toiso(t))-(new Date()*1))/(86400*1000) ;
 if (isNaN(datediff)) return false;
 return datediff;
}

function pad(a,b){return(1e15+a+"").slice(-b)}