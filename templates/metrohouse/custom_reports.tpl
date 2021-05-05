{*

04/08/2020 04:03 PM Sheila
- Modified layout to compatible with new UI.

*}

{if $sessioninfo.privilege.REPORTS_CUSTOM1}
<li><a href="#" class="submenu">Custom Reports</a>
	<ul>
	<li><a href="custom/metrohouse/report.consignment_branch_sales_by_price_type.php"><i class="icofont-chart-histogram icofont"></i>Branch Sales by Price Type Report</a></li>
	<li><a href="custom/metrohouse/consignment.price_wizard.php"><i class="icofont-money icofont"></i>Consignment Price Wizard</a></li>
	</ul>
</li>
{/if}
