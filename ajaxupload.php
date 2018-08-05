<?php
header('Content-type: application/json');
$success = false;
$cookied = @$_COOKIE['_u'];
$cookied = true;
if ($cookied) {
  if (!empty($_FILES)) {
    $i = 0;
    while ($current_file = @$_FILES["file$i"]['name']) {
      if (@$_FILES["file$i"]["error"] == 1) {
        $response = "Error " . $_FILES['file$i']["error"];
      } else {
        $extension = strtolower(substr(strrchr($current_file, '.'), 1));
        if ($extension == 'txt' || $extension == 'csv' || $extension == 'tdf') {
          $destination = 'C:\U2\UV\AQUATEC\TOUNIV\'		  . $current_file;
          $action      = move_uploaded_file($_FILES["file$i"]['tmp_name'], $destination);
          if ($action) {
            $response = $current_file;
            $success  = true;
          } else {
            $response = 'Failed to move ' . $destination;
          }
        } else {
          $response = "Invalid file type " + $extension;
        }
      }
      $i += 1;
    }
  } else {
    $response = "PHP FILES empty";
  }
} else {
  $response = "NO PERMISSIONS";
}
echo json_encode(array($success,$response));
?>