<?php

function breaktolines($str,$max) {
global $pdf;
$lines = explode("\n\r", $str);

$ans = array();
$spwidth = $pdf->getStringWidth(" "); //Width of space char
foreach ($lines as $interlf) {
 $e = explode(" ", $interlf);
 $line = "";
 $linesize = 0;
 // $e is an array of given text split at convenient points
 foreach ($e as $word) {
  $wordwidth = $pdf->getStringWidth($word);
  // Just in case a single word is longer than max line width. Chop off end.
  while ($wordwidth > $max) {
   $word = substr($word, 0, -1);
   $wordwidth = $pdf->getStringWidth($word);
  }
  // Start of a new line, no need to prepend space
  if ($line == "") {
			$line = $word;
			$linesize = $wordwidth;
		} else {
			// Add the next word if max line not exceeded.
			if ($linesize + $spwidth + $wordwidth > $max) {
			// Add what we have so far to end of array
			$ans[] = $line;
			$line = $word;
			$linesize = $wordwidth;
			} else {
				// Add to end of line
				$line .= " " . $word;
				$linesize += $spwidth + $wordwidth;
			}
		}
	}
	if ($line !== "")  $ans = array_merge($ans, explode("\n", $line));
}
return $ans;
}


function nestexplode($d,$always) {
	$d = explode(chr(254),$d);
	foreach ($d as $id=>$item) {
		if (strpos($item,chr(253)) || in_array($id,$always) ) {
			$d[$id] = explode(chr(253),$item) ;
		}
	}
	return $d;
}

function exploderequest() {
 $myrequest = array();
 foreach ($_REQUEST as $rid=>$ritem) {
  $always = array();
  if ($rid == 'doc') $always = array(6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,33) ;
  $myrequest[$rid] = nestexplode($ritem,$always);
 }
 return $myrequest;
}

function depickdate($daynumber) {
 return date('d/m/y',strtotime("+ $daynumber days",0-732*86400)); 
}

function numsizes($numin) {
 // Integer comes in

 global $pdf;
 $numin = round($numin+.49)/100 ;
 $bits = explode('.',$numin);
 $int = $bits[0].'.';
 isset($bits[1]) ? $fract = $bits[1] : $fract = '0';
 $w = $pdf->getStringWidth($int) ;
 $d = $int.str_pad($fract,2,'0') ;

 return array($w,$d);
}
?>