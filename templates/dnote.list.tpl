{*
8/10/2015 2:10 PM Andy
- Enhanced to show branch code when login at HQ.

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

6/4/2019 11:46 AM William
-Enhanced GRA,GRN to use report prefix.
*}

{if !$dn_list}
	<p align="center"> &nbsp; * No DN Found *</p>
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

    <table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px">
		<tr bgcolor="#ffee99">
			<th width="60">&nbsp;</th>
			{if $BRANCH_CODE eq 'HQ'}
				<th width="80">Branch</th>
			{/if}
			<th width="100">Inv No</th>
			<th width="100">DN No</th>
			<th>Type</th>
			<th width="100">ID</th>
			<th>Vendor</th>
			<th>Gross Amount</th>
			<th>GST Amount</th>
			<th>Total Amount Inclusive GST</th>
			<th>DN Date</th>
			<th>Last Update</th>
		</tr>
		{foreach from=$dn_list item=r}
		    <tr bgcolor="{cycle values=",#eeeeee"}">
		        <td align="center">
					<a href="{$smarty.server.PHP_SELF}?a=print_dn&id={$r.id}&branch_id={$r.branch_id}" target="_blank"><img src="ui/print.png" title="Print DN" border=0></a>
				</td>
				{if $BRANCH_CODE eq 'HQ'}
					<td align="center">{$r.branch_code}</td>
				{/if}
				<td>{$r.inv_no}</td>
		        <td>{$r.dn_no}</td>
				<td>{$r.ref_table|upper}</td>
				<td>{$r.report_prefix|upper}{$r.ref_id|string_format:"%05d"}</td>
				<td>{$r.vendor_description}</td>
				<td align="right">
					{if !$r.currency_code}
						{$r.total_gross_amount|number_format:2}
					{else}
						{$r.currency_code} {$r.total_gross_amount|number_format:2}
						<br />
						{assign var=base_grr_amount value=$r.total_gross_amount*$r.currency_rate}
						{assign var=base_grr_amount value=$base_grr_amount|round2}
						<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
					{/if}
				</td>
				<td align="right">{$r.total_gst_amount|number_format:2}</td>
				<td align="right">
					{if $r.is_under_gst}
						{assign var=final_amt value=$r.total_amount}
					{else}
						{assign var=final_amt value=$r.total_gross_amount}
					{/if}
					
					{if !$r.currency_code}
						{$final_amt|number_format:2}
					{else}
						{$r.currency_code} {$final_amt|number_format:2}
						<br />
						{assign var=base_grr_amount value=$final_amt*$r.currency_rate}
						{assign var=base_grr_amount value=$base_grr_amount|round2}
						<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
					{/if}
				</td>
		        <td align="center">{$r.dn_date}</td>
				<td align="center">{$r.last_update}</td>
		    </tr>
		{/foreach}
	</table>
{/if}
