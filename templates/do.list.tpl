{*
REVISION HISTORY
++++++++++++++++++
10/5/2007 4:26:00 PM gary
- added "DO/" for displaying do no.

12/18/2007 12:13:38 PM gary
- show all the deliver branches or company.

7/10/2009 3:07:29 PM Andy
- add let user to key in invoice no when print

7/31/2009 3:02:56 PM Andy
- Edit Do No. display layout

10/5/2009 2:30 PM Andy
- Add print DO Draft and Proforma DO

11/9/2009 1:15:27 PM edward
- Add change owner

11/10/2009 10:18:17 AM Andy
- Add invoice amount column

12/7/2009 3:23:49 PM Andy
- Fix get invoice num bugs

12/24/2009 4:19:30 PM Andy
- Add Invoice No column

1/14/2010 1:45:12 PM Andy
- Add debtor description under code

1/18/2010 11:35:51 AM Andy
- add paid checkbox

6/16/2011 5:11:21 PM Justin
- Added new fields as "Foreign Amount" and "Foreign Invoice Amount".

11/18/2011 2:47:15 PM Andy
- Add save/show DO price type. (only if all items in DO having same price type and is consignment mode).

1/17/2013 3:03 PM Justin
- Enhanced not to show empty description with "()".

4/12/2013 4:33 PM Andy
- Enhance to show a warning icon and ask user to open and save again the DO if found DO Amount need update.

1/5/2016 4:00 PM Qiu Ying
- Editing only allowed according to login branch (for saved DO)

1/11/2016 11:00 AM Qiu Ying
- Edit other branch DO and Sales Order should allow in consignment mode

05/05/2016 17:00 Edwin
- Added new table column "Added date" at Transfer Do, Credit Sales Do and Cash Sales Do.

08/02/2016 17:00 Edwin
- Enhanced on add printed status in "Approve" and "Checkout" tab.

4/19/2017 1:18 PM Khausalya
Enhanced changes from RM to use config setting. 

7/27/2017 2:01 PM Justin
- Enhance to show export button when it is Transfer DO and under Save or Waiting for Approval.

6/1/2018 2:00 PM HockLee
- Added Batch Code column.

7/16/2020 9:16 AM William
- Enhanced approved and checkout list can mark as paid and key in (Payment Date, payment type and Remark).

8/4/2020 9:23 AM William
- Remove sorting of column not able to use sorting function.

3/12/2021 1:30 PM Ian
- Added export DO items to csv option for approved DO
*}

{if $do_type eq 'transfer' and $config.do_transfer_have_discount}
	{assign var=show_invoice_amt value=1}
{elseif $do_type eq 'open' and $config.do_cash_sales_have_discount}
    {assign var=show_invoice_amt value=1}
{elseif $do_type eq 'credit_sales' and $config.do_credit_sales_have_discount}
    {assign var=show_invoice_amt value=1}
{/if}
<script>
{literal}
var do_inv_no = {};
var do_branch_id_list = {};
{/literal}
</script>

{$pagination}
<table class=sortable id=do_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
	<th class="ignore_sorting">&nbsp;</th>
	<th>DO No.</th>
	{if $do_type eq 'credit_sales'}
	<th>Batch Code</th>
	{/if}
	<th>Inv No.</th>
	<th>PO No.</th>
	<th>Deliver To</th>
	{if $do_type eq 'open' || $do_type eq 'credit_sales'}
	    <th class="ignore_sorting">Paid</th>
	{/if}
	{if $config.consignment_modules}
		<th>Price Type</th>
	{/if}
	<th>Amount {if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}({$config.arms_currency.symbol})</th>
		<th>Foreign Amount
	{/if}
	</th>
	{if $show_invoice_amt}
	    <th width="100">Invoice Amount {if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}({$config.arms_currency.symbol})</th>
			<th width="100">Foreign Invoice Amount
		{/if}
		</th>
	{/if}
	<th>DO Date</th>
	<th>Added Date</th>
	<th>Last Update</th>
	{if $show_do_printed}
		<th class="ignore_sorting">Printed DO</th>
	{/if}	
	{if $show_inv_printed}
		<th class="ignore_sorting">Printed Invoice</th>
	{/if}
</tr>

{section name=i loop=$do_list}
<script>
	var temp_inv = '{$do_list[i].inv_no}';
	var temp_inv2 = temp_inv.split("/");
 	//var s = '{$do_list[i].inv_no}'.substr(-5);
 	var s = temp_inv2[0];
    do_inv_no['{$do_list[i].branch_id}_{$do_list[i].id}'] = float(s);
	{if $do_list[i].do_type eq 'transfer'}
		do_branch_id_list['{$do_list[i].branch_id}_{$do_list[i].id}'] = [];
		{if $do_list[i].do_branch_id}
			do_branch_id_list['{$do_list[i].branch_id}_{$do_list[i].id}'].push('{$do_list[i].do_branch_id}');
		{else}
			{foreach from=$do_list[i].deliver_branch item=ddb}
				do_branch_id_list['{$do_list[i].branch_id}_{$do_list[i].id}'].push('{$ddb}');
			{/foreach}
		{/if}
	{/if}
</script>
<tr bgcolor={cycle values=",#eeeeee"}>
	<td align=center>	
			{if $do_list[i].approved}
				{if $do_list[i].checkout}
		 			<a href="do_checkout.php?a=view&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}" target="_blank"><img src="ui/view.png" title="View Completed DO" border=0></a>
				{else}	
	 				<a href="do.php?a=view&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}"><img src="ui/approved.png" title="Open this DO" border=0></a>
				{/if}
				<a href="javascript:void(do_print('{$do_list[i].id}','{$do_list[i].branch_id}',{if $do_list[i].checkout}true{else}false{/if},'{$do_list[i].invoice_markup}'))"><img src="ui/print.png" title="Print this DO" border=0></a>
			{elseif $do_list[i].status eq '2'}
	 			<a href="do.php?a={if $do_list[i].user_id == $sessioninfo.id}open{else}view{/if}&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}"><img src="ui/rejected.png" title="Open this DO" border=0></a>
			{elseif $do_list[i].status eq '4' || $do_list[i].status eq '5'}
	 			<a href="do.php?a=view&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}">
				<img src="ui/cancel.png" title="Open this DO" border=0>
				</a>
			{elseif $do_list[i].status eq '1'}
	 			<a href="do.php?a=view&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}">
				<img src="ui/view.png" title="View this DO" border=0>
			 	</a>
				<a href="javascript:void(do_print('{$do_list[i].id}','{$do_list[i].branch_id}',false,'proforma'))"><img src="ui/print.png" title="Print Proforma DO" border=0></a>
			{else}
				{if $sessioninfo.branch_id == $do_list[i].branch_id || $config.consignment_modules}
					{if $do_list[i].do_type}
						<a href="do.php?a=open&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}&do_type={$do_list[i].do_type}">
					{else}
					<a href="do.php?a=open&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}">
					{/if}
					<img src="ui/ed.png" title="Open this DO" border=0>
					</a>
				{else}
					<a href="do.php?a=view&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}{if $do_list[i].do_type}&do_type={$do_list[i].do_type}{/if}">
					<img src="ui/view.png" title="View this DO" border=0>
					</a>
				{/if}
			 	{if $sessioninfo.level>=9999 || $sessioninfo.id==$do_list[i].user_id}
				<a href="javascript:void(do_chown({$do_list[i].id},{$do_list[i].branch_id}))"><img src="ui/chown.png" title="Change owner" border=0></a>
				{/if}
				<a href="javascript:void(do_print('{$do_list[i].id}','{$do_list[i].branch_id}',false,'draft'))"><img src="ui/print.png" title="Print DO Draft" border=0></a>
			{/if}
			
			{* show export button when it is Transfer DO and under Save or Waiting for Approval *}
			{if $do_list[i].do_type eq 'transfer' && $do_list[i].active && !$do_list[i].approved && (!$do_list[i].status || $do_list[i].status eq '1' || $do_list[i].status eq '3')}
				<a href="javascript:void(do_export('{$do_list[i].id}','{$do_list[i].branch_id}',false,'draft'))"><img src="/ui/icons/page_excel.png" title="Export DO" border=0></a>
			{/if}
			
			{if $do_list[i].active eq 1 && $do_list[i].approved eq 1 && $do_list[i].status eq 1 || $do_list[i].checkout}
				<a href="do.php?a=export_approved_do&id={$do_list[i].id}&branch_id={$do_list[i].branch_id}">
				<img src="ui/icons/page_excel.png" title="Export DO Items" border=0>
				</a>
			{/if}
	</td>
	<td>
		{if $do_list[i].approved}
			{if $do_list[i].do_no}
				{$do_list[i].do_no}
			{else}
				{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(DD)
			{/if}
			<br>
			<font class="small" color=#009900>
			{*
			{if $do_list[i].hq_do_id}
				DO/{$do_list[i].branch_prefix}{$do_list[i].hq_do_id|string_format:"%05d"}(PD)
			{else}
				DO/{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
			{/if}
			*}
			{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
			</font>
		{elseif $do_list[i].status<1}
		    {if $do_list[i].do_no}
		        {$do_list[i].do_no}
		        <br>
				<font class="small" color=#009900>
				    {$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(DD)
				</font>
			{else}
			    {$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(DD)
		    {/if}
			{*
			{if $do_list[i].do_no}
				DO/{$do_list[i].do_no}(DD)
			{else}
				DO/{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(DD)
			{/if}
			*}
		{elseif $do_list[i].status eq '1'}
		    {if $do_list[i].do_no}
		        {$do_list[i].do_no}
		        <br>
				<font class="small" color=#009900>
				    {$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
				</font>
			{else}
			    {$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
		    {/if}
			{*
			DO/{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
			*}
		{elseif $do_list[i].status>1}
			{if $do_list[i].do_no}
		        {$do_list[i].do_no}
		        <br>
				<font class="small" color=#009900>
				    {$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
				</font>
			{else}
			    {$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
		    {/if}
			{*
			DO/{$do_list[i].branch_prefix}{$do_list[i].id|string_format:"%05d"}(PD)
			*}
		{/if}
		
	 	{if preg_match('/\d/',$do_list[i].approvals)}
		<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$do_list[i].approvals aorder_id=$do_list[i].approval_order_id}</font></div>
		{/if}
	</td>
	{if $do_type eq 'credit_sales'}
		<td align=center>{$do_list[i].batch_code|default:"-"}</td>
	{/if}
	<td align=center>{$do_list[i].inv_no|default:"-"}</td>
	<td align=center>{$do_list[i].po_no|default:"-"}</td>
	
	<td>
	{if $do_list[i].do_type eq 'credit_sales'}
	    {assign var=debtor_id value=$do_list[i].debtor_id}
	    Debtor: {$debtor.$debtor_id.code}
		{if $debtor.$debtor_id.description}
			<br />
			<span class="small" style="color:blue;">({$debtor.$debtor_id.description})</span>
		{/if}
	{else}
		{if $do_list[i].do_branch_id}
			{$do_list[i].branch_name_2}
		{elseif $do_list[i].open_info.name}
			{$do_list[i].open_info.name}
		{/if}
		{foreach from=$do_list[i].d_branch.name item=pn name=pn}
			{if $smarty.foreach.pn.iteration>1} ,{/if}
			{$pn}
		{/foreach}
	{/if}
	</td>
	{if $do_type eq 'open'|| $do_type eq 'credit_sales'}
	    <th>
          {if $do_list[i].do_type eq 'open' || $do_list[i].do_type eq 'credit_sales'}
			{if $do_list[i].approved}
				<button value="Paid" onclick="show_paid('{$do_list[i].id}','{$do_list[i].branch_id}')">Paid</button>
			{/if}
			<img id="img_paid_status_{$do_list[i].id}_{$do_list[i].branch_id}" src="{if $do_list[i].paid}ui/approved.png{else}ui/icons/cancel.png{/if}" title="Paid Status"/>
			     {*<input type="checkbox" name="paid" id="paid,{$do_list[i].id},{$do_list[i].branch_id}" title="Paid" onChange="update_paid(this);" value="1" {if !$do_list[i].approved}disabled {/if} {if $do_list[i].paid}checked {/if} />*}
		  {/if}
		</th>
	{/if}
	
	{if $config.consignment_modules}
		<td>{$do_list[i].sheet_price_type|default:'-'}</td>
	{/if}
	
	<td align="right">
		{if $do_list[i].amt_need_update}
			<img src="/ui/messages.gif" align="absmiddle" title="Please open and save again to correct the amount." />
		{/if}
		{$do_list[i].total_amount|number_format:2}
	</td>
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		<td align=right>{$do_list[i].total_foreign_amount|number_format:2}</td>
	{/if}
	{if $show_invoice_amt}
	    <td align="right">{$do_list[i].total_inv_amt|number_format:2}</td>
		{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
			<td align="right">{$do_list[i].total_foreign_inv_amt|number_format:2}</td>
		{/if}
	{/if}
	<td align=center>{$do_list[i].do_date|date_format:"%d-%m-%Y"}</td>
	<td align=center>{$do_list[i].added}</td>
	<td align=center>{$do_list[i].last_update}</td>
	{if $show_do_printed}
		<td align=center>{if $do_list[i].do_printed}<img src="ui/approved.png">{else}<img src="ui/approved_grey.png">{/if}</td>
	{/if}
	{if $show_inv_printed}
		<td align=center>{if $do_list[i].inv_printed}<img src="ui/approved.png">{else}<img src="ui/approved_grey.png">{/if}</td>
	{/if}
</tr>
{sectionelse}
<tr>
	{assign var=cols value=10}
	{if $config.consignment_modules && is_array($config.masterfile_branch_region) && is_array($config.consignment_multiple_currency)}
		{assign var=cols value=$cols+1}
		{if $show_invoice_amt}
			{assign var=cols value=$cols+1}
		{/if}
	{/if}
	{if $show_invoice_amt}
		{assign var=cols value=$cols+1}
	{/if}
	<td colspan="{$cols}" align=center>- no record -</td>
</tr>
{/section}
</table>
<script>
ts_makeSortable($('do_tbl'));
</script>
