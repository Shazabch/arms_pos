{*
7/17/2009 11:17:46 AM Andy
- Add legends for icons

8/4/2009 3:34:37 PM Andy
- edit invoice no. layout

7/13/2010 4:55:34 PM Andy
- Fix Consignment Invoice main page show wrong discount percent.

3/27/2012 12:02:42 PM Justin
- Added new column "Price Type".

1/22/2015 11:35 AM Justin
- Enhanced to have GST calculation.

3/23/2015 11:38 AM Justin
- Bug fixed GST amount shows wrongly.
*}

{if $smarty.request.t eq 4}
<div style="float:right;padding:4px;">
    <img src="ui/icons/package_green.png" align="absmiddle" /> POS Exported
    <img src="ui/icons/package_go.png" align="absmiddle" /> Export POS
    <img src="/ui/icons/flag_red.png" align="absmiddle" /> Block UBS Export
    <img src="/ui/icons/flag_green.png" align="absmiddle" /> Allow UBS Export
</div>
{/if}

{$pagination}



<table class=sortable id=ci_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>Invoice No.</th>
	<th>Type</th>
	<th>Deliver To</th>
	<th>Price Type</th>
	<th>Invoice Total<br />(RM)</th>
	{if $is_under_gst}
		<th>Invoice Total <br />Incl. GST (RM)</th>
	{/if}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th>Foreign Invoice <br />Total</th>
	{/if}
	<th>Discount (%)</th>
	<th>Discount Amount<br />(RM)</th>
	{if $is_under_gst}
		<th>Discount Amount<br />Incl. GST (RM)</th>
	{/if}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th>Foreign Discount <br />Amount</th>
	{/if}
	<th>Amount<br />(RM)</th>
	{if $is_under_gst}
		<th>GST (RM)</th>
		<th>Amount<br />Incl. GST (RM)</th>
	{/if}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<th>Foreign Amount</th>
	{/if}
	<th>Invoice Date</th>
	<th>Last Update</th>
</tr>

{section name=i loop=$ci_list}
<tr bgcolor={cycle values=",#eeeeee"}>
	<td align="left" width="90" nowrap>
			{if $ci_list[i].approved}
				{if $ci_list[i].checkout}
		 			<a href="ci_checkout.php?a=view&id={$ci_list[i].id}&branch_id={$ci_list[i].branch_id}" target="_blank"><img src="ui/view.png" title="View Completed Invoice" border=0></a>
				{else}	
	 				<a href="consignment_invoice.php?a=view&id={$ci_list[i].id}&branch_id={$ci_list[i].branch_id}"><img src="ui/approved.png" title="Open this Invoice" border=0></a>
				{/if}
				<a href="javascript:void(ci_print('{$ci_list[i].id}','{$ci_list[i].branch_id}',{if $ci_list[i].checkout}true{else}false{/if},'{$ci_list[i].invoice_markup}'))"><img src="ui/print.png" title="Print this Invoice" border=0></a>
				{if $ci_list[i].type eq 'sales'}
					<a href="javascript:" onClick="ci_export_pos('{$ci_list[i].id}','{$ci_list[i].branch_id}',this);">
					{if $ci_list[i].export_pos eq 1}
					    <img src="ui/icons/package_green.png" title="Invoice Exported" border=0>
					{else}
						<img src="ui/icons/package_go.png" title="Export to Pos Transaction" border=0>
					{/if}
					</a>
				{/if}
				<a {if ($config.ci_toggle_ubs_status_level and $sessioninfo.level>=$config.ci_toggle_ubs_status_level) or (!$config.ci_toggle_ubs_status_level and $sessioninfo.level>=1000)}href="javascript:toggle_export_ubs_status('{$ci_list[i].id}');"{/if}>
					{if $ci_list[i].export_ubs}
					    <img src="/ui/icons/flag_red.png" border="0" title="Block/Unblock UBS Export" id="img,export_ubs_flag,{$ci_list[i].id}" />
					{else}
						<img src="/ui/icons/flag_green.png" border="0" title="Block/Unblock UBS Export" id="img,export_ubs_flag,{$ci_list[i].id}" />
					{/if}
				</a>
			{elseif $ci3_list[i].status eq '2'}
	 			<a href="consignment_invoice.php?a={if $ci_list[i].user_id == $sessioninfo.id}open{else}view{/if}&id={$ci_list[i].id}&branch_id={$ci_list[i].branch_id}"><img src="ui/rejected.png" title="Open this Invoice" border=0></a>
			{elseif $ci_list[i].status eq '4' || $ci_list[i].status eq '5'}
	 			<a href="consignment_invoice.php?a=view&id={$ci_list[i].id}&branch_id={$ci_list[i].branch_id}">
				<img src="ui/cancel.png" title="Open this Invoice" border=0>
				</a>
			{elseif $ci_list[i].status eq '1'}
	 			<a href="consignment_invoice.php?a=view&id={$ci_list[i].id}&branch_id={$ci_list[i].branch_id}">
				<img src="ui/view.png" title="View this Invoice" border=0>
			 	</a>
			{else}
	 			<a href="consignment_invoice.php?a=open&id={$ci_list[i].id}&branch_id={$ci_list[i].branch_id}">
				<img src="ui/ed.png" title="Open this Invoice" border=0>
			 	</a>
	 		{/if}
	</td>
	<td>
		{if $ci_list[i].approved}
			{if $ci_list[i].ci_no}
				{$ci_list[i].ci_no}
			{else}
				{$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(DD)
			{/if}
			<br>
			<font class="small" color=#009900>
			{$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
			</font>
		{elseif $ci_list[i].status<1}
			{if $ci_list[i].ci_no}
		        {$ci_list[i].ci_no}
		        <br>
				<font class="small" color=#009900>
				    {$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(DD)
				</font>
			{else}
			    {$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(DD)
		    {/if}
		{elseif $ci_list[i].status eq '1'}
			{if $ci_list[i].ci_no}
		        {$ci_list[i].ci_no}
		        <br>
				<font class="small" color=#009900>
				    {$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
				</font>
			{else}
			    {$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
		    {/if}
		{elseif $ci_list[i].status>1}
		    {if $ci_list[i].ci_no}
		        {$ci_list[i].ci_no}
		        <br>
				<font class="small" color=#009900>
				    {$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
				</font>
			{else}
			    {$ci_list[i].branch_prefix}{$ci_list[i].id|string_format:"%05d"}(PD)
		    {/if}
		{/if}
		
	 	{if preg_match('/\d/',$ci_list[i].approvals)}
		<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$ci_list[i].approvals aorder_id=$ci_list[i].approval_order_id}</font></div>
		{/if}
	</td>
	<td>{$ci_list[i].type|default:'sales'|capitalize}</td>
	<td>
	{if $ci_list[i].ci_branch_id}
		{$ci_list[i].branch_name_2}
	{elseif $ci_list[i].open_info.name}
		{$ci_list[i].open_info.name}
	{/if}
	{foreach from=$ci_list[i].d_branch.name item=pn name=pn}
		{if $smarty.foreach.pn.iteration>1} ,{/if}
		{$pn}
	{/foreach}
	</td>
	<td>{$ci_list[i].sheet_price_type|default:'-'}</td>
	{if $ci_list[i].is_under_gst}
		<td class="r">{$ci_list[i].sub_total_gross_amt|number_format:2}</td>
		<td class="r">{$ci_list[i].sub_total_amt|number_format:2}</td>
	{else}
		<td align=right>{$ci_list[i].sub_total_amt|number_format:2}</td>
		{if $is_under_gst}<td class="r">-</td>{/if}
	{/if}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<td align=right>{$ci_list[i].sub_total_foreign_amt|number_format:2}</td>
	{/if}
	<td align=right>{$ci_list[i].discount_percent|default:'-'}</td>
	{if $ci_list[i].is_under_gst}
		<td class="r">{$ci_list[i].gross_discount_amount|number_format:2}</td>
		<td class="r">{$ci_list[i].discount_amount|number_format:2}</td>
	{else}
		<td class="r">{$ci_list[i].discount_amount|number_format:2}</td>
		{if $is_under_gst}<td class="r">-</td>{/if}
	{/if}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<td align=right>{$ci_list[i].foreign_discount_amount|number_format:2}</td>
	{/if}
	{if $ci_list[i].is_under_gst}
		<td class="r">{$ci_list[i].total_gross_amt|number_format:2}</td>
		<td class="r">{$ci_list[i].total_gst_amt|number_format:2}</td>
		<td class="r">{$ci_list[i].total_amount|number_format:2}</td>
	{else}
		<td class="r">{$ci_list[i].total_amount|number_format:2}</td>
		{if $is_under_gst}<td class="r">-</td>{/if}
		{if $is_under_gst}<td class="r">-</td>{/if}
	{/if}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<td align=right>{$ci_list[i].total_foreign_amount|number_format:2}</td>
	{/if}
	<td align=center>{$ci_list[i].ci_date|date_format:"%d-%m-%Y"}</td>
	<td align=center>{$ci_list[i].last_update}</td>
</tr>
{sectionelse}
<tr>
	{assign var=cols value=9}
	{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		{assign var=cols value=$cols+3}
	{/if}
	<td colspan="{$cols}" align=center>- no record -</td>
</tr>
{/section}
</table>
<script>
ts_makeSortable($('ci_tbl'));
</script>
