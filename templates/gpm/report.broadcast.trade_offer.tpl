{include file="header.tpl"}

{if !$no_header_footer}
<style>
{literal}
.td_h{
	background-color: #FFEE99;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var REPORT_OFFER = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
	},
	// function to validate form before submit
	check_form: function(){
		if(!this.f['offer_id'].value){
			alert('Please select Trade Offer.');
			return false;
		}
		return true;
	},
	// function when user click show report or export excel
	submit_form: function(t){
		this.f['export_excel'].value = '';
		
		if(t == 'excel'){
			this.f['export_excel'].value = 1;
		}
		
		if(!this.check_form())	return false;
		
		this.f.submit();
	}
};
{/literal}
</script>
{/if}

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="show_report" value="1" />
	<input type="hidden" name="export_excel" />
	
	<b>Trade Offer</b>
	<select name="offer_id">
		<option value="">-- Please Select --</option>
		{foreach from=$offer_list key=offer_id item=r}
			<option value="{$offer_id}" {if $smarty.request.offer_id eq $offer_id}selected {/if}>{$r.title} ({$r.date_from} - {$r.date_to})</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Show Report" onClick="REPORT_OFFER.submit_form();" />
	{if $sessioninfo.privilege.EXPORT_EXCEL}
		<button name="output_excel" onClick="REPORT_OFFER.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
	{/if}
</form>
<script type="text/javascript">REPORT_OFFER.initialize();</script>
{/if}

{if $smarty.request.show_report and !$err}
	<br />
	
	{if !$form}
		* No Data *
	{else}
		<table width="100%" class="report_table">
			{* Title *}
			<tr>
				<td width="200" class="td_h"><b>Trade Offer Title</b></td>
				<td>{$form.title}</td>
			</tr>
			
			{* Date *}
			<tr>
				<td class="td_h"><b>Date</b></td>
				<td>{$form.date_from} to {$form.date_to}</td>
			</tr>
			
			{* Qualify Condition *}
			<tr>
				<td class="td_h"><b>Qualify Condition</b></td>
				<td>Buy <b>{$form.qualify_qty}</b> get <b>{$form.qualify_offer}</b></td>
			</tr>
			
			{* Items List *}
			<tr>
				<td class="td_h"><b>Qualify Items</b></td>
				<td>
					<table class="report_table">
						<tr class="header">
							<th>ARMS CODE</th>
							<th>MCode</th>
							<th>Description</th>
						</tr>
						
						{foreach from=$form.item_list item=r}
							<tr>
								<td>{$r.sku_item_code}</td>
								<td>{$r.mcode}</td>
								<td>{$r.description}</td>
							</tr>
						{/foreach}
					</table>
				</td>
			</tr>
		</table>
		
		{* Summary *}
		{if !$form.summary}
			* No Summary Data *
		{else}
			
			{foreach from=$form.summary key=bid item=b_summary}
				<h2>Branch: {$branch_list.$bid.code} (Last Update: {$b_summary.last_update})</h2>
				<table width="100%" class="report_table">
					<tr class="header">
						<th width="150">ARMS CODE</th>
						<th>MCode</th>
						<th>Description</th>
						<th>Total Qty Sold</th>
					</tr>
					
					{if !$b_summary.items}
						<tr>
							<td colspan="4">* No Sales *</td>
						</tr>
					{else}
						{foreach from=$b_summary.items item=r}
							{assign var=sid value=$r.sku_item_id}
							<tr>
								<td>{$form.item_list.$sid.sku_item_code}</td>
								<td>{$form.item_list.$sid.mcode}</td>
								<td>{$form.item_list.$sid.description}</td>
								<td align="right">{$r.qty|qty_nf}</td>
							</tr>
						{/foreach}
						
						<tr class="header">
							<td align="right" colspan="3"><b>Total Qty</b></td>
							<td align="right">{$b_summary.total_qualify_qty|qty_nf}</td>
						</tr>
						
						<tr class="header">
							<td align="right" colspan="3"><b>Total Qualify Counter</b></td>
							<td align="right">{$b_summary.total_qualify_counter|qty_nf}</td>
						</tr>
					{/if}
				</table>
			{/foreach}
		{/if}
	{/if}
{/if}
{include file="footer.tpl"}
