{*
5/9/2013 3:30 PM Andy
- New Cutemaree Custom Report: Item Sales and Balance Report

04/08/2020 03:59 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu"><i class="icofont-chart-histogram icofont"></i>Custom Report</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/cutemaree/report.item_sales_report.php")}
				<li><a href="custom/cutemaree/report.item_sales_report.php">Item Sales and Balance Report</a></li>
			{/if}
		</ul>
	</li>
{/if}
