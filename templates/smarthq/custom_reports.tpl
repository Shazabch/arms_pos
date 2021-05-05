{*
12/9/2014 11:57 AM Andy
- New Custom Report for smarthq. (Stock Balance Summary)
*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu">Custom Report</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/smarthq/report.stock_balance_summary.php")}
				<li><a href="custom/smarthq/report.stock_balance_summary.php">Stock Balance Summary</a></li>
			{/if}
		</ul>
	</li>
{/if}
