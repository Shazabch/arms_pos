{*

04/08/2020 04:05 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
<li><a href="#" class="submenu">Custom Reports</a>
	<ul>
	<li><a href="custom/smo/sales_report.vendor.php">{*<img src=/ui/print.png align=absmiddle border=0>&nbsp; *} <i class="icofont-user-suited icofont"></i> Daily Vendor Sales Report</a></li>
	</ul>
</li>
{/if}
