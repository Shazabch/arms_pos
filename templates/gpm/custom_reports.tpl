{*

04/08/2020 04:00 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu"><i class="icofont-chart-histogram icofont"></i>GPM Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/gpm/report.broadcast.trade_offer.php")}
				<li><a href="custom/gpm/report.broadcast.trade_offer.php">Broadcast Trade Offer Report</a></li>
			{/if}
		</ul>
	</li>
{/if}