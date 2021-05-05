
{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu">Custom Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/sbakery/report.stock_movement_status.php")}
				<li><a href="custom/sbakery/report.stock_movement_status.php">Stock Movement Status Report</a></li>
			{/if}
		</ul>
	</li>
{/if}
