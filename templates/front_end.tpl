{*
5/6/2019 5:33 PM Justin
- Enhanced to change the Check Code icon do not take so much of height.

18/03/2020 4:13 PM Sheila
- Modified layout to compatible with new UI.

02/18/2021 1:42 PM Rayleen
- Add new icon "Price Checker" below Check Code
*}

<div id=front_end>

	{if $smarty.server.PHP_SELF ne '/front_end.check_code.php'}
	<div style="margin: 0 0 0 90%; position: absolute;">
	<a class="login-link" href="/front_end.check_code.php" title="Check Code">
	{*<img src="/ui/fe_checker.png" border=0 target=_fe>*}
	<img src="/ui/icon-flat/icon-checker.png" border=0 target=_fe>
	<br>Check Code</a>
	</div>
	{/if}

	<div style="margin: 100px 0 0 90%; position: absolute;">
	<a class="login-link" href="/price_check" title="Price Checker">
	<img src="/ui/icon-flat/price_checker.png" border=0 target=_fe>
	<br>Price Checker</a>
	</div>

	{if $smarty.server.PHP_SELF ne '/login.php'}
	<div>
	<a href="/index.php" title="User Login"><img src="/ui/fe_login.png" border=0 target=_fe><br>User Login</a>
	</div>
	{/if}

<!--	{if $smarty.server.PHP_SELF ne '/vendor.php'}
	<div>
	<a href="/vendor_login.php" title="Vendor Login"><img src="/ui/fe_vendor.png" border=0 target=_fe><br>Vendor Login</a>
	</div>
	{/if}
-->
</div>
