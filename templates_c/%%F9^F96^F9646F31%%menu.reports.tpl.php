<?php /* Smarty version 2.6.18, created on 2021-05-11 16:12:53
         compiled from menu.reports.tpl */ ?>
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_SALES']): ?>
<li><a href="#" class=submenu><img src="/ui/icons/table.png" align=absmiddle border=0> Sales Report</a>
	<ul>
		<li><a href="/report.actual_and_target_sales_by_week.php"> Actual and Target Sales by Day / Week</a></li>
		<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
		<li><a href="/report.branches_sales_comparison_by_day.php"> Branches Sales Comparison by Day / Week</a></li>
		<!--<li><a href="/report.branches_sales_comparison_by_day.php?view_type=week"> Branches Sales Comparison by Week</a></li>-->
		<?php endif; ?>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.discounted_sales.php" )): ?><li><a href="report.discounted_sales.php">Brand / Vendor Discounted Sales Report</a></li><?php endif; ?>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.cash_credit_sales.php" )): ?>
			<li><a href="report.cash_credit_sales.php">Cash/Credit Sales Report</a></li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['config']['use_consignment_bearing'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.consignment_bearing.php" )): ?><li><a href="report.consignment_bearing.php">Consignment Bearing Report</a></li><?php endif; ?>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.consignment_sales_by_sku.php" )): ?><li><a href="report.consignment_sales_by_sku.php">Consignment Sales by SKU Report</a></li><?php endif; ?>
				<li><a href="report.brand_sales.php"><img src="/ui/print.png" align="absmiddle" border="0">&nbsp; Daily Brand Sales Report</a></li>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.category_cash_credit_sales.php" )): ?><li><a href="report.category_cash_credit_sales.php">Daily Category from Cash/Credit Sales Report</a></li><?php endif; ?>
		<li><a href="sales_report.category.php">Daily Category Sales Report (POS + DO Sales)</a></li>
		<li><a href="/report.daily_consignment_outright_sales_by_category.php"> Daily Consignment/Outright/Concessionaire Sales by Category</a></li>
				<li><a href="report.dept_sales.php"><img src=/ui/print.png align=absmiddle border=0>&nbsp; Daily Department Sales Report</a></li>
		<li><a href="/report.daily_sales_by_sku.php?view_type=price">Daily Price Code Sales by SKU</a></li>
				<li><a href="report.vendor_sales.php"><img src=/ui/print.png align=absmiddle border=0>&nbsp; Daily Vendor Sales Report</a></li>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.daily_brand_sales_by_dept.php" )): ?><li><a href="report.daily_brand_sales_by_dept.php">Daily / Monthly Brand Sales by Department Report</a></li><?php endif; ?>
		<li><a href="/report.hourly_sales_by_category.php"> Hourly Sales by Category</a></li>
		<li><a href="/report.hourly_sales_by_day.php?view_type=day"> Hourly Sales by Day</a></li>
		<li><a href="/report.hourly_sales_by_day.php?view_type=month"> Hourly Sales by Month</a></li>
		<li><a href="/report.monthly_consignment_outright_sales.php"> Monthly Consignment/Outright/Concessionaire Sales </a></li>
		<!--<li><a href="/report.actual_and_target_sales_by_week.php?view_type=week"> Actual and Target Sales by Week</a></li>-->
        <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.monthly_gp_report_by_vendor.php" )): ?>
			<li><a href="report.monthly_gp_report_by_vendor.php">Monthly GP Report by PO Owner / Buyer</a></li>
		<?php endif; ?>
		<li><a href="/report.monthly_sales_comparison_by_category.php"> Monthly Sales Comparison by Category</a></li>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.monthly_sales_by_dept.php" )): ?><li><a href="report.monthly_sales_by_dept.php">Monthly Sales Discounted by Department Report</a></li><?php endif; ?>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.monthly_vendor_sales.php" )): ?>
			<li><a href="report.monthly_vendor_sales.php">Monthly Vendor Sales Report</a></li>
		<?php endif; ?>
        <?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.category_old_vs_new_sku_items.php" )): ?>
			<li><a href="report.category_old_vs_new_sku_items.php">Old vs New SKU Items by Category Report</a></li>
		<?php endif; ?>
		<li><a href="report.purchase_vs_sales.php">Purchase vs Sales Report</a></li>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.sales_report_by_day_month.php" )): ?><li><a href="report.sales_report_by_day_month.php">Sales Report by Day / Month</a></li><?php endif; ?>
		<?php if (file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.category_brand_color_size.php" )): ?>
			<li><a href="report.category_brand_color_size.php">Sales Trend by Category + Brand for Colour/Size Report</a></li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
		<li><a href="/report.yearly_sales_by_branch.php"> Yearly Sales Comparison by Branch</a></li>
		<?php endif; ?>
	</ul>
<?php endif; ?>
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_PERFORMANCE']): ?>
<!-- Performance-->
<li><a href="#" class=submenu><img src="/ui/icons/chart_curve.png" align=absmiddle border=0> Performance Report</a>
	<ul>
		<li><a href="report.block_sku_report.php">Block SKU Report</a></li>
		<li><a href="report.brand_sales_ranking_comparison_by_branch.php"> Brand Sales Ranking Comparison by Branch</a></li>
		<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
		<li><a href="report.category_sales_ranking_comparison_by_branch.php"> Category Sales Ranking Comparison by Branch</a></li>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_CSA'] && file_exists ( ($_SERVER['DOCUMENT_ROOT'])."/report.csa.php" ) && $this->_tpl_vars['config']['enable_csa_report']): ?>
		<li><a href="report.csa.php">Category Stock Analysis Report</a></li>
		<?php endif; ?>
		<li><a href="report.consignment_performance_report.php"> Consignment Performance Report</a></li>
		<li><a href="report.daily_pwp_sku_sales.php"> Daily PWP SKU Sales</a></li>
		<li><a href="report.daily_sku_items_sales.php"> Daily SKU Items Sales</a></li>
		<li><a href="report.hourly_sku_items_sales.php"> Hourly SKU Items Sales</a></li>
		<li><a href="report.hourly_sku_items_sales_by_race_and_category.php"> Hourly SKU Items Sales by Race and Category</a></li>
	    <li><a href="report.monthly_brand_sales_by_category.php"> Monthly Brand Sales by Category</a></li>
		<li><a href="report.monthly_sales_report_by_category.php"> Monthly SKU Items Sales Ranking by Category</a></li>
		<li><a href="report.monthly_vendor_sales_by_category.php"> Monthly Vendor Sales by Category</a></li>
		<li><a href="report.profit_and_loss_report_by_sku_items.php"> Profit and Loss Report by SKU Items</a></li>
		<?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
	    <li><a href="report.quaterly_branch_sales_by_sku_category.php"> Quarterly Branch Sales by Category / SKU Items</a></li>
		<?php endif; ?>
		<li><a href="report.quaterly_sales_by_sku_category.php"> Quarterly Sales by Category / SKU Item</a></li> 	    <!--<li><a href="report.daily_sku_items_sales_by_second_last_category.php?view_type=yearly"> Monthly SKU Items Sales by Second Last Category</a></li>-->
		<li><a href="report.daily_sku_items_sales_by_second_last_category.php"> Sales by 4th Level Category</a></li>
	    <?php if ($this->_tpl_vars['BRANCH_CODE'] == 'HQ'): ?>
	    <li><a href="report.sku_item_sales_of_brand_comparison_by_branch.php"> SKU Items Sales of Brand Comparison by Branch</a></li>
	    <li><a href="report.sku_item_sales_of_vendor_comparison_by_branch.php"> SKU Items Sales of Vendor Comparison by Branch</a></li>
	    <?php endif; ?>
	    <li><a href="report.vendor_sales_ranking_comparison_by_branch.php"> Vendor Sales Ranking Comparison by Branch</a></li>
	</ul>
<?php endif; ?>
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['REPORTS_MEMBERSHIP']): ?>
<!-- Member -->
<li><a href="#" class=submenu><img src="/ui/icons/group.png" align=absmiddle border=0> Membership Report</a>
	<ul>
	<li><a href="/report.hourly_member_sales_comparison_by_month.php"> Hourly Member Sales Comparison by Month / Day</a></li>
	<li><a href="/report.member_sales_comparison_by_day.php"> Member Sales Comparison by Day</a></li>
	<!--<li><a href="/report.hourly_member_sales_comparison_by_month.php?view_type=day"> Hourly Member Sales Comparison by Day</a></li>-->
	<li><a href="/report.member_sales_ranking.php"> Member Sales Ranking</a></li>
	<li><a href="/report.member_transaction_history.php"> Member Transaction History</a></li>
	</ul>
<?php endif; ?>