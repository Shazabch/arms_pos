<?php /* Smarty version 2.6.18, created on 2021-05-11 15:39:18
         compiled from front_end.tpl */ ?>

<div id=front_end>

	<?php if ($_SERVER['PHP_SELF'] != '/front_end.check_code.php'): ?>
	<div style="margin: 0 0 0 90%; position: absolute;">
	<a class="login-link" href="/front_end.check_code.php" title="Check Code">
		<img src="/ui/icon-flat/icon-checker.png" border=0 target=_fe>
	<br>Check Code</a>
	</div>
	<?php endif; ?>

	<div style="margin: 100px 0 0 90%; position: absolute;">
	<a class="login-link" href="/price_check" title="Price Checker">
	<img src="/ui/icon-flat/price_checker.png" border=0 target=_fe>
	<br>Price Checker</a>
	</div>

	<?php if ($_SERVER['PHP_SELF'] != '/login.php'): ?>
	<div>
	<a href="/index.php" title="User Login"><img src="/ui/fe_login.png" border=0 target=_fe><br>User Login</a>
	</div>
	<?php endif; ?>

<!--	<?php if ($_SERVER['PHP_SELF'] != '/vendor.php'): ?>
	<div>
	<a href="/vendor_login.php" title="Vendor Login"><img src="/ui/fe_vendor.png" border=0 target=_fe><br>Vendor Login</a>
	</div>
	<?php endif; ?>
-->
</div>