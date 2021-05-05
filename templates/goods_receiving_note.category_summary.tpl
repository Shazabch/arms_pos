{*
10/14/2011 3:20:48 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/20/2014 10:37 AM Justin
- Enhanced to have export feature for itemise table.

6/4/2014 2:48 PM Justin
- Enhanced to use new method for export itemise into CSV.

4/24/2018 1:52 PM Justin
- Enhanced to show foreign currency.

06/24/2020 4:04 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

{if !$no_header_footer}

{if $config.do_credit_sales_show_sales_person_name or $config.do_cash_sales_show_sales_person_name}
	{assign var=show_sales_person_name value=1}
{/if}

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
</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';
var do_credit_sales_show_sales_person_name = '{$config.do_credit_sales_show_sales_person_name}';
var do_cash_sales_show_sales_person_name = '{$config.do_cash_sales_show_sales_person_name}';

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
	document.f.cat_id.value = root_id;
	//document.f.a.value = 'category';
	document.f.submit();
}

function expand_sub(root_id, indent, el, root_per){
	if(el.src.indexOf('clock')>0)   return;
	
	el.onClick='';
	el.src = '/ui/clock.gif';
	new Ajax.Request(phpself+'?'+Form.serialize(document.f)+"&a=ajax_load_category&ajax=1&cat_id="+root_id+"&indent="+indent+'&root_per='+root_per,
	{
		onComplete: function(e) {
			new Insertion.After($('r'+root_id), e.responseText);
			el.remove();
		},
	});
}

function show_sku(root_id, img){
    if(img.src.indexOf('clock')>0)   return;
	img.src = '/ui/clock.gif';
	
	$('show_sku').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('show_sku',phpself+'?'+Form.serialize(document.f)+"&a=ajax_load_sku&cat_id="+root_id,{
		evalScripts:true,
		onComplete: function(e){
			img.src = '/ui/icons/table.png';
		}
	});
}

function show_debtor(val){    
    // debtor list
    if(val=='credit_sales') $('div_debtor_list').show();
    else    $('div_debtor_list').hide();
}

function export_itemise_info(root_id){
	document.f.is_itemise_export.value=1;
	document.f.itemise_cat_id.value=root_id;
	document.f.submit();

	document.f.is_itemise_export.value=0;
	document.f.itemise_cat_id.value=0;
}
{/literal}
</script>

{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
<div class="noprint stdframe">
<form name="f" method="get">
<input type="hidden" name="subm" value="1" />
<input type="hidden" name="cat_id" value="{$smarty.request.cat_id}" />

	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
		<option value=''>-- All --</option>
		{foreach from=$branches key=bid item=r}
			<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
		{/foreach}
		</select>&nbsp;&nbsp;
	{/if}

	<b>SKU Type</b>
	<select name="sku_type">
	    <option value="">-- All --</option>
	    {foreach from=$sku_type item=r}
	        <option value="{$r.code}" {if $r.code eq $smarty.request.sku_type}selected {/if}>{$r.code}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;
	
	<b>Status</b>
	<select name="status">
	    <option value="" {if !$smarty.request.status}selected {/if}>-- All --</option>
	    <option value="1" {if $smarty.request.status eq 1}selected {/if}>Approved</option>
	    <option value="0" {if $smarty.request.status eq '0'}selected {/if}>Not Approved</option>
	</select>&nbsp;&nbsp;
	
	<b>Date From</b>
	<input type="text" name="from" value="{$smarty.request.from}" id="inp_date_from" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
	<b>To</b>
	<input type="text" name="to" value="{$smarty.request.to}" id="inp_date_to" readonly="1" size=12 />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;

 	<input class="btn btn-primary" type="submit" value='Show Report' /> &nbsp;&nbsp;

	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button class="btn btn-primary" name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
	
	{if $config.foreign_currency}
		<p>
			* {$LANG.BASE_CURRENCY_CONVERT_NOTICE}
		</p>
	{/if}
</form>
</div>
{/if}

<h3>{$report_title}</h3>

{if !$no_header_footer}
    <p>
		&#187; <a href="javascript:void(show_sub(0));">ROOT</a> /
		{if $cat_info}
		    {foreach from=$cat_info.cat_tree_info item=ct}
		        <a href="javascript:void(show_sub('{$ct.id}'));">{$ct.description}</a> /
		    {/foreach}
		    {$cat_info.description} /
		{/if}
		<br /><font class="small">Click on a sub-category for further detail. Click <img src="/ui/icons/table.png" align="absmiddle" />  to display SKU in the category.</font>
	</p>
{/if}

{if !$tb}
	{if $smarty.request.subm}No Data{/if}
{else}
<table class="sortable tb" cellspacing="0" cellpadding="2" border="0">
    <tr>
		<th align="left" rowspan="2">&nbsp;</th>
		{assign var=lasty value=0}
		{assign var=lastm value=0}
		{foreach from=$uq_cols key=dt item=d}
		    <th valign="bottom" colspan="2">
				{if $lastm ne $d.m or $lasty ne $d.y}
					<span class="small">{$d.m|string_format:'%02d'}/{$d.y}</span><br />
					{assign var=lastm value=$d.m}
					{assign var=lasty value=$d.y}
				{/if}
				{$d.d}
			</th>
		{/foreach}
		<th rowspan="2">T.Sell</th>
		{if $is_under_gst}
			<th rowspan="2">T.GST</th>
			<th rowspan="2">T.Sell<br />Inc. GST</th>
		{/if}
		{if $sessioninfo.show_cost}
			<th rowspan="2">T.Cost</th>
		{/if}
		{if $sessioninfo.show_report_gp}
			<th rowspan="2">GP</th>
			<th rowspan="2">GP(%)</th>
		{/if}
	</tr>

	<tr>
		{foreach from=$uq_cols key=dt item=d}
			<th>T.Sell</th>
			{if $sessioninfo.show_cost}
				<th>T.Cost</th>
			{/if}
		{/foreach}
	</tr>

	{include file='goods_receiving_note.category_summary.table.tpl'}
	
	<tr class=sortbottom>
		<th align="right">Total</th>

		{foreach from=$uq_cols key=dt item=d}
			{capture assign=tooltip}
				Qty:{$tb_total.data.$dt.qty|qty_nf}  /  Selling:{$tb_total.data.$dt.sell|string_format:'%.2f'}  /  Cost:{$tb_total.data.$dt.cost|string_format:'%.2f'}
			{/capture}
			<td class="small" align="right" title="{$tooltip}">{$tb_total.data.$dt.sell|number_format:2}</td>
			{if $sessioninfo.show_cost}
				<td class="small {if $tb_total.data.$dt.have_fc}converted_base_amt{/if}" align="right" title="{$tooltip}">{$tb_total.data.$dt.cost|number_format:2}{if $tb_total.data.$dt.have_fc}*{/if}</td>
			{/if}
		{/foreach}

		<td class="small" align="right">{$tb_total.data.total.sell|number_format:2}</td>
		{if $is_under_gst}
			{assign var=ttl_gst value=$tb_total.data.total.gst_sell-$tb_total.data.total.sell}
			<td class="small" align="right">{$ttl_gst|number_format:2}</td>
			<td class="small" align="right">{$tb_total.data.total.gst_sell|number_format:2}</td>
		{/if}
		{if $sessioninfo.show_cost}
			<td class="small {if $tb_total.data.total.have_fc}converted_base_amt{/if}" align="right">{$tb_total.data.total.cost|number_format:2}{if $tb_total.data.total.have_fc}*{/if}</td>
		{/if}
		{if $sessioninfo.show_report_gp}
			{assign var=gp value=$tb_total.data.total.sell-$tb_total.data.total.cost}
			<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp|number_format:2}</td>
			{if $tb_total.data.total.sell>0}
				{assign var=gp_per value=$gp/$tb_total.data.total.sell*100}
			{else}
				{assign var=gp_per value=0}
			{/if}
			<td class="small" align="right" {if $gp < 0}style="color:red;"{/if}>{$gp_per|number_format:2}</td>
		{/if}
	</tr>
</table>

<div id="show_sku"></div>
{/if}

{include file='footer.tpl'}


<script>
init_calendar();
</script>
