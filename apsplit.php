<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<head>
 <title>Doc Splitter</title>
</head>
<body>
<div>
 <button onclick='bigger()'>Bigger</button>
 <button onclick='smaller()'>Smaller</button>
 <button onclick='sharper()'>Sharper</button>
 <button onclick='faster()'>Faster</button>
 <button id=nextbut onclick='next()'>Done</button>
 <span id=count></span>
</div>
<div id=cent style='width:100%'>
</div>
<body>
</div>
</html>
<?php
 $dblink = new mysqli("127.0.0.1","root","password","docs");
 $stmt = "select * from apdocs where pages > 1 and topage = pages and pagefrom is null order by rand() limit 10";
 $results = array();
 $result = mysqli_query($dblink,$stmt);
 if ($result) {while($row = mysqli_fetch_row($result)) $results[]=$row;}
 mysqli_close($dblink); 
?>
<script>
j = <?php echo(json_encode($results));?> ;
currpage = -1;
pagesets = null; // e.g. [1,2] on [1,2,3,4,5] results in [1] [2] [3,4,5]
// Read as "Split everything after x"
size = 300;
resolution = 100;

function showpdf(id) {
 
 obj = j[id];
 if (pagesets == null) pagesets = JSON.parse(obj[23]).ps;
 if (pagesets == null) pagesets = [];
 
 document.getElementById('count').innerHTML = 'Showing '+(id+1)+' of '+j.length+" Pages to split:"+obj[6];
 d = document.getElementById('cent');
 d.innerHTML = '';

 for (p=1;p<=obj[7];p++) {

  if (pagesets.indexOf(p-1)>-1) {
   im = document.createElement('hr');
   d.appendChild(im);
  }

  im = document.createElement('img');
  im.style.width = size+'px';
  im.src = 'eng_fetchthumb.php?fname=pdfdocs/'+obj[6]+'&page='+(p-1)+'&resolution='+resolution;
  im.id = 'p_'+id+'_'+p;
  im.onclick=clickimage
  d.appendChild(im);
 }

}
function clickimage() {
 bits = event.target.id.split('_');
 p = bits[2]*1-1;
 if (p==0) return;
 pos = pagesets.indexOf(p) ;
 if (pos > -1) {pagesets.splice(pos,1);} else {pagesets.push(p);}
 showpdf(currpage);
}
function shownall() { d = document.getElementById('cent'); d.innerHTML = 'No items left to split';}
function bigger() { size = size *1.5; showpdf(currpage);}
function smaller() { size = size /1.5; showpdf(currpage);}
function sharper() { resolution = parseInt(resolution *1.5); showpdf(currpage);}
function faster() { resolution = parseInt(resolution/1.5); showpdf(currpage);}
function enablenext() { document.getElementById('nextbut').disabled = false;}
function pagegroupdone(resp) {
 console.log(resp);
 currpage += 1;
 if (currpage < j.length) {
  pagesets = null;
  bits = j[currpage][9].split('\f');
  for (i=0;i<bits.length-1;i++) {
   q = (' 1_'+bits[i].split(/ +/).join(' 1_')+' 2_'+bits[i+1].split(/ +/).join(' 2_')).split(' ');
  }
  size = 800/j[currpage][7];
  if (size < 150) size = 150;
  showpdf(currpage);
 } else {
  shownall();
 }
}

function next() {
 sub = 'itemid='+j[currpage][0]+'&pagesets='+JSON.stringify(pagesets);
console.log(sub);
 callserver(sub,pagegroupdone);
 document.getElementById('nextbut').disabled = true;
 setTimeout(enablenext, 2000);
}

function callserver(sub,callback) {
 var x = new XMLHttpRequest();
 x.open("POST", 'ajaxgrouppages.php');
 x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 x.onreadystatechange = function () {
  if (x.readyState == 4 && x.status == 200) {
   if (callback) callback(x.response);
  }
 }
 var url = sub; ;
 x.send(url);
}
pagegroupdone("First");
</script>
