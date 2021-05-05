{*
11/23/2012 10:34 AM Andy
- Add new custom report for aneka (Counter Collection Detail Report).

11/26/2012 10:35 AM Andy
- Add $smarty.server.DOCUMENT_ROOT prefix for file_exists checking to fix some of the menu not show when in custom report other other sub-folder.

04/08/2020 03:58 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu"><i class="icofont-chart-histogram icofont"></i>Custom Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/aneka/pos_report.counter_collection_details.php")}
				<li><a href="custom/aneka/pos_report.counter_collection_details.php">Counter Collection Detail Report</a></li>
			{/if}
		</ul>
	</li>
{/if}
