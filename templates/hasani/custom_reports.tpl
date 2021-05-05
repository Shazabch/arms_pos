{*

04/08/2020 04:01 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu"><i class="icofont-chart-histogram icofont"></i>Custom Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/hasani/report.stock_balance_summary.php")}
				<li><a href="custom/hasani/report.stock_balance_summary.php">Stock Balance Summary</a></li>
			{/if}
		</ul>
	</li>
{/if}
