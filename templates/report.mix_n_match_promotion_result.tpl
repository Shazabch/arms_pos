{*
11/11/2011 5:17:46 PM Andy
- Fix export excel not working.

06/30/2020 04:00 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}

{if !$no_header_footer}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function reload_promo_list(){
	var bid = document.f_a['branch_id'].value;
	var y = document.f_a['year'].value;
	var m = document.f_a['month'].value;
	var promo_filter = document.f_a['promo_filter'].value;
	if(!bid || !y || !m){
		$('div_promo_list').update('- No Promotion Found -');
		return false;
	}
	
	var params = {
		a: 'ajax_load_promo_list',
		branch_id: bid,
		year:y,
		month:m,
		promo_filter: promo_filter
	};
	
	$('div_promo_list').update(_loading_);
	new Ajax.Updater('div_promo_list', phpself, {
		parameters:	params,
		onComplete:{
			// do something
		}
	});
}

function show_report(type){
	var checked_count = 0;
	var chx = $(document.f_a).getElementsBySelector('input[name="branch_promo_id[]"]');
	if(!chx){
		alert('Please search promotion first');
		return false;
	}
	
	for(var i=0; i<chx.length; i++){
		if(chx[i].checked){
			checked_count++;
			break;
		}
	}
	//alert(checked_count);
	if(!checked_count){
		alert('Please search and select at least 1 promotion.');
		return false;
	}
	
	document.f_a['export_excel'].value = 0;
	
	if(type=='export_excel')	document.f_a['export_excel'].value = 1;
	
	document.f_a.submit();
}

function toggle_branch_promo_id(ele){
	var c = ele.checked;
	
	$(document.f_a).getElementsBySelector('input[name="branch_promo_id[]"]').each(function(inp){
		inp.checked = c;
	});
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
<form name="f_a" method="post" class="form" onSubmit="return false;">
	<input type="hidden" name="load_report" value="1" />
	<input type="hidden" name="export_excel" />
	
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch:</b>
		<select name="branch_id" onChange="reload_promo_list();">
			<option value="">-- Please Select --</option>
			{foreach from=$branches key=bid item=b}
				<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{else}
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
	{/if}
	
	<b>Year:</b>
	<select name="year" onChange="reload_promo_list();">
		<option value="">-- Please Select --</option>
		{foreach from=$year_list item=y}
			<option value="{$y}" {if $smarty.request.year eq $y}selected {/if}>{$y}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Month:</b>
	<select name="month" onChange="reload_promo_list();">
		<option value="">-- Please Select --</option>
		{foreach from=$months key=m item=m_label}
			<option value="{$m}" {if $smarty.request.month eq $m}selected {/if}>{$m_label}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>Promotion Filter:</b>
	<select name="promo_filter" onChange="reload_promo_list();">
		<option value="">-- All --</option>
		{foreach from=$promo_filter_list key=k item=f}
			<option value="{$k}" {if $smarty.request.promo_filter eq $k}selected {/if}>{$f}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<br />
	<fieldset style="width:700px;">
		<legend>Promotion</legend>
		<div style="max-height:300px;overflow-y:auto;" id="div_promo_list">
			{include file='report.mix_n_match_promotion_result.promotion_list.tpl'}
		</div>
	</fieldset>
	
	<br />
	<button class="btn btn-primary" onClick="show_report();">{#SHOW_REPORT#}</button>&nbsp;&nbsp;&nbsp;&nbsp;

	{if $sessioninfo.privilege.EXPORT_EXCEL}
	  	<button class="btn btn-primary" name="output_excel" onClick="show_report('export_excel');">{#OUTPUT_EXCEL#}</button>
	  {/if}
</form>
{/if}

{if $smarty.request.load_report}
{if !$data and !$err}
	<p>- No Data -</p>
{else}

<h2>{$report_title}</h2>
<ul>
	<li>Transaction count may have overlap between different promotion, so total transaction may not equal to sum transaction.</li>
</ul>
<table class="tb" width="100%" cellspacing="0" cellpadding="4">
	<thead>
		<tr>
			<th>Promotion Title</th>
			{assign var=last_month value=''}
			{foreach from=$date_list key=date_key item=d}
				<th>
					{if $last_month ne $d|date_format:"%m"}{$d|date_format:"%m/%Y"}<br>{/if}
					{assign var=last_month value=$d|date_format:"%m"}
					{$d|date_format:"%d"}
				</th>
			{/foreach}
			<th>Total<br>Trans</th>
			<th>Total<br>Discount Amt</th>
		</tr>
	</thead>
	{foreach from=$data.by_promo key=branch_promo_id item=promo}
		<tr>
			<td><b>{$promo.promo_info.title}</b></td>
			{foreach from=$date_list key=date_key item=d}
				<td align="right" {if $promo.promo_usage.$date_key}title="Trans: {$promo.promo_usage.$date_key.pos_count|number_format}, Discount Amt: {$promo.promo_usage.$date_key.amt|number_format:2}"{/if}>	
					{if !$promo.promo_usage.$date_key}
						-
					{else}
						{$promo.promo_usage.$date_key.amt|number_format:2}
					{/if}
				</td>
			{/foreach}
			
			<!-- Total Trans -->
			<td align="right">
				{if !isset($promo.promo_usage.total.pos_count)}
					-
				{else}
					{$promo.promo_usage.total.pos_count|number_format}
				{/if}
			</td>
			
			<!-- Total Discount Amt -->
			<td align="right">
				{if !isset($promo.promo_usage.total.amt)}
					-
				{else}
					{$promo.promo_usage.total.amt|number_format:2}
				{/if}
			</td>
		</tr>
	{/foreach}
	<tr>
		<td align="right"><b>Total</b></td>
		{foreach from=$date_list key=date_key item=d}
			<td align="right" {if $data.total.$date_key}title="Trans: {$data.total.$date_key.pos_count|number_format}, Discount Amt: {$data.total.$date_key.amt|number_format:2}"{/if}>
				{if !isset($data.total.$date_key)}
					-
				{else}
					{*<a href="/pos_report.tran_details.php?a=load_table&counters={$smarty.request.branch_id}|all&date_from={$d}&date_to={$d}&tran_status=1&other_filter=got_mm_discount&payment_type=all&tran_type=all&submits=1" target="_blank">*}
					{$data.total.$date_key.amt|number_format:2}
					{*</a>*}
				{/if}
			</td>
		{/foreach}
		<!-- Total Trans -->
		<td align="right">
			{if !isset($data.total.total.pos_count)}
				-
			{else}
				{$data.total.total.pos_count|number_format}
			{/if}
		</td>
		
		<!-- Total Discount Amt -->
		<td align="right">
			{if !isset($data.total.total.amt)}
				-
			{else}
				{$data.total.total.amt|number_format:2}
			{/if}
		</td>
	</tr>
</table>
{/if}
{/if}
{include file='footer.tpl'}