#!/Perl/bin/perl
use CGI qw(:standard);
print "Content-type:text/html\n\n";
$outfile  ="C:\\IBM\\UV\\AQUATEC\\FROMWEB\\";
$infile   ="C:\\IBM\\UV\\AQUATEC\\TOWEB\\";
$sessionid = "";
$sessionid = param('session');

if ($sessionid eq "") {
 $length=8;
 for($i=0 ; $i< $length ;) {
  $j = chr(int(rand(127)));
  if($j =~ /[a-zA-Z0-9]/) {
   $sessionid .=$j;
  $i++;
  }
 }
}
open(of,'> '.$outfile.$sessionid);
print of $ENV{QUERY_STRING}; 
close(of);
for ($count=1;$count<50;$count++) {
 if (-e $infile.$sessionid) {
  open(ifl,$infile.$sessionid);
  @inf = <ifl>;
  print @inf;
  close(ifl);
  $count = 1000 ;
 } else {
  sleep 1;
 }
}
