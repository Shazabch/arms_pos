{*

*}


<!-- print sheet -->
<div class="printarea">

<table width="100%" cellspacing="0" cellpadding="0" border="1" class="small">
<tr>
    <td><img src="{get_logo_url mod='membership'}" height=80 hspace=5 vspace=5></td>
	<td width=100%>
	<h2>
		<div style="text-align:center;">Tax Invoice</div>
		{$from_branch.description}</h2>
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}
		&nbsp;&nbsp; Fax: {$from_branch.phone_3}
		&nbsp;&nbsp;&nbsp;&nbsp;GST Reg No: {$from_branch.gst_register_no}
		
		{/if}
	</td>
	<td rowspan="2" valign="bottom">
	    <table class="normal_tbl">
			<tr>
				<td colspan=2><div style="background:#000;padding:4px;color:#fff" align=center><b>REDEMPTION Info</b></div></td>
			</tr>
			<tr bgcolor="#cccccc" height=22>
				<td nowrap>Redemption No.</td><td nowrap>{$form.redemption_no}</td>
			</tr>
		    <tr height=22>
				<td nowrap>Date</td><td nowrap>{$form.added|date_format:$config.dat_format}</td>
			</tr>
			<tr bgcolor="#cccccc" height=22>
				<td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td>
			</tr>
			<tr>
			    <td colspan="2" align="center">{$page}</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td style="padding:5px;" colspan="2">
        <table class="normal_tbl" width="100%">
			<tr>
				<td colspan="6"><div style="background:#000;padding:4px;color:#fff" align=center><b>Membership Info</b></div></td>
			</tr>
			<tr height=22>
				<td><b>Name</b></td><td>{$membership_info.designation} {$membership_info.name}</td>
				<td><b>NRIC</b></td><td>{$membership_info.nric}</td>
				<td><b>Card No.</b></b></td><td>{$membership_info.card_no}</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<br />
{*<tr>
	<td>
	<table width="100%" cellspacing=5 cellpadding=0 border=0 height="120px" class="tb">
		<tr>
	        <td >

				<table>
				<tr><td><b>Name</b></td><td>{$membership_info.designation} {$membership_info.name}</td></tr>
				<tr><td><b>NRIC</b></td><td>{$membership_info.nric}</td></tr>
				<tr><td><b>Current {$config.membership_cardname} Number</b></td><td>{$membership_info.card_no}</td></tr>
				</table>
	        </td>
		</tr>
	</table>
	</td>
</tr>
</table>*}


<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb small">

<tr bgcolor=#cccccc>
	<th width="5">&nbsp;</th>
	<th nowrap>ARMS Code</th>
	<th nowrap>Article<br />/MCode</th>
	<th>SKU Description</th>
	<th nowrap width="60">Selling Price</th>
	<th nowrap width="60">Qty</th>
	<th nowrap width="60">Amt</th>	
	<th nowrap>GST Code</th>
	<th nowrap width="60">GST Amt</th>
	<th nowrap width="60">Amt Incl. GST</th>
</tr>

{assign var=counter value=0}
{foreach from=$items item=r}
<!-- {$counter++} -->
<tr class="no_border_bottom">
	<td align="center" nowrap>{$start_counter+$counter}.</td>
	<td align="center" nowrap>{$r.sku_item_code|default:"&nbsp;"}</td>
	<td align="center" nowrap>{if $r.artno <> ''}{$r.artno|default:"&nbsp;"}{else}{$r.mcode|default:"&nbsp;"}{/if}</td>
	<td width="90%">
		<div class="crop">{$r.description|default:"&nbsp;"}</div>
		{if $r.is_voucher && $r.voucher_code}
			Voucher Value: {$r.voucher_value|number_format:2}<br />
			Voucher Codes: {$r.voucher_code}
		{/if}
	</td>
	<td align="right">{$r.selling_price|number_format:2}</td>
	<td align="right">{$r.qty|qty_nf|ifzero:"-"}</td>
		
	<td align="right">{$r.line_gross_amt|number_format:2}</td>
	<td align="right">{$r.gst_code}@{$r.gst_rate}%</td>
	<td align="right">{$r.line_gst_amt|number_format:2}</td>
	
	<td align="right">{$r.line_amt|number_format:2}</td>
</tr>
{/foreach}

{repeat s=$counter+1 e=$PAGE_SIZE}
<!-- filler -->
<tr height=20 class="no_border_bottom">
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/repeat}

{if $is_lastpage}
	<tr class="total_row">
		<th align="right" colspan="5" >Total</th>
		<th align="right">{$form.total_qty|qty_nf}</th>
		<th align="right">{$form.gross_total_amt|number_format:2}</th>
		<th align="right">-</th>
		<th align="right">{$form.total_gst_amt|number_format:2}</th>		
		<th align="right">{$form.total_amount|number_format:2}</th>
	</tr>

	{assign var=cols value=9}

	{if $form.total_pt_need}
		<tr class="total_row">
			<th align="right" colspan="{$cols}">Paid by Points</th>
			<th align="right">{$form.total_pt_need|number_format|default:"&nbsp;"}</th>
		</tr>
	{/if}

	{if $form.total_cash_paid}
		<tr class="total_row">
			<th align="right" colspan="8">Total Cash Paid</th>
			<th align="right">{$form.total_cash_paid|number_format:2}</th>
		</tr>
	{/if}
{/if}

</table>


</div>
