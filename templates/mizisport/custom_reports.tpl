{*
11/27/2012 1:45 PM Andy
- Add $smarty.server.DOCUMENT_ROOT prefix for file_exists checking to fix some of the menu not show when in custom report other other sub-folder.
- New Custom Report: Outlet Receipts Report.

12/6/2012 5:47 PM Justin
- Added new report hyperlink "Sell Thru by Category Report".
*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu">Custom Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/mizisport/report.stock_aging.php")}
				<li><a href="custom/mizisport/report.stock_aging.php">Stock Aging Report</a></li>
			{/if}
			
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/mizisport/report.outlet_receipts_report.php")}
				<li><a href="custom/mizisport/report.outlet_receipts_report.php">Outlet Receipts Report</a></li>
			{/if}

			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/mizisport/report.sell_thru_by_category.php")}
				<li><a href="custom/mizisport/report.sell_thru_by_category.php">Sell Thru by Category Report</a></li>
			{/if}
		</ul>
	</li>
{/if}
