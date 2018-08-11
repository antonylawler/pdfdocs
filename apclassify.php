<!DOCTYPE html>
<link rel="stylesheet" href="css/w3.css">
<link rel="stylesheet" href="css/local.css">
<script src="js/bpif.js"></script>
<style>
 .h12{height:12px;}
 h1 {color:white;}
</style>
<?php
 error_reporting(E_ALL);
ini_set('display_errors', 1);
  $dblink = new mysqli("127.0.0.1","root","password","docs");
 $stmt     = "select * from apdocs where posted is null order by itemid desc limit 10";
 $wordset  = array();
 $result   = mysqli_query($dblink,$stmt);
 $docs = array();
 while($row = mysqli_fetch_row($result)) {$docs[]=$row;}
// while($row = mysqli_fetch_row($result)) {$row[23] = mb_convert_encoding($row[23],'UTF-8'); $docs[]=$row;}
 mysqli_close($dblink);
?>
<head>
 <title>Classifier</title>
</head>
<body style='background:black'>
<input id=editable type=hidden>
<div id=features style='float:left' class='w3-small w3-black'>
 <table style='width:100%;border-collapse: collapse;'>
 <tr><th width='180px'></th><th width='300px'></th><th></th></tr>
 <tr><td>Class Review</td><td id=recid></td></tr>
 <tr style='height:30px;'>
  <td>Document Type</td>
  <td>
   <input id='rc_I' type="radio" name="f_11" value="I" onclick='invoiceselected()'><label>Invoice</label><br>
   <input id='rc_C' type="radio" name="f_11" value="C" onclick='invoiceselected()'><label>Credit</label><br>
   <input id='rc_S' type="radio" name="f_11" value="S" onclick='statementselected()'><label>Statement</label><br>
   <input id='rc_O' type="radio" name="f_11" value="O" onclick='otherselected()'><label>Other</label><br>
  </td>
  <td rowspan=15>
   <div id=divid style='float:left;overflow:hidden' onmousewheel='dozoomin(event)' onmousedown='md(event)' onmouseup='mu(event)' onmousemove='mm(event)' onmouseout='mo(event)' ondragstart='return false;'>
   </div>
  </td>
 </tr>
 <tr class='suppline'><td class=h12>Supplier</td><td><input class='search' name='WEBINPUT.SUPPLIER' id='f_12'><span style='font-size:8px'></span></td></tr>
 <tr class='featline'><td class=h12 onclick=addnext()>Invoice No</td><td><input onfocus='currbox = this;' id='f_15' type=input><span></span></td></tr>
 <tr class='featline'><td class=h12 onclick=addnext()>Purchase Order</td><td><input onfocus='currbox = this;' id='f_16' type=input><span></span></td></tr>
 <tr class='featline'><td class=h12 onclick=addnext()>Tax Date</td><td><input onfocus='currbox = this;' id='f_17' type=input><span></span></td></tr>
 <tr class='featline'><td class=h12 onclick=addnext()>Goods</td><td><input style='text-align:right' onfocus='currbox = this;' id='f_18' type=input><span></span></td></tr>
 <tr class='featline'><td class=h12 onclick=addnext()>VAT</td><td><input  style='text-align:right' onfocus='currbox = this;' id='f_19' type=input><span></span></td></tr>
 <tr class='featline'><td class=h12 onclick=addnext()>Total</td><td><input  style='text-align:right' onfocus='currbox = this;' id='f_20' type=input><span></span></td></tr>
 <tr><td class=h12>Ignore Errors</td><td><input type='checkbox' id=ignore></td></tr>
 <tr><td class=h12>Pages</td><td id=pagelist></td></tr>
 <tr><td valign='top'><button  valign='top' accesskey='S' onclick='dowrite()'>Done</button><button onclick=showpdf()>Show PDF</td></tr>
 </table>
</div>
</body>
<script>
function showpdf() {
 window.open('pdfdocs/'+docs[currentdoc][6]);
}
function invoiceselected() {
 elements = document.getElementsByClassName('suppline');
 for (el=0;el<elements.length;el++) elements[el].style.visibility = 'visible';
 elements = document.getElementsByClassName('featline');
 for (el=0;el<elements.length;el++) elements[el].style.visibility = 'visible';
}
function statementselected() {
 elements = document.getElementsByClassName('suppline');
 for (el=0;el<elements.length;el++) elements[el].style.visibility = 'visible';
 elements = document.getElementsByClassName('featline');
 for (el=0;el<elements.length;el++) elements[el].style.visibility = 'hidden';
}
function otherselected() {
 elements = document.getElementsByClassName('suppline');
 for (el=0;el<elements.length;el++) elements[el].style.visibility = 'hidden';
 elements = document.getElementsByClassName('featline');
 for (el=0;el<elements.length;el++) elements[el].style.visibility = 'hidden';
}
function dozoomin(ev) {
 ev.stopPropagation();
 ims             = images['img_'+currentdoc+'_'+currentpage].style;
 oldwidth        = parseFloat(ims.width);
 delta           = ev.wheelDelta > 0 ? 1 : -1 ;
 zoomlevel       = Math.max(0,Math.min(zoomlevel+delta,20)); 
 ims.width       = dw*Math.pow(zoomby,zoomlevel)+'px'; // Increase width
 ims.height      = dw*ratio*Math.pow(zoomby,zoomlevel)+'px'; // Increase height

 sizeinc         = dw*Math.pow(zoomby,zoomlevel)-oldwidth;
 xo              = (calcmousexy(ev).mx-offset(document.getElementById('divid')).mx); // How many pixels across the image is the mouse
 yo              = (calcmousexy(ev).my-offset(document.getElementById('divid')).my); // How many pixels down the image is the mouse
 cutl            = Math.max(dw-parseFloat(ims.width),Math.min(0,parseFloat(ims.marginLeft)-sizeinc*xo/dw));
 cutt            = Math.max(dw*ratio-parseFloat(ims.height),Math.min(0,parseFloat(ims.marginTop)-sizeinc*yo/dw));
 ims.marginLeft  = cutl+'px';
 ims.marginTop   = cutt+'px';
 highlightwords();
}
function offset(el) {
 var rect = el.getBoundingClientRect(),
 scrollLeft = window.pageXOffset || document.documentElement.scrollLeft,
 scrollTop = window.pageYOffset || document.documentElement.scrollTop;
 return { my: rect.top + scrollTop, mx: rect.left + scrollLeft }
}
function calcmousexy(ev) {
 var x = 0;
 var y = 0;
 ev.pageX ? x = ev.pageX : x = ev.clientX ; x += document.documentElement.scrollLeft; 
 ev.pageY ? y = ev.pageY : y = ev.clientY ; y += document.documentElement.scrollTop;
 return {"mx":x,"my":y};
}
function md(ev) {
 setmousexy(); 
 dragging = true;
 ims = images['img_'+currentdoc+'_'+currentpage].style;
 origleft = parseFloat(ims.marginLeft)||0;
 origtop = parseFloat(ims.marginTop)||0;
}
function mu(ev) {
 dragging = false;
}
function mo(ev) {
 ev.preventDefault();
 dragging = false;
}
function mm(ev) {
 event.preventDefault();
 if (!dragging) return;
 xmove          = calcmousexy(event).mx - mousex;ymove = calcmousexy(event).my - mousey;
 ims            = images['img_'+currentdoc+'_'+currentpage].style;
 cutl           = Math.max(Math.min(0,origleft+xmove),dw-parseFloat(ims.width));
 cutt           = Math.max(Math.min(0,origtop+ymove),dw*ratio-parseFloat(ims.height));
 ims.marginLeft = cutl + 'px';
 ims.marginTop  = cutt + 'px';
 highlightwords();
}
function setmousexy() {
 event.pageX ? mousex = event.pageX : mousex = event.clientX ; //mousex += document.documentElement.scrollLeft; 
 event.pageY ? mousey = event.pageY : mousey = event.clientY ;// mousey += document.documentElement.scrollTop;
}
function addsuppliers(resp) {
 // After loading the suppliers.txt file. Not needed if we can get a list direct.
 var l = resp.split('\x0A');
 ids = [];
 descs = [];
 for (i in l) {
  b = l[i].split(',');
  ids.push(b[0]);
  descs.push(b[1]);
 }
 recs = ids.join('\x12')+'\x13'+descs.join('\x12');
 storecachetable(['','','SUPPLIERLIST',recs]);
}

function loadimages() {
 // After loading a list of suppliers
 images = {};
 resolution = 360;
 for (var i = 0; i < docs.length; i++) {
  docs[i][24] = JSON.parse(docs[i][24]);
  for (var p = 0;p<=docs[i][11]-docs[i][10];p++) {
   docs[i][24][p][0] = docs[i][24][p][0].sort(function(a,b) {return a[1]==b[1] ? a[0]-b[0] : a[1]-b[1]}) ;
   var img        = new Image();
   var uid = 'pdfdocs/images/'+docs[i][6].split('.')[0]+'-'+(p+1)+'.jpg';
   img.id = 'img_'+i+'_'+p;
   img.src = 'eng_fetchthumb.php?fname=pdfdocs/'+docs[i][6]+'&page='+(p+docs[i][10]*1-1)+'&resolution='+resolution;
   img.passerr = 'eng_fetchthumb.php?fname=pdfdocs/'+docs[i][6]+'&page='+(p+docs[i][10]-1)+'&resolution='+resolution;
   img.onerror = function() {this.src = this.passerr;};
   images[img.id] = img;
   if (img.id == 'img_0_0') {
    showimage();
    highlightwords();
   }
  }
 }
 nextdoc();
}

function listpages() {
 pl = document.getElementById('pagelist');
 pl.innerHTML = '';
 if (docs[currentdoc][10] == null) {docs[currentdoc][10] = 1; docs[currentdoc][11] = 1; }

 for (var p = docs[currentdoc][10]*1;p<=docs[currentdoc][11]*1;p++) {
  b = document.createElement('button');
  if (currentpage == p) {b.style.backgroundColor = 'white';}
  b.innerText = p;
  b.onclick=function() {
   currentpage=this.innerText*1-docs[currentdoc][10];
   showimage();
   dozoomin(event);
   listpages();   
  }
  pl.appendChild(b);
 }
}
function addnext(ev) {
 evt = event.target;
 if (evt.nextSibling.firstChild.sources) {
  var tid = evt.nextSibling.firstChild.sources[0] ;
  var tp = tid.split('_')[1];
  var tw = tid.split('_')[2];
  w = '';
  evt.nextSibling.firstChild.sources[1] += 1;
  for (i=0;i<evt.nextSibling.firstChild.sources[1];i++) {w += docs[currentdoc][24][tp][0][tw*1+i][4];}
  evt.nextSibling.firstChild.value = w;
 }
}
function ajaxcall(url,req,callback) {
 var x = new XMLHttpRequest();
 x.open("POST",url, true);
 x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 x.onreadystatechange = function () {
  if (x.readyState == 4 && x.status == 200) {
   if (callback) callback(x.response);
  }
 }
 x.send(req+'&rnd='+Math.random());
}
function showimage() {
 cutl           = 0;
 cutt           = 0;
 if (docs[currentdoc][24][currentpage][2] == 0) docs[currentdoc][24][currentpage][2] = 841;
 if (docs[currentdoc][24][currentpage][1] == 0) docs[currentdoc][24][currentpage][1] = 595;
 ratio          = docs[currentdoc][24][currentpage][2]/docs[currentdoc][24][currentpage][1];
 dw             = Math.min(window.innerWidth-500,window.innerHeight/ratio-10);
 ims            = images['img_'+currentdoc+'_'+currentpage].style;
 ims.width      = dw+'px';
 ims.height     = dw*ratio+'px';
 ims.marginLeft = '0px';
 ims.marginTop  = '0px';
 document.getElementById('divid').innerHTML = '';
 document.getElementById('divid').appendChild(images['img_'+currentdoc+'_'+currentpage]);
 document.getElementById('divid').style.width = dw+'px';
 document.getElementById('divid').style.height = dw*ratio+'px';
}


function highlightwords() {
 zl = Math.pow(zoomby,zoomlevel);
 sw = dw*zl/docs[currentdoc][24][currentpage][1];
 zcounter = 1;
 elements = document.getElementsByClassName('highlight');
 while(elements.length > 0){elements[0].parentNode.removeChild(elements[0]);}

 words = docs[currentdoc][24][currentpage][0];

 for (i=0;i<words.length;i++) {
  w = words[i];
  if (w[0]*sw+cutl<dw && w[1]*sw+cutt<dw*ratio && w[0]*sw+cutl>0) {
  currelement                  = document.createElement('div');
  currelement.style.cssText    = 'position:absolute;opacity:.2;padding:0px;margin:0px;';
  currelement.style.left       = (w[0]*sw+cutl+offset(document.getElementById('divid')).mx-3) + 'px';
  currelement.style.top        = (w[1]*sw+cutt+offset(document.getElementById('divid')).my-3) + 'px';
  var wid                      = (w[2]-w[0])*sw-1 ;
  if (wid+w[0]*sw+cutl>dw) wid = dw-w[0]*sw-cutl;
  currelement.style.width = (wid+8) + 'px';
  var hei = (w[3]-w[1])*sw ;
  if (hei+w[1]*sw+cutt > dw*ratio) hei = dw*ratio-w[1]*sw-cutt;
  currelement.style.height = (hei+8) + 'px';
  currelement.style.zIndex = ++zcounter;
  currelement.className    = 'highlight';
  currelement.id           = 'word_'+currentpage+'_'+i;
  if (w[5] != null) {
   currelement.style.background = 'red';
  } else if (w[4].match(/\d\.\d\d/)) {
   currelement.style.background = 'yellow';
  } else if (w[4].match(/\d/)) {
   currelement.style.background = 'green';
  } else {
   continue;
  }

  currelement.onclick = function() {
   tid = this.id ;
   tp = tid.split('_')[1];
   tw = tid.split('_')[2];
   ans = docs[currentdoc][24][tp*1][0][tw*1][4];
   currbox.sources = null;
   if (currbox.id == 'f_17') { // Date is a special case since it can include spaces
    for (i=1;i<3;i++) {
     if (isadate(ans)) break;
     ans += ' '+docs[currentdoc][24][tp*1][0][tw*1+i][4];
     docs[currentdoc][24][tp][0][tw*1+i][5] = currbox.id;
    }
    currbox.value = ans;
    currbox.sources = [tid,i];
   } else if (currbox.id == 'f_18' || currbox.id == 'f_19' || currbox.id == 'f_20') {
    for (i=1;i<3;i++) {
     if (iscurrency(ans)) break;
     ans += ' '+docs[currentdoc][24][tp*1][0][tw*1+i][4];
     docs[currentdoc][24][tp][0][tw*1+i][5] = currbox.id;
    }
    currbox.value = ans;
    currbox.sources = [tid,i];
   } else {
    docs[currentdoc][24][tp][0][tw][5] = currbox.id;
    currbox.value = ans;
    currbox.sources = [tid,1];
   }
   this.style.background = 'red';
 
   if (currbox.id == 'f_15') {        document.getElementById('f_16').focus();
   } else if (currbox.id == 'f_16') { document.getElementById('f_17').focus();
   } else if (currbox.id == 'f_17') { document.getElementById('f_18').focus();
   } else if (currbox.id == 'f_18') { document.getElementById('f_19').focus();
   } else if (currbox.id == 'f_19') { document.getElementById('f_20').focus();
   }
  }
   document.getElementById('divid').appendChild(currelement);
  }
 }
}

function iscurrency(v) {
 var t = v.replace(' ','');
 if (t.match(/\d{0,3},?\d{1,3}\.\d\d/)) return true;
 return false;
}

function validateall() {

 success = true;
 words = docs[currentdoc][9];
 docs[currentdoc][23] = {};
 ignore = document.getElementById('ignore').checked;

 if (document.getElementById('rc_I').checked) {docs[currentdoc][13] = 'I';
 } else if (document.getElementById('rc_C').checked)  {docs[currentdoc][13] = 'C';
 } else if (document.getElementById('rc_S').checked)  {docs[currentdoc][13] = 'S';
 } else if (document.getElementById('rc_O').checked)  {docs[currentdoc][13] = 'O';
 }
 if (document.getElementById('rc_O').checked) return success;

 f = document.getElementById('f_12') ; // Supplier ID
 if (decodeURI(localStorage.getItem('SUPPLIERLIST')).split('\x13')[0].split('\x12').indexOf(f.value) == -1 || f.value == '') { 
  f.style.background = '#ffa0a0';
  success = false;
 } else {
  f.style.background = '#ffffff';
  docs[currentdoc][12] = f.value;
 }
 if (document.getElementById('rc_S').checked) return success;

 f = document.getElementById('f_15'); // Invoice No
 evs = findall(words,f.value);
 if (evs.length == 0 && !ignore) {
  f.nextSibling.innerText = "Cannot find in document";
  success = false;
 } else {
  f.nextSibling.innerText = "";
  docs[currentdoc][15] = f.value;
 }

 f = document.getElementById('f_16'); // Purchase Order
 evs = findall(words,f.value);
 if (evs.length == 0 && !ignore) {
  f.nextSibling.innerText = "Cannot find in document";
  success = false;
 } else {
  f.nextSibling.innerText = "";
  docs[currentdoc][16] = f.value;
 }

 f = document.getElementById('f_17'); // Date
 evs = findall(words,f.value);
 if (evs.length == 0 && !ignore) {
  f.nextSibling.innerText = "Cannot find in document";
  success = false;
 } else {
  isodate = toiso(f.value);
  datediff = Math.abs(Date.parse(toiso(f.value))-(new Date()*1))/(86400*1000) ;
  if (datediff > 900 || isNaN(datediff)) {
   success = false;
   f.nextSibling.innerText = "Cannot convert to a date";
  } else {
   f.nextSibling.innerText = "";
   occurrence = 1;
   docs[currentdoc][23][f.id] = [f.value,occurrence];
   docs[currentdoc][17] = isodate;
  }
 }

 f = document.getElementById('f_18'); // Goods
 evs = findall(words,f.value);
 if (f.value == '') {
  goods = '';
 } else if (evs.length == 0 && !ignore) {
  f.nextSibling.innerText = "Cannot find in document";
  success = false;
 } else {
  goods = f.value.replace(',','').replace(' ','').match(/[\d|\.]+/);
  if (goods == null || goods.length != 1) {
   success = false;
   f.nextSibling.innerText = "Cannot convert to a currency amount";
  } else {
   f.nextSibling.innerText = "";
   occurrence = 1;
   docs[currentdoc][23][f.id] = [f.value,occurrence];
  }
 }

 f = document.getElementById('f_19'); // VAT
 evs = findall(words,f.value);
 if (evs.length == 0 && !ignore) {
  f.nextSibling.innerText = "Cannot find in document";
  success = false;
 } else {
  vat = f.value.replace(',','').replace(' ','').match(/[\d|\.]+/);
  if (vat == null || vat.length != 1) {
   success = false;
   f.nextSibling.innerText = "Cannot convert to a currency amount";
  } else {
   f.nextSibling.innerText = "";
   occurrence = 1;
   docs[currentdoc][23][f.id] = [f.value,occurrence];
  }
 }

 f = document.getElementById('f_20'); // Total
 evs = findall(words,f.value);
 if (evs.length == 0 && !ignore) {
  f.nextSibling.innerText = "Cannot find in document";
  success = false;
 } else {
  total = f.value.replace(',','').replace(' ','').match(/[\d|\.]+/);  
  if (total == null || total.length != 1) {
   success = false;
   f.nextSibling.innerText = "Cannot convert to a currency amount";
  } else {
   f.nextSibling.innerText = "";
   occurrence = 1;
   docs[currentdoc][23][f.id] = [f.value,occurrence];
  }
 }


 if (success) {
  vat   = parseInt(vat[0]*100+.5); 
  total = parseInt(total[0]*100+.5);
  if ((document.getElementById('f_19').value.indexOf('-')) >=0 ) vat = vat*-1;
  if ((document.getElementById('f_20').value.indexOf('-')) >=0 ) total = total*-1;
  if (goods == '') {
   goods = total-vat;
  } else {
   goods = parseInt(goods[0]*100+.5);
   if ((document.getElementById('f_18').value.indexOf('-')) >=0 ) goods = goods*-1;
  }
  if (Math.abs(total) < Math.abs(goods) || Math.abs(total) < Math.abs(vat)) {
   success = false;
   document.getElementById('f_20').nextSibling.innerText = 'Goods and VAT must be less than the total';
  }
  if (goods+vat==total) {
   docs[currentdoc][18] = goods;
   docs[currentdoc][19] = vat;
   docs[currentdoc][20] = total;
  } else {
   success = false;
   document.getElementById('f_20').nextSibling.innerText = 'Goods plus VAT must equal the total ';
  }
 }
 
 return success;

}

function writedone(resp) {
 console.log(resp);
}

function writeit() {
 url = 'ajaxwritedoc.php';
 req = 'call='+encodeURIComponent(JSON.stringify(docs[currentdoc]));
 ajaxcall(url,req,writedone);
}

function dowrite() {
 if (!validateall()) return;
 writeit();
 currentdoc += 1;
 nextdoc();
}
function nextdoc() { 
 if (currentdoc >= docs.length) {
  document.body.innerHTML = '<h1>No more to show</h1>';
 } else {
  currbox        = document.getElementById('f_12');
  cutl           = 0;
  cutt           = 0;
  zoomlevel      = 0;
  dragging       = false;
  zoomlevel      = 0;
  mousex         = 0;
  mousey         = 0;
  showimage();
  listpages();
  highlightwords();
  elements = document.getElementsByClassName('suppline');
  for (el=0;el<elements.length;el++) elements[el].childNodes[1].childNodes[1].innerText = '';
  elements = document.getElementsByClassName('featline');
  for (el=0;el<elements.length;el++) elements[el].childNodes[1].childNodes[1].innerText = '';
  invoiceselected();
  showvalues();
  document.getElementById('f_12').focus();
 }
}
function showvalues() {
 document.getElementById('recid').innerHTML = docs[currentdoc][0] + ' '+ docs[currentdoc][10] + ' ' + docs[currentdoc][11];
 var t = document.getElementById('rc_'+docs[currentdoc][13]);
 if (t) {t.checked = 'true';t.click()}
 document.getElementById('f_12').value = docs[currentdoc][12];
 document.getElementById('f_15').value = JSON.parse(docs[currentdoc][23])['f_15'];
 document.getElementById('f_16').value = JSON.parse(docs[currentdoc][23])['f_16'];
 document.getElementById('f_17').value = JSON.parse(docs[currentdoc][23])['f_17'];
 document.getElementById('f_18').value = JSON.parse(docs[currentdoc][23])['f_18'];
 document.getElementById('f_19').value = JSON.parse(docs[currentdoc][23])['f_19'];
 document.getElementById('f_20').value = JSON.parse(docs[currentdoc][23])['f_20'];
 var p = decodeURI(localStorage.getItem('SUPPLIERLIST')).split('\x13')[0].split('\x12').indexOf(docs[currentdoc][12]);
 var w = decodeURI(localStorage.getItem('SUPPLIERLIST')).split('\x13')[1].split('\x12')[p];
 document.getElementById('f_12').nextSibling.innerText = w;
 removesel();
}
function findall(txt,val) {
 if (val == "") return [] ;
 if (txt == "" || txt == null) return [] ;
 var pos = 0;
 var finds = [];
 while ((p = txt.indexOf(val,pos)) != -1) {pos = p+1;finds.push(p);}
 return finds;
}
currentdoc     = 0;
currentpage    = 0;
zoomby         = 1.25;
zoomlevel      = 0;
dw             = 450;
dragging       = false;
docs           = <?php echo(json_encode($docs));?>;
loadimages();
attachevents();


//ajaxcall('SUPPLIER.TXT','',addsuppliers);

/*
Need an option to skip if failed write
Need to sort out some of the colouring
Need to store the occurence level of items clicked when there is more than one.
Optionally
- Restrict which items are highlighted on a field by field basis
Recommend a common class for other. (Terms and conditions etc)
- Nicer interface
- Flow diagram of work and documentation
- Clear down of old jpgs.
*/

</script>
