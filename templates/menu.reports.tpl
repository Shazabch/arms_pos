{*
6/15/2010 12:09:34 PM yinsee
- brand sales and vendor sale comparison report out can view by all branches

6/15/2010 3:04:15 PM Justin
- fixed the menu to show brand and vendor sales comparison reports instead of SKU Item brand and vendor sales

8/12/2010 3:30:25 PM Alex
- Add csa report

12/15/2010 10:18:01 AM Alex
- add consignment bearing report

3/25/2011 10:48:52 AM Andy
- Fix wrong spelling. Quaterly to Quarterly

6/1/2011 4:47:44 PM Alex
- add $config.use_consignment_bearing to hide consignment bearing report

12/27/2011 11:45:43 AM Justin
- Added new report hyperlink "Monthly Vendor Sales Report".

12/28/2011 11:27:54 AM Andy
- Add new sales report "Sales Trend by Category + Brand for Color/Size Report".

12/30/2011 3:03:43 PM Justin
- Added new sales report "Old vs New SKU Items by Category Report".

8/17/2012 3:18 PM Justin
- Added new config checking to show the CSA report link.

11/23/2012 10:14 AM Andy
- Add $smarty.server.DOCUMENT_ROOT prefix for file_exists checking to fix some of the menu not show when in custom report other other sub-folder.

3/24/2014 5:56 PM Justin
- Modified the wording from "Color" to "Colour".

11/27/2014 4:39 PM Andy
- Change "Daily Brand Sales Report","Daily Vendor Sales Report" and "Department Monthly Sales Report" to new url.

12/12/2016 10:33 AM Andy
- Rename "Department Monthly Sales Report" to "Daily Department Sales Report".

3/20/2017 10:44 Am Chong Meng
- Add new report "Cash/Credit Sales Report".

5/3/2017 11:15 AM Qiu Ying
- Enhanced to add (POS + DO Sales) in Title

6/6/2017 11:14 AM Qiu Ying
- Enhanced to rearrange report menu

7/11/2017 10:25 AM Justin
- Modified the report name changed from "Daily / Monthly SKU Items Sales by Second Last Category" become "Sales by Category (4th Level Category)".

12/29/2017 2:48 PM Justin
- Added new report "Monthly GP Report by PO Owner / Buyer".

1/8/2018 5:57 PM Justin
- Added new report "Consignment Sales by SKU Report".

10/23/2018 5:09 PM Justin
- Enhanced the report name to include "Concessionaire".
*}
{if $sessioninfo.privilege.REPORTS_SALES}
<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Sales Report</span><i class="sub-angle fe fe-chevron-down"></i></a>

	<ul class="sub-slide-menu">
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.actual_and_target_sales_by_week.php">{*hb06 -*} Actual and Target Sales by Day / Week</a></li>
		{if $BRANCH_CODE eq 'HQ'}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.branches_sales_comparison_by_day.php">{*hq02 -*} Branches Sales Comparison by Day / Week</a></li>
		<!--<li><a href="/report.branches_sales_comparison_by_day.php?view_type=week">{*hq03 -*} Branches Sales Comparison by Week</a></li>-->
		{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.discounted_sales.php")}<li><a href="report.discounted_sales.php">Brand / Vendor Discounted Sales Report</a></li>{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.cash_credit_sales.php")}
			<li class="sub-slide-sub"><a class="sub-slide-item" href="report.cash_credit_sales.php">Cash/Credit Sales Report</a></li>
		{/if}
		{if $config.use_consignment_bearing && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.consignment_bearing.php")}<li class="sub-slide-sub"><a class="sub-slide-item" href="report.consignment_bearing.php">Consignment Bearing Report</a></li>{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.consignment_sales_by_sku.php")}
		<li class="sub-slide-sub"><a  class="sub-slide-item" href="report.consignment_sales_by_sku.php">Consignment Sales by SKU Report</a></li>{/if}
		{*<li class="sub-slide-sub"><a class="sub-slide-item" href="sales_report.brand.php">Daily Brand Sales Report</a></li>*}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.brand_sales.php">Daily Brand Sales Report</a></li>
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.category_cash_credit_sales.php")}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.category_cash_credit_sales.php">Daily Category from Cash/Credit Sales Report</a></li>{/if}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="sales_report.category.php">Daily Category Sales Report (POS + DO Sales)</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.daily_consignment_outright_sales_by_category.php">{*hb02  -*} Daily Consignment/Outright/Concessionaire Sales by Category</a></li>
		{*<li class="sub-slide-sub"><a class="sub-slide-item" href="sales_report.department.php">Department Monthly Sales Report</a></li>*}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.dept_sales.php">Daily Department Sales Report</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.daily_sales_by_sku.php?view_type=price">Daily Price Code Sales by SKU</a></li>
		{*<li class="sub-slide-sub"><a class="sub-slide-item" href="sales_report.vendor.php">Daily Vendor Sales Report</a></li>*}
		<li><a href="report.vendor_sales.php">Daily Vendor Sales Report</a></li>
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.daily_brand_sales_by_dept.php")}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.daily_brand_sales_by_dept.php">Daily / Monthly Brand Sales by Department Report</a></li>{/if}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.hourly_sales_by_category.php">{*hb09 -*} Hourly Sales by Category</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.hourly_sales_by_day.php?view_type=day">{*hb10 -*} Hourly Sales by Day</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.hourly_sales_by_day.php?view_type=month">{*hb11 -*} Hourly Sales by Month</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.monthly_consignment_outright_sales.php">{*hb07 -*} Monthly Consignment/Outright/Concessionaire Sales </a></li>
		<!--<li><a href="/report.actual_and_target_sales_by_week.php?view_type=week">{*hb04 -*} Actual and Target Sales by Week</a></li>-->
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_gp_report_by_vendor.php")}
			<li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_gp_report_by_vendor.php">Monthly GP Report by PO Owner / Buyer</a></li>
		{/if}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.monthly_sales_comparison_by_category.php">{*hb01 -*} Monthly Sales Comparison by Category</a></li>
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_sales_by_dept.php")}<li><a href="report.monthly_sales_by_dept.php">Monthly Sales Discounted by Department Report</a></li>{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_vendor_sales.php")}
			<li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_vendor_sales.php">Monthly Vendor Sales Report</a></li>
		{/if}
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.category_old_vs_new_sku_items.php")}
			<li class="sub-slide-sub"><a class="sub-slide-item" href="report.category_old_vs_new_sku_items.php">Old vs New SKU Items by Category Report</a></li>
		{/if}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.purchase_vs_sales.php">Purchase vs Sales Report</a></li>
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sales_report_by_day_month.php")}<li><a href="report.sales_report_by_day_month.php">Sales Report by Day / Month</a></li>{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.category_brand_color_size.php")}
			<li class="sub-slide-sub"><a class="sub-slide-item" href="report.category_brand_color_size.php">Sales Trend by Category + Brand for Colour/Size Report</a></li>
		{/if}
		{if $BRANCH_CODE eq 'HQ'}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.yearly_sales_by_branch.php">{*hq01 -*} Yearly Sales Comparison by Branch</a></li>
		{/if}
	</ul>
{/if}
{if $sessioninfo.privilege.REPORTS_PERFORMANCE}
<!-- Performance-->
<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label"> Performance Report</span><i class="sub-angle fe fe-chevron-down"></i></a>

	<ul class="sub-slide-menu">
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.block_sku_report.php">Block SKU Report</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.brand_sales_ranking_comparison_by_branch.php">{*ph02 -*} Brand Sales Ranking Comparison by Branch</a></li>
		{if $BRANCH_CODE eq 'HQ'}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.category_sales_ranking_comparison_by_branch.php">{*ph01 -*} Category Sales Ranking Comparison by Branch</a></li>
		{/if}
		{if $sessioninfo.privilege.REPORTS_CSA && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.csa.php") && $config.enable_csa_report}
		<li class="sub-slide-sub"><a href="report.csa.php">Category Stock Analysis Report</a></li>
		{/if}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.consignment_performance_report.php">{*pb05 -*} Consignment Performance Report</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.daily_pwp_sku_sales.php">{*pb07 -*} Daily PWP SKU Sales</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.daily_sku_items_sales.php">{*pb04 -*} Daily SKU Items Sales</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.hourly_sku_items_sales.php">{*phb06 -*} Hourly SKU Items Sales</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.hourly_sku_items_sales_by_race_and_category.php">{*pb08 -*} Hourly SKU Items Sales by Race and Category</a></li>
	    <li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_brand_sales_by_category.php">{*pb02 -*} Monthly Brand Sales by Category</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_sales_report_by_category.php">{*pb01 -*} Monthly SKU Items Sales Ranking by Category</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.monthly_vendor_sales_by_category.php">{*pb03 -*} Monthly Vendor Sales by Category</a></li>
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.profit_and_loss_report_by_sku_items.php">{*pb09 -*} Profit and Loss Report by SKU Items</a></li>
		{if $BRANCH_CODE eq 'HQ'}
	    <li class="sub-slide-sub"><a class="sub-slide-item" href="report.quaterly_branch_sales_by_sku_category.php">{*phb09 -*} Quarterly Branch Sales by Category / SKU Items</a></li>
		{/if}
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.quaterly_sales_by_sku_category.php">{*phb01 -*} Quarterly Sales by Category / SKU Item</a></li> {* Disabled fpr pkt-hq *}
	    <!--<li><a href="report.daily_sku_items_sales_by_second_last_category.php?view_type=yearly">{*phb02 -*} Monthly SKU Items Sales by Second Last Category</a></li>-->
		<li class="sub-slide-sub"><a class="sub-slide-item" href="report.daily_sku_items_sales_by_second_last_category.php">{*phb03 -*} Sales by 4th Level Category</a></li>
	    {if $BRANCH_CODE eq 'HQ'}
	    <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_item_sales_of_brand_comparison_by_branch.php">{*ph03 -*} SKU Items Sales of Brand Comparison by Branch</a></li>
	    <li class="sub-slide-sub"><a class="sub-slide-item" href="report.sku_item_sales_of_vendor_comparison_by_branch.php">{*ph04 -*} SKU Items Sales of Vendor Comparison by Branch</a></li>
	    {/if}
	    <li><a href="report.vendor_sales_ranking_comparison_by_branch.php">{*ph05 -*} Vendor Sales Ranking Comparison by Branch</a></li>
	</ul>
{/if}
{if $sessioninfo.privilege.REPORTS_MEMBERSHIP}
<!-- Member -->
<li class="sub-slide"><a class="sub-side-menu__item" data-toggle="sub-slide" href="#"><span class="sub-side-menu__label">Membership Report</span><i class="sub-angle fe fe-chevron-down"></i></a>

	<ul class="sub-slide-menu">
	<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.hourly_member_sales_comparison_by_month.php">{*mhb02 -*} Hourly Member Sales Comparison by Month / Day</a></li>
	<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.member_sales_comparison_by_day.php">{*mhb01 -*} Member Sales Comparison by Day</a></li>
	<!--<li><a href="/report.hourly_member_sales_comparison_by_month.php?view_type=day">{*mhb03 -*} Hourly Member Sales Comparison by Day</a></li>-->
	<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.member_sales_ranking.php">{*mhb04 -*} Member Sales Ranking</a></li>
	<li class="sub-slide-sub"><a class="sub-slide-item" href="/report.member_transaction_history.php"> Member Transaction History</a></li>
	</ul>
{/if}
{*<!--
<li><a href="#" class=submenu>hb08 - * kiv *</a></li>
-->
*}
