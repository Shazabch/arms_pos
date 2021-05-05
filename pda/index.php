<?php

require('common.php');

if (!$login)
{
	header("Location: login.php");
}
else
{
	header("Location: home.php");
}
?>
