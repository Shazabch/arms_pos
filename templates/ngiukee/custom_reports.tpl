{*
11/23/2012 10:34 AM Andy
- Add new custom report for aneka (Counter Collection Detail Report).

11/26/2012 10:35 AM Andy
- Add $smarty.server.DOCUMENT_ROOT prefix for file_exists checking to fix some of the menu not show when in custom report other other sub-folder.

7/10/2013 11:20 AM Andy
- Remove Goods Receiving Files Transfer and UBS Export from custom report.

5/29/2014 2:44 PM Fithri
- change report name to Category Sales Report by SKU
*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu">Custom Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/ngiukee/consignment_category_sales_report.php")}
				{*<li><a href="custom/ngiukee/consignment_category_sales_report.php">Consignment Category Sales Report</a></li>*}
				<li><a href="custom/ngiukee/consignment_category_sales_report.php">Category Sales Report by SKU</a></li>
			{/if}
		</ul>
	</li>
{/if}
