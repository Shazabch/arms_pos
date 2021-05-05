<?php
/*
4/28/2011 10:59:22 AM Andy
- Fix price checker auto logout if no item scan after 30 minutes
*/
?>
<html>
<meta http-equiv="refresh" content="30;URL=idle.php">

<style>
body {font:12px Arial; margin:0; padding:0; background:#000; color:#0f0; text-align:center; }
</style>

<body>

<br><h1>ARMS&copy; Price Checker</h1><br>Please scan your barcode.<br>


<?
print "Branch: ".$_COOKIE['arms_login_branch'];
?>

</html>
