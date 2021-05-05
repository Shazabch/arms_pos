<?php
ini_set("display_errors",1);
include("../include/class.phpmailer.php");
$mailer = new PHPMailer();
$mailer->Subject="Test";
$mailer->Body="Test!! Please reply to yinsee@wsatp.com if you receive this!";
$mailer->AddAddress("sllee@aneka.com.my");
$mailer->AddAddress("cslo@aneka.com.my");
$mailer->AddAddress("yinsee@aneka.com.my");
print "Sending";
$r = $mailer->Send();
var_dump($r);

?>
