{*
REVISION HISTORY
=================
10/6/2010 3:34:47 PM Justin
- Added config check for GRN future to display different result while creating GRN from GRR.

9/15/2011 4:43:43 PM Justin
- Changed the tab value from 6 to 7.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

12/24/2012 1:39 PM Justin
- Enhanced to prevent user double tab and double import PO/DO items while creating new GRN from GRR.

6/5/2017 15:15 Qiu Ying
- Enhanced to add GRR Date

4/19/2018 5:03 PM Justin
- Enhanced to show both base and foreign currency.

5/17/2019 11:46 AM William
- Enhance "GRA" word to use report_prefix.
*}

{if $grr}
	{assign var=rmk_colspan value=5}
	{if $config.use_grn_future && !$is_search}
		&nbsp;
		<form>
		<b>&nbsp;Find Document No.</b> <input name="find_grr" value="{$smarty.request.find_grr}" size="5"> <input type="submit" value="Find">
		<input type="hidden" name="t" value="7">
		</form>
	{/if}
	<table width="100%" style="{if !$config.use_grn_future}border:1px solid #000;{/if} padding:2;" cellpadding="4" cellspacing="1" border="0">
		<tr bgcolor="#ffee99">
			<td colspan="2">&nbsp;</td>
			<th>GRR No.</th>
			<th>GRR Date</th>
			<th>Department</th>
			<th>Vendor Code</th>
			{if $config.enable_vendor_account_id}
				<th>Account ID</th>
				{assign var=rmk_colspan value=$rmk_colspan+1}
			{/if}
			<th>Vendor</th>
			<th>Lorry No.</th>
			<th>Total Ctn</th>
			<th>Total Pcs</th>
			<th>Amount</th>
			<th>Received</th>
			<th>By</th>
			<th width="16">&nbsp;</th>
		</tr>

		<tbody {if count($grr) > 25}style="height:600px;overflow-y:auto;overflow-x:hidden;"{/if}>
		{assign var=grr_id value=''}
		{section name=i loop=$grr}
			{if $grr_id ne $grr[i].grr_id}
				{assign var=grr_id value=`$grr[i].grr_id`}
				<tr {if !$config.use_grn_future}style="font-weight:bold;"{/if} bgcolor={cycle values=",#eeeeee"}>
					<td colspan="2">
					{if $config.use_grn_future}
						{assign var=have_inv value=0}
						{assign var=have_do value=0}
						{assign var=have_oth value=0}
						{section name=tmp loop=$grr}
							{if $grr[tmp].grr_id eq $grr[i].grr_id}
								{if $grr[tmp].type eq 'INVOICE'}
									{assign var=have_inv value=1}
								{elseif $grr[tmp].type eq 'DO'}
									{assign var=have_do value=1}
								{elseif $grr[tmp].type eq 'OTHER'}
									{assign var=have_oth value=1}
								{/if}
							{/if}
						{/section}
						{if $grr[i].status}
							<img src="ui/lock.png" border=0 title="GRR is being used"></a>
						{elseif !$have_inv && !$have_do && !$have_oth}
							<img src="ui/lock.png" border=0 title="GRR does not contain Invoice, DO or Other"></a>
						{else}
							<a rel="#" onmouseup="prevent_doubleclick(this);" href="?a=open_grr&grr_id={$grr[i].id}&grr_item_id={$grr[i].grr_item_id}&grp_po_id={$grr[i].grp_po_id}&action=edit"><img src="ui/add_form.png" border=0 title="Create GRN for this GRR"></a>
						{/if}
					{/if}
					</td>
					<td>{$grr[i].report_prefix}{$grr[i].grr_id|string_format:"%05d"}</td>
					<td>{$grr[i].rcv_date}</td>
					<td>{$grr[i].department}</td>
					<td>{$grr[i].vendor_code}</td>
					{if $config.enable_vendor_account_id}
						<td>{$grr[i].account_id}</td>
					{/if}
					<td>{$grr[i].vendor}</td>
					<td>{$grr[i].transport}</td>
					<td align=right>{$grr[i].grr_ctn|number_format}</td>
					<td align=right>{$grr[i].grr_pcs|number_format}</td>
					<td align=right>
						{if !$grr[i].currency_code}
							{$grr[i].grr_amount|number_format:2}
						{else}
							{$grr[i].currency_code} {$grr[i].grr_amount|number_format:2}
							<br />
							{assign var=base_grr_amount value=$grr[i].grr_amount*$grr[i].currency_rate}
							{assign var=base_grr_amount value=$base_grr_amount|round2}
							<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
						{/if}
					</td>
					<td align=right>{$grr[i].rcv_date|date_format:"%d/%m/%Y"}</td>
					<td align=center>{$grr[i].rcv_u}</td>
				</tr>
			{/if}
			{if !$config.use_grn_future}
				<tr class=small bgcolor={cycle values=",#eeeeee"}>
					<td width=10>
						{if $grr[i].grn_used}
						<img src="ui/lock.png" border=0 title="GRR is used"></a>
						{else}
						<a href="?a=open_grr&grr_item_id={$grr[i].id}"><img src="ui/add_form.png" border=0 title="Create GRN for this GRR"></a>
						{/if}
					</td>
					<td>{$grr[i].type}</td>
					<td>{$grr[i].doc_no}</td>
					<td colspan="{$rmk_colspan}">Remark: {$grr[i].remark|default:"-"}</td>
					{if $grr[i].type eq 'PO'}
						<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
					{else}
						<td align=right>{$grr[i].ctn|number_format}</td>
						<td align=right>{$grr[i].pcs|number_format}</td>
						<td align=right>
							{if !$grr[i].currency_code}
								{$grr[i].amount|number_format:2}
							{else}
								{$grr[i].currency_code} {$grr[i].amount|number_format:2}
								<br />
								{assign var=base_grr_amount value=$grr[i].amount*$grr[i].currency_rate}
								{assign var=base_grr_amount value=$base_grr_amount|round2}
								<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
							{/if}
						</td>
					{/if}
					<td>&nbsp;</td>
					<td align=center>{$grr[i].u}</td>
				</tr>
			{/if}
		{/section}
		</tbody>
	</table>
{elseif $smarty.request.find_grr}
<i> -- No document matches your search --</i><br>
<a href="{$smarty.server.PHP_SELF}{if $config.use_grn_future}?t=7{/if}"> <img src="/ui/refresh.png" border="0" align="absmiddle"> Show all GRR </a>
{else}
<i> <img src="ui/bananaman.gif" align="absmiddle"> Horray! there are no GRR at the moment.</i>
{/if}