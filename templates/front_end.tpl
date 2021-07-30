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
	<div class="position-absolute text-center text-warning" data-placement="left" data-toggle="tooltip-primary" title="Check Code" style="right: 20px; top: 20px; z-index: 9999999;">
		<div class="card p-2">
			<a href="/front_end.check_code.php" data-placement="left" data-toggle="tooltip-primary" title="Check Code" class="link-stretched text-reset"><i class="fas fa-sticky-note fa-3x" target=_fe></i></a>
			<span class="fs-07 mt-1 font-weight-bold">Check Code</span>
		</div>
	</div>
	{/if}
	<div class="position-absolute text-center text-info" data-placement="left" data-toggle="tooltip-primary" title="Check Code" style="right: 20px; top: 115px; z-index: 999999;">
		<div class="card p-2">
			<a href="/price_check" data-placement="left" data-toggle="tooltip-primary" title="Price Checker" class="link-stretched text-reset"><i class="fas fa-tag fa-3x" target=_fe></i></a>
			<span class="fs-07 mt-1 font-weight-bold">Price Check</span>
		</div>
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
