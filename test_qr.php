<?php
//include only that one, rest required files will be included from it
include("include/phpqrcode/qrlib.php");

//write code into file, Error corection lecer is lowest, L (one form: L,M,Q,H)
//each code square will be 4x4 pixels (4x zoom)
//code will have 2 code squares white boundary around 

QRcode::png('ARMS QR Testing', 'test_qr.png', 'L', 8, 2);

header('Content-type: image/jpeg');
print file_get_contents('test_qr.png');
?>
