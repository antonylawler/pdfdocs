<?php
require_once('../includes/TCPDF/tcpdf.php');
require_once('pdffuncs.php');

$request = exploderequest();
$pdf = new TCPDF();

 $firstline = 90;
 $lastline = 240;
 $ypos = 999999;
 $pdf->SetPrintHeader(false);
 $pdf->SetPrintFooter(false);
 $pdf->setFooterMargin(0);
 $pdf->setHeaderMargin(0);
 $pdf->SetAutoPageBreak(TRUE, 0);
 $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0,0,0)));
 $doc = $request['doc'];
 for ($i = 0 ; $i < count($doc[6]);$i++) {
  if ($ypos > $lastline) {
   $ypos == 999999 ?	firstheader() : otherheader();
   $ypos = $firstline;
  } else {
   $pdf->Line(15, $ypos , 195, $ypos);
  }
  $pdf->SetFont(null,'',10);
  $pdf->Text(15,$ypos,$doc[6][$i]);
  $pdf->Text(60,$ypos,ucwords(strtolower($doc[7][$i])));
  $num = round($doc[8][$i]);
  $w = $pdf->getStringWidth($num);
  $pdf->Text(58-$w,$ypos, $num);
  
  list($w,$d) = numsizes($doc[9][$i]/100);
  $pdf->Text(190-$w,$ypos,$d);
  $ypos = $ypos + 5 ;
 }
 $request['doc'][3] = explode(chr(252),$doc[3]);
 $txt = $request['doc'][3];
 $ypos += 10;
 for ($i = 0;$i < count($txt);$i++) {
  if ($ypos > $lastline) {
   $ypos == 999999 ?	firstheader() : otherheader();
   $ypos = $firstline;
  }
  $pdf->Text(60,$ypos,$txt[$i]);
  $ypos = $ypos + 5;
 }
 lastfooter();

$filename = __DIR__.'/pdfdocs/DEBIT_'.$request['id'][0].'.PDF';
echo $filename;
$pdf->Output($filename, 'F');


function otherheader() {
 otherfooter();
 firstheader();
}

function otherfooter() {
 lastfooter();
}

function firstheader() {
 global $pdf,$request;
 $doc = $request['doc'];
 $pdf->AddPage();
 $pdf->setJPEGQuality(600);
 $pdf->image('COLOGO.jpg',15,12,60);
 $pdf->setFont(null,'B',110);
 $pdf->SetFont(null,'',10);

 $pdf->Text(20,40,ucwords(strtolower($request['supplier'][0])));
 $pdf->Text(20,44,ucwords(strtolower($request['supplier'][1])));
 $pdf->Text(20,48,ucwords(strtolower($request['supplier'][2])));
 $pdf->Text(20,52,ucwords(strtolower($request['supplier'][3])));
 $pdf->Text(20,56,$request['supplier'][4]);
 
 $pdf->SetAlpha(0.5);
 $pdf->SetFont(null,'B',30);
 
 $t = 'DEBIT NOTE';
 
 $pdf->Text(195-$pdf->getStringWidth($t),10,$t);
 $pdf->SetFont(null,'',13);
 $pdf->Text(145,29,'Doc. No.');
 $pdf->Text(145,36,'Date');

 $pdf->SetAlpha(1);

 $t = $request['id'][0] ;
 $pdf->Text(195-$pdf->getStringWidth($t),29,$t);

 $t = depickdate($doc[1]) ;
 $pdf->Text(195-$pdf->getStringWidth($t),36,$t);

 $pdf->SetFont(null,'',10); 
// $pdf->Text(145,50,$doc[34]);

 $pages = $pdf->getAliasNbPages();
 $thispage = $pdf->getAliasNumPage() ;

 $pdf->Text(175,78,"Page $thispage of $pages"); 

 $pdf->SetFont(null,'B',10);

 $pdf->Line(15,85,195,85);
 $pdf->Line(15,90,195,90);
 $pdf->Text(15,85,'Product Code');
 $pdf->Text(43,85,'Quantity');
 $pdf->Text(60,85,'Description');
 $pdf->Text(184,85,'Value');

}

function lastfooter() {
 global $pdf,$request,$copy;

 $pdf->Line(15,250,195,250);
 $doc = $request['doc'];

 $pdf->SetFont(null,'',10);
 $pdf->Text(15,252,'Originating Branch');
 $pdf->Text(16,257,ucwords(strtolower($request['branch'][0])));
 $pdf->Text(16,261,ucwords(strtolower($request['branch'][1])));
 $pdf->Text(16,265,ucwords(strtolower($request['branch'][2])));
 $pdf->Text(16,269,ucwords(strtolower($request['branch'][3])));
 $pdf->Text(16,273,ucwords(strtolower($request['branch'][4])));
 
 $pdf->Text(150,252,'Total Value');
 list($w,$d) = numsizes(array_sum($doc[9])/100);
 $pdf->Text(190-$w,252,$d);
 
 $pdf->SetFont(null,'N',8); 
 $pdf->Text(15,287,'VAT Number:'.$request['company'][8].' e:admin@plumlink.co.uk '.' phone:'.$request['branch'][7]);
 $pdf->Text(15,283,ucwords(strtolower($request['company'][0].','.$request['company'][1].','.$request['company'][2].','.$request['company'][3].',')).$request['company'][4]);
}

?>
