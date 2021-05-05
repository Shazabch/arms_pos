{*

*}


<pre>
	<table width="40">
		<tr>
			<td colspan=2 align="center">
				{$from_branch.description|upper|default:"-"} ({$from_branch.company_no})			
				<br />
				(GST Reg No: {$from_branch.gst_register_no})
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
		</tr>
		<tr>
			<td width="35%">Redeem No: </td>
			<td width="65%">{$form.redemption_no|default:"-"}</td>
		</tr>
		<tr>
			<td>Date: </td>
			<td>{$form.added|default:"-"}</td>
		</tr>
		<tr height="5">
			<td colspan=2></td>
		</tr>
		<tr>
			<td colspan=2>==============================</td>
		</tr>
		<tr height=20>
			<td align="center" colspan=2>Membership Info</td>
		</tr>
		<tr>
			<td colspan=2>==============================</td>
		</tr>
		<tr>
		    <td nowrap>No: </td>
		    <td>{$membership_info.card_no|default:"-"}</td>
		</tr>
		<tr>
			<td>IC: </td>
		    <td>{$membership_info.nric|default:"-"}</td>
		</tr>
		<tr>
		    <td valign="top">Name: </td>
		    <td>{$membership_info.name|default:"-"}</td>
		</tr>
	</table>
	<table width="40" style="margin-top:-1.5em;">
		<tr>
			<td width="62%">Details</td>
			<td width="38%"></td>
		</tr>
		<tr>
			<td colspan=2>------------------------------</td>
		</tr>
		{foreach from=$items item=r}
			<tr>
				<td>{$r.sku_item_code}</td>
				<td><div class="crop">{$r.description|default:"-"}</div></td>
			</tr>
			
			{assign var=gst_amt value=$r.line_gst_amt/$r.qty}
			{assign var=selling_price_incl_gst value=$r.selling_price+$gst_amt}
			<tr>
				<td>{$r.qty|number_format|default:0} X {$selling_price_incl_gst|number_format:2}</td>
				<td align="right">{$r.line_amt|number_format:2}</td>
			</tr>
		{/foreach}
		<tr>
			<td colspan=2>------------------------------</td>
		</tr>
		<tr>
			<td>Total Amt:</td>
			<td align="right">{$form.total_amount|number_format:2|default:"-"}</td>
		</tr>
		{if $form.total_pt_need}
			<tr>
				<td>Paid by Points:</td>
				<td align="right">{$form.total_pt_need|number_format|default:"-"}</td>
			</tr>
		{/if}
		{if $form.total_cash_paid}
			<tr>
				<td>Total Cash Paid:</td>
				<td align="right">{$form.total_cash_paid|number_format:2|default:"-"}</td>
			</tr>
		{/if}
		
		<tr>
			<td colspan=2>==============================</td>
		</tr>
	</table>
	
	<table width="40" style="margin-top:-1.5em;">
		<tr>
			<td colspan="3">GST Summary</td>
		</tr>
		<tr>
			<td>GST Code</td>
			<td>Amt</td>
			<td align="right">GST</td>
		</tr>
		<tr>
			<td colspan="3">==============================</td>
		</tr>
		{foreach from=$gst_summary item=r}
			<tr>
				<td>{$r.tax_indicator}@{$r.tax_rate}%</td>
				<td>{$r.before_tax_price|number_format:2}</td>
				<td align="right">{$r.tax_amount|number_format:2}</td>
			</tr>
		{/foreach}
	</table>
</pre>
