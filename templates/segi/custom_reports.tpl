{*

04/08/2020 04:04 PM Sheila
- Modified layout to compatible with new UI.

*}
{if $sessioninfo.privilege.REPORTS_CUSTOM1}
	<li><a href="#" class="submenu">Custom Reports</a>
		<ul>
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/custom/segi/report.member_point_expire.php")}
				<li><a href="custom/segi/report.member_point_expire.php"><i class="icofont-star icofont"></i>Member Point Expire Report</a></li>
			{/if}
		</ul>
	</li>
{/if}