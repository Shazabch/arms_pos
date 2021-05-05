{*
6/10/2011 11:53:27 AM Alex
- create by me
- group report as consignment report

6/17/2011 3:47:41 PM Andy
- Add include custom report templates in consignment report.

7/18/2011 2:33:01 PM Andy
- Add 'Negative Stock Report'. 

12/19/2012 6:03 PM Justin
- Added new report link 'Slow Moving Items'.

3/24/2014 5:56 PM Justin
- Modified the wording from "Color" to "Colour".

5/29/2014 1:50 PM Andy
- Add privilege checking "CON_VIEW_REPORT" for Consignment Report sub-menu.

8/26/2015 12:43 PM Andy
- Add Stock Aging Report for consignment customer.
*}
{if $sessioninfo.privilege.REPORTS_SALES}
<li><a href="#" class=submenu><img src="/ui/icons/table.png" align=absmiddle border=0> Sales Report</a>
	<ul>
		{if $BRANCH_CODE eq 'HQ'}
		<li><a href="/report.yearly_sales_by_branch.php">{*hq01 -*} Yearly Sales Comparison by Branch</a></li>
		{/if}
		<li><a href="sales_report.category.php">Daily Category Sales Report</a></li>
	</ul>
</li>
{/if}

{if $sessioninfo.privilege.REPORTS_SKU}
<li><a href="#" class=submenu><img src=/ui/icons/box.png align=absmiddle border=0> SKU Reports</a>
	<ul>
		<li><a href="report.stock_balance.php">Stock Balance Report by Department</a></li>
		<li><a href="report.stock_balance_report_by_day.php">Stock Balance Report by Day</a></li>
		<li><a href="report.stock_balance_summary.php">Stock Balance Summary</a></li>
		<li><a href="report.negative_stock.php">Negative Stock</a></li>
		<li><a href="report.slow_moving_item.php">Slow Moving Items</a></li>
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_aging.php")}
			<li><a href="report.stock_aging.php">Stock Aging Report</a></li>
		{/if}
	</ul>
</li>
{/if}

{if $sessioninfo.privilege.STOCK_CHECK_REPORT and !strstr($config.hide_from_menu,'STOCK_CHECK_REPORT')}
<li><a href="#" class=submenu>Stock Take</a>
	<ul>
		<li><a href="report.stock_check.php">Stock Take Summary</a></li>
		<li><a href="report.stock_take_variance.php">Stock Take Variance Report</a></li>
		<li><a href="report.stock_take_variance_by_dept.php">Stock Take Variance by Dept Report</a></li>
	</ul>
</li>
{/if}

{if $sessioninfo.privilege.CON_VIEW_REPORT}
<li><a href="#" class=submenu>Consignment Report</a>
	<ul>
        <li><a href="report.consignment_sales_report.php">Sales Report</a></li>
        <li><a href="report.consignment_stock_report.php">Stock Balance Report</a></li>
        <li><a href="report.consignment_in_stock_report.php">In Stock Report</a></li>
        <li><a href="report.consignment_return_stock_report.php">Return Stock Report</a></li>
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.consignment_branch_sales_by_price_type.php")}
        	<li><a href="report.consignment_branch_sales_by_price_type.php">Branch Sales by Price Type Report</a></li>
        {/if}
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.delivered_items_history.php")}
      	<li><a href="report.delivered_items_history.php?view_type=items">Delivered Items History by Items</a></li>
      	<li><a href="report.delivered_items_history.php?view_type=branches">Delivered Items History by Branches</a></li>
    	{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.items_never_delivered.php")}
			<li><a href="report.items_never_delivered.php">Items Never Delivered</a></li>
    	{/if}
	</ul>
</li>
{/if}

{*<li><a href="#" class=submenu><img src="/ui/icons/table.png" align=absmiddle border=0> Sales Report</a>
	<ul>
		{if $BRANCH_CODE eq 'HQ'}
		<li><a href="/report.yearly_sales_by_branch.php"> Yearly Sales Comparison by Branch</a></li>
		<li><a href="/report.branches_sales_comparison_by_day.php"> Branches Sales Comparison by Day / Week</a></li>
		<!--<li><a href="/report.branches_sales_comparison_by_day.php?view_type=week"> Branches Sales Comparison by Week</a></li>-->
		{/if}
		<li><a href="/report.monthly_sales_comparison_by_category.php"> Monthly Sales Comparison by Category</a></li>
		<li><a href="/report.daily_consignment_outright_sales_by_category.php"> Daily Consignment/Outright Sales by Category</a></li>
		<li><a href="/report.monthly_consignment_outright_sales.php"> Monthly Consignment/Outright Sales </a></li>
		<!--<li><a href="/report.actual_and_target_sales_by_week.php?view_type=week"> Actual and Target Sales by Week</a></li>-->
		<li><a href="/report.actual_and_target_sales_by_week.php"> Actual and Target Sales by Day / Week</a></li>
		<li><a href="/report.hourly_sales_by_category.php"> Hourly Sales by Category</a></li>
		<li><a href="/report.hourly_sales_by_day.php?view_type=day"> Hourly Sales by Day</a></li>
		<li><a href="/report.hourly_sales_by_day.php?view_type=month"> Hourly Sales by Month</a></li>
		<li><a href="/report.daily_sales_by_sku.php?view_type=price">Daily Price Code Sales by SKU</a></li>
		<li><a href="sales_report.category.php">Daily Category Sales Report</a></li>
		<li><a href="sales_report.brand.php"><img src=/ui/print.png align=absmiddle border=0>&nbsp; Daily Brand Sales Report</a></li>
		<li><a href="sales_report.vendor.php"><img src=/ui/print.png align=absmiddle border=0>&nbsp; Daily Vendor Sales Report</a></li>
		<li><a href="sales_report.department.php"><img src=/ui/print.png align=absmiddle border=0>&nbsp; Department Monthly Sales Report</a></li>
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sales_report_by_day_month.php")}<li><a href="report.sales_report_by_day_month.php">Sales Report by Day / Month</a></li>{/if}
        <li><a href="report.purchase_vs_sales.php">Purchase vs Sales Report</a></li>
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.daily_brand_sales_by_dept.php")}<li><a href="report.daily_brand_sales_by_dept.php">Daily / Monthly Brand Sales by Department Report</a></li>{/if}
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_sales_by_dept.php")}<li><a href="report.monthly_sales_by_dept.php">Monthly Sales Discounted by Department Report</a></li>{/if}
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.discounted_sales.php")}<li><a href="report.discounted_sales.php">Brand / Vendor Discounted Sales Report</a></li>{/if}
        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.category_cash_credit_sales.php")}<li><a href="report.category_cash_credit_sales.php">Daily Category from Cash/Credit Sales Report</a></li>{/if}
        {if $config.use_consignment_bearing && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.consignment_bearing.php")}<li><a href="report.consignment_bearing.php">Consignment Bearing Report</a></li>{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_vendor_sales.php")}
			<li><a href="report.monthly_vendor_sales.php">Monthly Vendor Sales Report</a></li>
		{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.category_brand_color_size.php")}
			<li><a href="report.category_brand_color_size.php">Sales Trend by Category + Brand for Colour/Size Report</a></li>
		{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.category_old_vs_new_sku_items.php")}
			<li><a href="report.category_old_vs_new_sku_items.php">Old vs New SKU Items by Category Report</a></li>
		{/if}
	</ul>
</li>

<!-- Performance-->
<li><a href="#" class=submenu><img src="/ui/icons/chart_curve.png" align=absmiddle border=0> Performance Report</a>
	<ul>
	    <li><a href="report.quaterly_sales_by_sku_category.php"> Quarterly Sales by Category / SKU Item</a></li>
	    <!--<li><a href="report.daily_sku_items_sales_by_second_last_category.php?view_type=yearly"> Monthly SKU Items Sales by Second Last Category</a></li>-->
	    <li><a href="report.daily_sku_items_sales_by_second_last_category.php"> Daily / Monthly SKU Items Sales by Second Last Category</a></li>
	    <li><a href="report.hourly_sku_items_sales.php"> Hourly SKU Items Sales</a></li>
	    <li><a href="report.quaterly_branch_sales_by_sku_category.php"> Quarterly Branch Sales by Category / SKU Items</a></li>
	    <li><a href="report.category_sales_ranking_comparison_by_branch.php"> Category Sales Ranking Comparison by Branch</a></li>
	    <li><a href="report.brand_sales_ranking_comparison_by_branch.php"> Brand Sales Ranking Comparison by Branch</a></li>
	    <li><a href="report.vendor_sales_ranking_comparison_by_branch.php"> Vendor Sales Ranking Comparison by Branch</a></li>
	    {if $BRANCH_CODE eq 'HQ'}
	    <li><a href="report.sku_item_sales_of_brand_comparison_by_branch.php"> SKU Items Sales of Brand Comparison by Branch</a></li>
	    <li><a href="report.sku_item_sales_of_vendor_comparison_by_branch.php"> SKU Items Sales of Vendor Comparison by Branch</a></li>
	       {/if}
		<li><a href="report.monthly_sales_report_by_category.php"> Monthly SKU Items Sales Ranking by Category</a></li>
		<li><a href="report.monthly_brand_sales_by_category.php"> Monthly Brand Sales by Category</a></li>
		
		<li><a href="report.monthly_vendor_sales_by_category.php"> Monthly Vendor Sales by Category</a></li>
		
		<li><a href="report.daily_sku_items_sales.php"> Daily SKU Items Sales</a></li>
		<li><a href="report.consignment_performance_report.php"> Consignment Performance Report</a></li>
		<li><a href="report.daily_pwp_sku_sales.php"> Daily PWP SKU Sales</a></li>
        <li><a href="report.hourly_sku_items_sales_by_race_and_category.php"> Hourly SKU Items Sales by Race and Category</a></li>
		<li><a href="report.profit_and_loss_report_by_sku_items.php"> Profit and Loss Report by SKU Items</a></li>
		<li><a href="report.block_sku_report.php">Block SKU Report</a></li>
		{if $sessioninfo.privilege.REPORTS_CSA && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.csa.php") && $config.enable_csa_report}
		<li><a href="report.csa.php">Category Stock Analysis Report</a></li>
		{/if}
	</ul>
</li>*}

{if isset($config.custom_report)}
	{include file=$config.custom_report}
{/if}