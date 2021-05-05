{*
6/3/2010 6:07:08 PM Andy
- Add Export UBS for CN/DN.

10/8/2010 3:43:35 PM Andy
- Add sorting feature to CN/DN list.

12/15/2011 2:55:43 PM Justin
- Added to show foreign amount and discount amount columns.

1/22/2015 11:35 AM Justin
- Enhanced to have GST calculation.

3/9/2015 2:29 PM Justin
- Bug fixed on the invoice amount does not show correctly.

3/23/2015 11:38 AM Justin
- Bug fixed GST amount shows wrongly.
*}

{if !$sheet_list}
	<p> &nbsp; * No {$sheet_name|lower|capitalize} Found *</p>
{else}
    {if $total_page >1}
	<div style="padding:2px;float:left;">
	Page
	<select onChange="page_change(this);">
		{section loop=$total_page name=s}
			<option value="{$smarty.section.s.iteration-1}" {if $smarty.request.p eq $smarty.section.s.iteration-1}selected {/if}>{$smarty.section.s.iteration}</option>
		{/section}
	</select>
	</div>
	{/if}

    {if $smarty.request.t eq 4}
	<div style="float:right;padding:4px;">
	    <img src="/ui/icons/flag_red.png" align="absmiddle" /> Block UBS Export
	    <img src="/ui/icons/flag_green.png" align="absmiddle" /> Allow UBS Export
	</div>
	{/if}
    <table class="sortable" width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px" id="tbl_cn_list">
		<tr bgcolor="#ffee99">
			<th width="60">&nbsp;</th>
			<th width="100">{$sheet_type|upper} No</th>
			<th>Deliver To</th>
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
			<th>Qty</th>
			<th>Created By</th>
			<th>Date</th>
			<th>Last Update</th>
		</tr>
		{foreach from=$sheet_list item=r}
		    <tr bgcolor="{cycle values=",#eeeeee"}">
		        <td align="center">
					{if $r.status eq '2'}<!-- Rejected -->
					    <a href="{$smarty.server.PHP_SELF}?a={if $r.user_id eq $sessioninfo.id}open{else}view{/if}&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/rejected.png" title="Open this CN" border="0" /></a>
					{elseif $r.status eq '4' || $r.status eq '5'}
					    <a href="{$smarty.server.PHP_SELF}?a=view&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/cancel.png" title="Open this CN" border="0" /></a>
					{elseif $r.status eq '1'}
                        <a href="{$smarty.server.PHP_SELF}?a=view&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/view.png" title="View this CN" border="0" /></a>
					{else}
					    <a href="{$smarty.server.PHP_SELF}?a=open&id={$r.id}&branch_id={$r.branch_id}"><img src="ui/ed.png" title="Open this CN" border="0" /></a>
					{/if}
					{if $r.active eq 1 and $r.approved eq 1}
						<a href="javascript:void(do_print('{$r.id}','{$r.branch_id}'))"><img src="ui/print.png" title="Print {$sheet_name|lower|capitalize}" border=0></a>
					    <a {if ($config.ci_toggle_ubs_status_level and $sessioninfo.level>=$config.ci_toggle_ubs_status_level) or (!$config.ci_toggle_ubs_status_level and $sessioninfo.level>=1000)}href="javascript:void(toggle_export_ubs_status('{$r.branch_id}','{$r.id}'));"{/if}>
							{if $r.export_ubs}
							    <img src="/ui/icons/flag_red.png" border="0" title="Block/Unblock UBS Export" id="img,export_ubs_flag,{$r.branch_id},{$r.id}" />
							{else}
								<img src="/ui/icons/flag_green.png" border="0" title="Block/Unblock UBS Export" id="img,export_ubs_flag,{$r.branch_id},{$r.id}" />
							{/if}
						</a>
					{/if}
				</td>
		        <td nowrap>
                    {if $r.approved}
						{if $r.inv_no}
							{$r.inv_no}
						{else}
							{$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
						{/if}
						<br>
						<font class="small" color=#009900>{$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)</font>
					{elseif $r.status<1}
					    {if $r.inv_no}
					        {$r.inv_no}
					        <br>
							<font class="small" color=#009900>
							    {$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
							</font>
						{else}
						    {$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
					    {/if}
					{elseif $r.status eq '1'}
					    {if $r.inv_no}
					        {$r.inv_no}
					        <br>
							<font class="small" color=#009900>
							    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
							</font>
						{else}
						    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
					    {/if}
					{elseif $r.status>1}
						{if $r.inv_no}
					        {$r.inv_no}
					        <br>
							<font class="small" color=#009900>
							    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
							</font>
						{else}
						    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
					    {/if}
					{/if}

				 	{if preg_match('/\d/',$r.approvals)}
					<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$r.approvals aorder_id=$r.approval_order_id}</font></div>
					{/if}
		        </td>
		        <td>
					{$r.cn_branch_code} - {$r.cn_branch_desc}
				</td>
				{if $r.is_under_gst}
					<td class="r">{$r.sub_total_gross_amt|number_format:2}</td>
					<td class="r">{$r.total_amount|number_format:2}</td>
				{else}
					<td class="r">{$r.sub_total_amt|number_format:2}</td>
					{if $is_under_gst}<td class="r">-</td>{/if}
				{/if}
				{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
					<td align=right>{$r.total_foreign_amount+$r.foreign_discount_amount|number_format:2}</td>
				{/if}
				<td class="r">{$r.discount|default:'-'}</td>
				{if $r.is_under_gst}
					<td class="r">{$r.gross_discount_amount|number_format:2}</td>
					<td class="r">{$r.discount_amount|number_format:2}</td>
				{else}
					<td class="r">{$r.discount_amount|number_format:2}</td>
					{if $is_under_gst}<td class="r">-</td>{/if}
				{/if}
				{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
					<td align=right>{$r.foreign_discount_amount|number_format:2}</td>
				{/if}
				{if $r.is_under_gst}
					<td class="r">{$r.total_gross_amt|number_format:2}</td>
					<td class="r">{$r.total_gst_amt|number_format:2}</td>
					<td class="r">{$r.total_amount|number_format:2}</td>
				{else}
					<td class="r">{$r.total_amount|number_format:2}</td>
					{if $is_under_gst}
						<td class="r">-</td>
						<td class="r">-</td>
					{/if}
				{/if}
				{if is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
					<td align=right>{$r.total_foreign_amount|number_format:2}</td>
				{/if}
				<td class="r">{$r.total_qty|number_format:2}</td>
				<td align="center">{$r.username}</td>
				<td align="center">{$r.date}</td>
				<td align="center">{$r.last_update}</td>
		    </tr>
		{/foreach}
	</table>
	
	<script>
	{literal}
	ts_makeSortable($('tbl_cn_list'));
	{/literal}
	</script>

{/if}

