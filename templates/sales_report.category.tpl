{*
9/17/2010 2:29:27 PM Andy
- Fix report to prevent user from triggle sub category or sku multiple time.

10/29/2010 4:19:02 PM Alex
- add show cost and show report gp privilege

11/24/2010 11:45:12 AM Andy
- Add option to let user choose whether use report server or not.

11/29/2010 12:42:27 PM Andy
- Remove all fresh market items from this report.
- Show a new row of fresh market amount if "sales amount" is choose.

12/15/2010 10:32:36 AM Andy
- Change "use report server" checkbox only available to system admin.

2/15/2011 9:59:46 AM Andy
- Reconstruct daily category sales report to show fresh market data.

7/6/2011 5:18:49 PM Andy
- Fix un-category sales missing from report.
- Fix item direct under category cannot show sku details.

9/8/2011 3:13:23 PM Andy
- Add show transaction count and buying power if show report by using top category or line.

9/21/2011 2:11:51 PM Andy
- Fix transaction count column not match when show sub-category.

10/14/2011 11:53:11 AM Alex
- change qty use value_format:'qty'

3/27/2012 3:08:13 PM Justin
- Enhanced existing Branch filter to accept "Branch Region" filter.

2/4/2013 5:34 PM Justin
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/2/2014 5:27 PM Justin
- Enhanced to show the total of Mix & Match, Credit & Cash Sales DO and Grand Total row.

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

6/5/2014 11:54 AM Justin
- Bug fixed of some info were missing after new method applied.

4:54 PM 11/27/2014 Andy
- Enhance to show Service Charges and GST.
- The report no longer show mix & match discount when found sales cache data contain discount2 value.
- Bring the grand total calculation to php instead of calculate in tpl.
- Enhance when report is sorting, the "Total" and "Grand Total" will always stick to the bottom.
- Enhance the report to able to show by GST Amount or Sales Amount Included GST.

5/27/2015 11:52 AM Justin
- Enhanced to show different label for DO when Transfer DO is allowed to show while config is enabled.

1/18/2017 10:40 AM Andy
- Fixed failed to Export DO Items.

6/21/2017 2:22 PM Justin
- Enhanced to comment out the Mix & Match Total feature.

12/6/2017 5:20 PM Andy
- Enhanced to able to filter by member, disable if got config sku_report_sales_cache_no_member_data.

5/4/2018 5:49 PM Andy
- Fixed the report always show no data if no POS data found.

5/21/2018 4:30 pm Kuan Yeh
- Bug fixed of calendar method shown on excel export  

6/7/2019 1:27 PM William
- Added two column Gross sales and Discount.

6/26/2019 9:18 AM William
- tpl file calculate gross amount change to use php calculate.

06/30/2020 10:57 AM Sheila
- Updated button css.

7/17/2020 10:40 AM William
- Remove character "%" on the row GP(%).

10/15/2020 9:00 AM William
- Change GST word to use Tax.
- Added new config "enable_tax" checking.
*}

{include file='header.tpl'}

{if !$no_header_footer}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
#show_sku {
	padding:10px 0;
}
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.fm_data{
    background-color: #fcf;
}

.use_grn_cost{
	background-color: #fcf;
}
</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';
var show_tran_count = int('{$show_tran_count}');

{literal}
function init_calendar(){
    Calendar.setup({
        inputField     :    "inp_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_date_from",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "inp_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_date_to",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
}

function show_sub(root_id){
	document.f.cat_id.value = root_id
	//document.f.a.value = 'category';
	document.f.submit();
}

function expand_sub(root_id, indent, el, root_per, is_fresh_market){
	if(el.src.indexOf('clock')>0)   return;
	
	el.onClick='';
	el.src = '/ui/clock.gif';
	new Ajax.Request(phpself+'?'+Form.serialize(document.f)+"&a=ajax_load_category&ajax=1&cat_id="+root_id+"&indent="+indent+'&root_per='+root_per+'&is_fresh_market='+is_fresh_market+'&show_tran_count='+show_tran_count,
	{
		onComplete: function(e) {
		    if(is_fresh_market){
                new Insertion.After($('tbody_fm_row-'+root_id), e.responseText);
			}else{
                new Insertion.After($('tbody_cat_row-'+root_id), e.responseText);
			}
			
			el.remove();
		},
	});
}

function show_sku(root_id, img, is_fresh_market, direct_under_cat){
    if(img.src.indexOf('clock')>0)   return;
	img.src = '/ui/clock.gif';
	
	$('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	var params = Form.serialize(document.f)+"&a=ajax_load_sku&cat_id="+root_id+'&is_fresh_market='+is_fresh_market;
	if(direct_under_cat)	params += '&direct_under_cat=1';
	
	new Ajax.Updater('show_sku',phpself,{
		parameters: params,
		evalScripts:true,
		onComplete: function(e){
			img.src = '/ui/icons/table.png';
		}
	});
}

function toggle_fm_row(ele){
	var show = (ele.src.indexOf('expand')>=0) ? true : false;
	
	$$('#tbl_cat tr.is_fresh_market_row').each(function(r){
		if(show)    $(r).show();
		else    $(r).hide();
	});
	
	if(show)  ele.src = '/ui/collapse.gif';
	else    ele.src = '/ui/expand.gif';
}

function show_do_sku(img){
    if(img.src.indexOf('clock')>0)   return;
	img.src = '/ui/clock.gif';
	
	$('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	var params = Form.serialize(document.f)+"&a=ajax_load_do_sku";
	
	new Ajax.Updater('show_sku',phpself,{
		parameters: params,
		evalScripts:true,
		onComplete: function(e){
			img.src = '/ui/icons/table.png';
		}
	});
}

function export_itemise_info(itemise_type, root_id, is_fresh_market, direct_under_cat){
	document.f.itemise_type.value=itemise_type;
	document.f.is_itemise_export.value=1;
	document.f.itemise_cat_id.value=root_id;
	document.f.itemise_is_fresh_market.value=is_fresh_market;
	document.f.itemise_direct_under_cat.value=direct_under_cat;
	document.f.submit();

	document.f.is_itemise_export.value=0;
	document.f.itemise_cat_id.value=0;
	document.f.itemise_is_fresh_market.value=0;
	document.f.itemise_direct_under_cat.value=0;
}
{/literal}
</script>

{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
	{if $config.membership_module and !$config.sku_report_sales_cache_no_member_data}
		{assign var=show_member_filter value=1}
	{/if}
<div class="noprint stdframe">
<form name="f" method="get">
<input type="hidden" name="subm" value="1" />
<input type="hidden" name="is_itemise_export" value="0" />
<input type="hidden" name="itemise_cat_id" value="0" />
<input type="hidden" name="itemise_type" value="" />
<input type="hidden" name="itemise_is_fresh_market" value="0" />
<input type="hidden" name="itemise_direct_under_cat" value="0" />
<input type="hidden" name="cat_id" value="{$smarty.request.cat_id}" />

	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
		<option value=''>-- All --</option>
		{foreach from=$branches key=bid item=r}
		
			{if $config.sales_report_branches_exclude}
			{if in_array($r.code,$config.sales_report_branches_exclude)}
			{assign var=skip_this_branch value=1}
			{else}
			{assign var=skip_this_branch value=0}
			{/if}
			{/if}
		
			{if !$branches_group.have_group.$bid and !$skip_this_branch}
				<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
			{/if}
		{/foreach}
		{if $branches_group.header}
		    <optgroup label='Branch Group'>
			{foreach from=$branches_group.header key=bgid item=bg}
		    	    <option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
		    	    {foreach from=$branches_group.items.$bgid item=r}
					
						{if $config.sales_report_branches_exclude}
						{if in_array($r.code,$config.sales_report_branches_exclude)}
						{assign var=skip_this_branch value=1}
						{else}
						{assign var=skip_this_branch value=0}
						{/if}
						{/if}
					
						{if !$skip_this_branch}
		    	        <option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/if}
						
		    	    {/foreach}
		    	{/foreach}
			</optgroup>
		{/if}
		{if $config.consignment_modules && $config.masterfile_branch_region}
		    <optgroup label='Region'>
			{foreach from=$config.masterfile_branch_region key=type item=f}
				{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
					{assign var=curr_type value="REGION_`$type`"}
					<option value="REGION_{$type}" {if $smarty.request.branch_id eq $curr_type}selected {/if}>{$type|upper}</option>
				{/if}
		    {/foreach}
			</optgroup>
		{/if}
		</select>&nbsp;&nbsp;
	{/if}

     <b>SKU Type</b>
	<select name="sku_type">
	    <option value="">-- All --</option>
	    {foreach from=$sku_type item=r}
	        <option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;
	
	<b>Date From</b>
	<input type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b>
	<input type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
	
	<input type="checkbox" name="by_monthly" value="1" {if $smarty.request.by_monthly}checked {/if} />
	<b>Group Monthly</b>
    &nbsp;&nbsp;
    {if $sessioninfo.level>=9999}
		<input type="checkbox" name="use_report_server" value="1" {if $smarty.request.use_report_server or !$smarty.request.subm}checked {/if} />
		<b>Use report server</b>
	{else}
	    <input type="hidden" name="use_report_server" value="1" />
	{/if}
	<br />
	
	{if $show_member_filter}
		<span>
			<b>Member </b>
			<select name="memb_filter">
				<option value="">-- All --</option>
				<option value="member" {if $smarty.request.memb_filter eq 'member'}selected {/if}>Member Only</option>
				<option value="non_member" {if $smarty.request.memb_filter eq 'non_member'}selected {/if}>Non-Member Only</option>
			</select>
		</span>
		<br />
	{/if}
	
	<b>Report Type: </b>
	<input type="radio" value="qty" name="report_type" {if $smarty.request.report_type eq 'qty' or !$smarty.request.report_type}checked {/if} /> Sales Qty &nbsp;&nbsp;
	<input type="radio" value="amt" name="report_type" {if $smarty.request.report_type eq 'amt'}checked {/if} /> Sales Amount &nbsp;&nbsp;
	{if $config.enable_gst || $config.enable_tax}
		<input type="radio" value="gst_amt" name="report_type" {if $smarty.request.report_type eq 'gst_amt'}checked {/if} /> Tax Amount &nbsp;&nbsp;
		<input type="radio" value="amt_inc_gst" name="report_type" {if $smarty.request.report_type eq 'amt_inc_gst'}checked {/if} /> Sales Amount Included Tax &nbsp;&nbsp;
	{/if}
	{if $sessioninfo.show_report_gp}
	<input type="radio" value="gp" name="report_type" {if $smarty.request.report_type eq 'gp'}checked {/if} /> Gross Profit &nbsp;&nbsp;
	<input type="radio" value="gp_pct" name="report_type" {if $smarty.request.report_type eq 'gp_pct'}checked {/if} /> GP (%) &nbsp;&nbsp;
	{/if}
 	<input class="btn btn-primary" type="submit" value='Show Report' /> &nbsp;&nbsp;

	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button class="btn btn-primary" name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
		{if $root_cat_info.level >= 2}
			<input type="checkbox" name="include_sub_cat" value="1" {if $smarty.request.include_sub_cat}checked {/if} />
			Export with all sub-category
		{/if}
	{/if}
</form>
</div>
{/if}

<h3>{$report_title}</h3>

{if !$no_header_footer}
    <p>
		&#187; <a href="javascript:void(show_sub(0));">ROOT</a> /
		{if $root_cat_info}
		    {foreach from=$root_cat_info.cat_tree_info item=ct}
		        <a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a> /
		    {/foreach}
		    {$root_cat_info.description} /
		{/if}
		<ul>
			<li>Click on a sub-category for further detail. Click <img src="/ui/icons/table.png" align="absmiddle" />  to display SKU in the category.</li>
			<li>Transaction count can only view at line/department.</li>
			<li>The total of Mix & Match and {if $config.sales_report_include_transfer_do}Transfer,{/if} Credit & Cash Sales DO shows out only while in category "Line" position.</li>
			<li>The total of Mix & Match will not affected by SKU Type filter.</li>
			<li>In later counter revision, the Mix & Match and Receipt Total Discount will directly reduce the sales amount, thus the Mix & Match Discount Row will not be shown. </li>
		</ul>
	</p>
{/if}

{if !$got_data}
	{if $smarty.request.subm}No Data{/if}
{else}
<table class="tb sortable" cellspacing="0" cellpadding="2" border="0" id="tbl_cat">
    <tr>
		<th align="left">&nbsp;</th>

		{assign var=lasty value=0}
		{assign var=lastm value=0}
		{foreach from=$uq_cols key=dt item=d}
		    <th valign="bottom">
			{if $smarty.request.by_monthly}
				{if $lasty ne $d.y}
					<span class="small">{$d.y}</span><br />
					{assign var=lasty value=$d.y}
				{/if}
				{$d.m|str_month|truncate:3:''}
			{else}
				{if $lastm ne $d.m or $lasty ne $d.y}
				    <span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
				    {assign var=lastm value=$d.m}
					{assign var=lasty value=$d.y}
				{/if}
				{$d.d}
			{/if}
			</th>
		{/foreach}
		<th>Total<br />Qty</th>
		<th>Gross Amount</th>
		<th>Discount</th>
		<th>Amount</th>
		
		{if $config.enable_gst || $config.enable_tax}
			<th>Tax</th>
			<th>Amt Inc Tax</th>
		{/if}
		
		{if $show_tran_count}
			<th>Tran.<br />Count</th>
			<th>Buying<br />Power</th>
		{/if}
		
		{if $sessioninfo.show_cost}
			<th>Cost</th>
		{/if}
		{if $sessioninfo.show_report_gp}
			<th>GP</th>
			<th>GP(%)</th>
		{/if}
		<th>Contrib<br>(%)</th>
	</tr>

	{include file='sales_report.category.table.tpl' curr_cat_info=$root_cat_info}
	{assign var=curr_root_cat_id value=$root_cat_info.id}
	
	<tr class="sortbottom" style="background-color:#dcdcdc;">
		<th align="right">Total</th>
		
		{foreach from=$uq_cols key=dt item=d}
			{assign var=fmt value="%0.2f"}
			{if $smarty.request.report_type eq 'qty'}
				{assign var=fmt value="qty"}
				{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.qty}
			{elseif $smarty.request.report_type eq 'amt'}
				{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.amt}
			{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
				{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.amt-$tb_total.$curr_root_cat_id.total.$dt.cost}
			{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
				{assign var=fmt value="%0.2f"}
				{if $tb_total.$curr_root_cat_id.total.$dt.amt eq 0}
					{assign var=val value=''}
				{else}
				    {assign var=gp value=$tb_total.$curr_root_cat_id.total.$dt.amt-$tb_total.$curr_root_cat_id.total.$dt.cost}
					{assign var=val value=$gp/$tb_total.$curr_root_cat_id.total.$dt.amt*100}
				{/if}
			{elseif $smarty.request.report_type eq 'gst_amt'}
				{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.tax_amount}
			{elseif $smarty.request.report_type eq 'amt_inc_gst'}
				{assign var=val value=$tb_total.$curr_root_cat_id.total.$dt.amt_inc_gst}
			{/if}
			{capture assign=tooltip}
				Qty:{$tb_total.$curr_root_cat_id.total.$dt.qty|value_format:'qty'}  /  Amt:{$tb_total.$curr_root_cat_id.total.$dt.amt|string_format:'%.2f'}  /  Cost:{$tb_total.$curr_root_cat_id.total.$dt.cost|string_format:'%.3f'} / Tax:{$tb_total.$curr_root_cat_id.total.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$tb_total.$curr_root_cat_id.total.$dt.amt_inc_gst|string_format:'%.2f'}
			{/capture}
			<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
		{/foreach}

		<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.qty|value_format:'qty'}</td>
		<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.gross_amt|value_format:'%0.2f'|default:''}</td>
		<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.discount|value_format:'%0.2f'|default:''}</td>
		<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.amt|value_format:'%0.2f'}</td>
		
		{if $config.enable_gst || $config.enable_tax}
			{* GST *}
			<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.tax_amount|value_format:'%0.2f'}</td>
			
			{* Amt Inc GST *}
			<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.amt_inc_gst|value_format:'%0.2f'}</td>
		{/if}
		
		{if $show_tran_count}
			<td align="right">-</td><td align="right">-</td>
		{/if}
		{if $sessioninfo.show_cost}
			<td class="small" align="right">{$tb_total.$curr_root_cat_id.total.total.cost|value_format:'%0.2f'}</td>
		{/if}
		
		{if $sessioninfo.show_report_gp}
			{assign var=gp value=$tb_total.$curr_root_cat_id.total.total.amt-$tb_total.$curr_root_cat_id.total.total.cost}
			<td class="small" align="right">{$gp|value_format:"%0.2f"}</td>
			{if $tb_total.$curr_root_cat_id.total.total.amt>0}
			    {assign var=gp_per value=$gp/$tb_total.$curr_root_cat_id.total.total.amt*100}
			{else}
			    {assign var=gp_per value=0}
			{/if}
			<td class="small" align="right">{$gp_per|value_format:'%0.2f':'-'}</td>
		{/if}
	</tr>
	
	{if !$curr_root_cat_id}
		<!-- show mix & match discount amount -->
		{*if $mm_tb_total}
			<tr>
				<th align="right">Mix & Match Total</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'amt'}
						{assign var=val value=$mm_tb_total.$dt.amt*-1}
					{else}
						{assign var=val value=""}
					{/if}
					{capture assign=tooltip}Amt:{$mm_tb_total.$dt.amt*-1|string_format:'%.2f'}{/capture}
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">-</td>
				<td class="small" align="right">{$mm_tb_total.amt*-1|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					<td class="small" align="right">-</td>
					<td>&nbsp;</td>
				{/if}
		
				{if $show_tran_count}
					<td class="small" align="right">{$mm_tb_total.trans_count|value_format:'qty':'-'}</td><td align="right">-</td>
				{/if}
				{if $sessioninfo.show_cost}
					<td class="small" align="right">-</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					<td class="small" align="right">{$mm_tb_total.amt*-1|value_format:$fmt:'-'}</td>
					<td class="small" align="right">-</td>
				{/if}
			</tr>
		{/if*}
		
		<!-- show DO cash & credit sales -->
		{if $do_tb_total}
			<tr>
				<th align="right">
					{if !$no_header_footer}
						<img src="/ui/icons/table.png" align="absmiddle" onclick="show_do_sku(this);" title="Show SKU" /> 
					{/if}
					{if !$config.sales_report_include_transfer_do}Credit & Cash Sales{/if} DO Total
				</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'qty'}
						{assign var=fmt value="qty"}
						{assign var=val value=$do_tb_total.$dt.qty}
					{elseif $smarty.request.report_type eq 'amt'}
						{assign var=val value=$do_tb_total.$dt.amt}
					{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
						{assign var=val value=$do_tb_total.$dt.amt-$do_tb_total.$dt.cost}
					{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
						{assign var=fmt value="%0.2f"}
						{if $do_tb_total.$dt.amt eq 0}
							{assign var=val value=''}
						{else}
							{assign var=gp value=$do_tb_total.$dt.amt-$do_tb_total.$dt.cost}
							{assign var=val value=$gp/$do_tb_total.$dt.amt*100}
						{/if}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$do_tb_total.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$do_tb_total.$dt.amt_inc_gst}
					{/if}
					{capture assign=tooltip}
						Qty:{$do_tb_total.$dt.qty|value_format:'qty'}  /  Amt:{$do_tb_total.$dt.amt|string_format:'%.2f'}  /  Cost:{$do_tb_total.$dt.cost|string_format:'%.3f'} / Tax:{$do_tb_total.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$do_tb_total.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">{$do_tb_total.qty|value_format:'qty':'-'}</td>
				<td class="small" align="right">{$do_tb_total.gross_amt|value_format:'%0.2f':'-'}</td>
				<td class="small" align="right">{$do_tb_total.discount|value_format:'%0.2f':'-'}</td>
				<td class="small" align="right">{$do_tb_total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">{$do_tb_total.tax_amount|value_format:'%0.2f':'-'}</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$do_tb_total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $show_tran_count}
					<td align="right">-</td><td align="right">-</td>
				{/if}
				{if $sessioninfo.show_cost}
					<td class="small" align="right">{$do_tb_total.cost|value_format:'%0.2f':'-'}</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					{assign var=gp value=$do_tb_total.amt-$do_tb_total.cost}
					<td class="small" align="right">{$gp|value_format:"%0.2f":'-'}</td>
					{if $do_tb_total.amt>0}
						{assign var=gp_per value=$gp/$do_tb_total.amt*100}
					{else}
						{assign var=gp_per value=0}
					{/if}
					<td class="small" align="right">{$gp_per|value_format:'%0.2f':'-'}</td>
				{/if}
			</tr>
		{/if}
		
		{* Service Charge *}
		{if $sc_data}
			<tr>
				<th align="right">Service Charge</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'amt'}
						{assign var=val value=$sc_data.data.$dt.amt}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$sc_data.data.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$sc_data.data.$dt.amt_inc_gst}
					{else}
						{assign var=val value=""}
					{/if}
					{capture assign=tooltip}
						Amt:{$sc_data.data.$dt.amt|string_format:'%.2f'}  /  Tax:{$sc_data.data.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$sc_data.data.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">{$sc_data.total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">{$sc_data.total.tax_amount|value_format:'%0.2f':'-'}</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$sc_data.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $show_tran_count}
					<td class="small" align="right">-</td><td align="right">-</td>
				{/if}
				
				{if $sessioninfo.show_cost}
					<td class="small" align="right">-</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					<td class="small" align="right">{$sc_data.total.amt|value_format:$fmt:'-'}</td>
					<td class="small" align="right">-</td>
				{/if}
			</tr>
		{/if}
		
		{* Deposit Received *}
		{if $dp_rcv_data}
			<tr>
				<th align="right">Deposit (Received)</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'amt'}
						{assign var=val value=$dp_rcv_data.data.$dt.amt}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$dp_rcv_data.data.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$dp_rcv_data.data.$dt.amt_inc_gst}
					{else}
						{assign var=val value=""}
					{/if}
					{capture assign=tooltip}
						Amt:{$dp_rcv_data.data.$dt.amt|string_format:'%.2f'}  /  Tax:{$dp_rcv_data.data.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$dp_rcv_data.data.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">{$dp_rcv_data.total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">{$dp_rcv_data.total.tax_amount|value_format:'%0.2f':'-'}</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$dp_rcv_data.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $show_tran_count}
					<td class="small" align="right">-</td><td align="right">-</td>
				{/if}
				
				{if $sessioninfo.show_cost}
					<td class="small" align="right">-</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					<td class="small" align="right">{$dp_rcv_data.total.amt|value_format:$fmt:'-'}</td>
					<td class="small" align="right">-</td>
				{/if}
			</tr>
		{/if}
		
		{* Deposit Used *}
		{if $dp_used_data}
			<tr>
				<th align="right">Deposit (Used)</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'amt'}
						{assign var=val value=$dp_used_data.data.$dt.amt}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$dp_used_data.data.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$dp_used_data.data.$dt.amt_inc_gst}
					{else}
						{assign var=val value=""}
					{/if}
					{capture assign=tooltip}
						Amt:{$dp_used_data.data.$dt.amt|string_format:'%.2f'}  /  Tax:{$dp_used_data.data.$dt.tax_amount|string_format:'%.2f'} / Amt Inc Tax:{$dp_used_data.data.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">{$dp_used_data.total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">{$dp_used_data.total.tax_amount|value_format:'%0.2f':'-'}</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$dp_used_data.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $show_tran_count}
					<td class="small" align="right">-</td><td align="right">-</td>
				{/if}
				
				{if $sessioninfo.show_cost}
					<td class="small" align="right">-</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					<td class="small" align="right">{$dp_used_data.total.amt|value_format:$fmt:'-'}</td>
					<td class="small" align="right">-</td>
				{/if}
			</tr>
		{/if}
		
		{* Rounding *}
		{if $rounding_data}
			<tr>
				<th align="right">Rounding</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'amt'}
						{assign var=val value=$rounding_data.data.$dt.amt}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$rounding_data.data.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$rounding_data.data.$dt.amt_inc_gst}
					{else}
						{assign var=val value=""}
					{/if}
					{capture assign=tooltip}
						Amt:{$rounding_data.data.$dt.amt|string_format:'%.2f'}  / Amt Inc Tax:{$rounding_data.data.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">{$rounding_data.total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">-</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$rounding_data.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $show_tran_count}
					<td class="small" align="right">-</td><td align="right">-</td>
				{/if}
				
				{if $sessioninfo.show_cost}
					<td class="small" align="right">-</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					<td class="small" align="right">{$rounding_data.total.amt|value_format:$fmt:'-'}</td>
					<td class="small" align="right">-</td>
				{/if}
			</tr>
		{/if}
		
		{* Over *}
		{if $over_data}
			<tr>
				<th align="right">Over</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					{if $smarty.request.report_type eq 'amt'}
						{assign var=val value=$over_data.data.$dt.amt}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$over_data.data.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$over_data.data.$dt.amt_inc_gst}
					{else}
						{assign var=val value=""}
					{/if}
					{capture assign=tooltip}
						Amt:{$over_data.data.$dt.amt|string_format:'%.2f'}  / Amt Inc Tax:{$over_data.data.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">-</td>
				<td class="small" align="right">{$over_data.total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">-</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$over_data.total.amt_inc_gst|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $show_tran_count}
					<td class="small" align="right">-</td><td align="right">-</td>
				{/if}
				
				{if $sessioninfo.show_cost}
					<td class="small" align="right">-</td>
				{/if}
				{if $sessioninfo.show_report_gp}
					<td class="small" align="right">{$over_data.total.amt|value_format:$fmt:'-'}</td>
					<td class="small" align="right">-</td>
				{/if}
			</tr>
		{/if}
		
		<!-- grand total -->
		{if $tb_grand_total}
			<tr class="sortbottom" style="background-color:#dcdcdc;">
				<th align="right">Grand Total</th>
				{foreach from=$uq_cols key=dt item=d}
					{assign var=fmt value="%0.2f"}
					
					{if $smarty.request.report_type eq 'qty'}
						{assign var=fmt value="qty"}
						{assign var=val value=$tb_grand_total.$dt.qty}
					{elseif $smarty.request.report_type eq 'amt'}
						{assign var=val value=$tb_grand_total.$dt.amt}
					{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp'}
						{assign var=val value=$tb_grand_total.$dt.amt-$tb_grand_total.$dt.cost}
					{elseif $sessioninfo.show_report_gp && $smarty.request.report_type eq 'gp_pct'}
						{assign var=fmt value="%0.2f"}
						{if $tb_grand_total.$dt.amt eq 0}
							{assign var=val value=''}
						{else}
							{assign var=gp value=$tb_grand_total.$dt.amt-$tb_grand_total.$dt.cost}
							{assign var=val value=$gp/$tb_grand_total.$dt.amt*100}
						{/if}
					{elseif $smarty.request.report_type eq 'gst_amt'}
						{assign var=val value=$tb_grand_total.$dt.tax_amount}
					{elseif $smarty.request.report_type eq 'amt_inc_gst'}
						{assign var=val value=$tb_grand_total.$dt.amt_inc_gst}
					{/if}
					{capture assign=tooltip}
						Qty:{$tb_grand_total.$dt.qty|value_format:'qty'}  /  Amt:{$tb_grand_total.$dt.amt|string_format:'%.2f'}  /  Cost:{$tb_grand_total.$dt.cost|string_format:'%.3f'}  /  Tax:{$tb_grand_total.$dt.tax_amount|string_format:'%.2f'}/ Amt Inc Tax:{$tb_grand_total.$dt.amt_inc_gst|string_format:'%.2f'}
					{/capture}
					
					<td class="small" align="right" title="{$tooltip}">{$val|value_format:$fmt}</td>
				{/foreach}
				
				<td class="small" align="right">{$tb_grand_total.total.qty|value_format:'qty':'-'}</td>
				{assign var=grand_total_gross_amount value=$tb_grand_total.total.amt+$tb_grand_total.total.discount}
				<td class="small" align="right">{$grand_total_gross_amount|value_format:'%0.2f'|default:''}</td>
				<td class="small" align="right">{$tb_grand_total.total.discount|value_format:'%0.2f'|default:''}</td>
				<td class="small" align="right">{$tb_grand_total.total.amt|value_format:'%0.2f':'-'}</td>
				
				{if $config.enable_gst || $config.enable_tax}
					{* GST *}
					<td class="small" align="right">{$tb_grand_total.total.tax_amount|string_format:'%0.2f'}</td>
					
					{* Amt Inc GST *}
					<td class="small" align="right">{$tb_grand_total.total.amt_inc_gst|string_format:'%0.2f'}</td>
				{/if}
				
				{if $show_tran_count}
					<td align="right">-</td><td align="right">-</td>
				{/if}
				
				{if $sessioninfo.show_cost}
					<td class="small" align="right">{$tb_grand_total.total.cost|value_format:'%0.2f':'-'}</td>
				{/if}
				
				{if $sessioninfo.show_report_gp}
					{assign var=gp value=$tb_grand_total.total.amt-$tb_grand_total.total.cost}
					<td class="small" align="right">{$gp|value_format:"%0.2f":'-'}</td>
					
					{if $tb_grand_total.total.amt>0}
						{assign var=gp_per value=$gp/$tb_grand_total.total.amt*100}
					{else}
						{assign var=gp_per value=0}
					{/if}
					<td class="small" align="right">{$gp_per|value_format:'%0.2f':'-'}</td>
				{/if}
			</tr>
		{/if}
	{/if}
</table>

<div id="show_sku"></div>
{/if}

{include file='footer.tpl'}

{if !$no_header_footer}
	<script>

		{literal}
			init_calendar();

		{/literal}

	</script>
{/if}