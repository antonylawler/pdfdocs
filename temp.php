<pre>
<?php
$call = 'this is it / and it goes on';
print("BEfore encode\n");
$call = json_encode($call);
print_r($call);
print("\nAfter decode\n");
$resp  = json_decode($call);
print_r($resp);
$conn  = new mysqli("127.0.0.1","root","password","docs");
print_r($resp);
print_r("----<br>");
print_r($conn->real_escape_string('Try this out /  andthis'));

?>