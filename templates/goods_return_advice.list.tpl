{*
Revision History
================
4/19/2007 yinsee
- allow non-owner to edit and print checklist

3/25/2008 3:44:28 PM gary
- lorry to vehicle

4/13/2010 3:11:07 PM Andy
- GRA Change to only "Complated GRA" can print.

10/29/2010 5:49:05 PM Alex
- Add show cost privilege

11/1/2010 2:36:45 PM Alex
- fix link and print bugs if select all branch from goods

4/29/2011 4:21:11 PM Justin
- Rounding is now base on config['gra_cost_decimal_points'], if not found means all round by 2 decimal points.

5/23/2011 12:10:59 PM Justin
- Modified the grand total amount to round by default 2 decimal points instead of follow config set.

6/27/2011 12:20:22 PM Andy
- Add print preview page for GRA.

7/24/2012 11:06 AM Justin
- Added "Account ID" column and available when config is found.
- Added Vendor Code column.

7/31/2012 4:28:14 PM Justin
- Enhanced to show branch code column when search result from HQ.

9/25/2012 11:49 AM Justin
- Added to show disposal date while in summary mode.

7/2/2013 11:35 AM Justin
- Enhanced to use checklist dialog when click print checklist.
- Enhanced to show view and printing icon for "Waiting for Approval" tab.
- Enhanced to show current approver for cancelled/terminated tab.

07/16/2013 05:58 PM Justin
- Bug fixed the GRA approvals some times cannot display.

7/31/2013 11:58 AM Andy
- Fix GRA show wrong Approval sequence in Waiting for Approval list.

2/10/2014 5:24 PM Justin
- Bug fixed on loading wrong GRA items while access GRA that created from subbranch at HQ.

3/24/2015 11:09 AM Justin
- Enhanced to have print DN feature.

4/20/2015 4:04 PM Justin
- Enhanced the total amount of GRA always include amount from items not in ARMS.

4/20/2015 4:41 PM Justin
- Enhanced to have GST information.

4/29/2015 4:51 PM Justin
- Enhanced to pickup remark.

5/21/2015 9:12 AM Justin
- Bug fixed on gra amount has calculate wrongly.

5/2/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

7/27/2018 5:53 PM Justin
- Bug fixed on amount include GST will calculate wrongly if not using foreign currency feature.

12/7/2018 9:47 AM Justin
- Enhanced to have Rounding Adjust.

5/16/2019 4:21 PM William
- Enhance "GRA" word to use report_prefix.
*}

{literal}
<script>
/*function do_print_checklist(id,bid)
{
	var a = prompt('Enter Packing List #, leave blank to print the latest.');
	if (a==null) return false;
	ifprint.location = 'goods_return_advice.php?id='+id+'&bid='+bid+'&a=print_checklist&bno='+a;
	//window.open('goods_return_advice.php?id='+id+'&bid='+bid+'&a=print_checklist&bno='+a);
}*/

function do_print(id,bid,no_dialog)
{
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=bid;
	if (no_dialog==true)
	{
	    print_ok();
	    return;
	}
	curtain(true);
	show_print_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
	if(!document.f_prn['own_copy'].checked&&!document.f_prn['vendor_copy'].checked){
		alert('Please Select at least one copy');
		return false;
	}

	$('print_dialog').style.display = 'none';
	//document.f_prn.target = "ifprint";
	document.f_prn.target = "_blank";
	document.f_prn.submit();
	curtain(false);
}

function print_cancel()
{
	$('print_dialog').style.display = 'none';
	curtain(false);
}

</script>
{/literal}

{if $form.is_summary}
<div id=gra_list style="border:1px solid #000">
{/if}
{if !$gra_list}
<p align=center>-- No Record --</p>
{else}
<div align="left">
{$pagination}
</div>
<table border=0 cellspacing=1 cellpadding=4 width=100%>

<thead class="bg-gray-100">
	<tr >
		<th>&nbsp;</th>
		<th>GRA</th>
		{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
			<th>Branch</th>
		{/if}
		<th>Vendor Code</th>
		{if $config.enable_vendor_account_id}
			<th>Account ID</th>
		{/if}
		<th>Vendor</th>
		<th>Vehicle No</th>
		{if $form.is_summary}
		<th>DN No.</th>
		<th>DN Amount</th>
		{/if}
		<th>SKU Type</th>
		{if $sessioninfo.show_cost}
			{if $got_rounding_adj}
				<th>Amount<br />Before Round</th>
				<th>Rounding<br />Adjust</th>
			{/if}
			{if $form.is_summary && $have_fc}
				<th>Foreign Amount</th>
				<th>Exchange Rate</th>
			{/if}
			<th>Amount</th>
			{if $form.is_summary && $is_under_gst}
				<th>GST</th>
				<th>Amount<br />Incl. GST</th>
			{/if}
		{/if}
		<th>Added</th>
		<th>Last Update</th>
		{if $mode=='cancel'}
		<th>Reason</th>
		{/if}
		{if $form.is_summary && $config.gra_enable_disposal}
		<th>Disposal Date</th>
		{/if}
		</tr>
</thead>
<tbody class="fs-08">
{section name=i loop=$gra_list}
<tr bgcolor="{cycle values="#eeeeee,"}">
    <td nowrap>
	<textarea id="remark_{$gra_list[i].id}_{$gra_list[i].branch_id}" style="display:none;">{$gra_list[i].remark2|escape:'html'}</textarea>
    {if $gra_list[i].status==0 || $gra_list[i].status==2}
	    {if $gra_list[i].returned==0}
		    {if $form.is_summary}
				<a href="goods_return_advice.php?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}" target="_blank">
				<img src="ui/view.png" border="0" title="View"></a>		    
		    {else}
				{if $gra_list[i].status==2 || $gra_list[i].approved==1}
					<a href="goods_return_advice.php?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}" target="_blank">
					<img src="ui/view.png" border="0" title="View"></a>		
				{else}
					<a href="?a=open&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}">
					<img src="ui/ed.png" border="0" title="Edit"></a>
				{/if}
				
				<a href="javascript:void(do_print_checklist_dialog({$gra_list[i].id},{$gra_list[i].branch_id}))">
				<img src="ui/report_edit.png" border="0" title="Print Checklist"></a>
		    {/if}
		{else}
		    {if $form.is_summary}
				<a href="goods_return_advice.php?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}"  target="_blank">
				<img src="ui/view.png" border="0" title="View"></a>
		    {else}
				<a href="?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}">  		
				<img src="ui/view.png" border="0" title="View"></a>
		    {/if}
            <a href="javascript:void(do_print({$gra_list[i].id},{$gra_list[i].branch_id}))"><img src=ui/print.png border="0" title="Print"></a>
			
			{if $gra_list[i].generate_arms_dn}
				<img src="ui/icons/page_add.png" border="0" title="Generate ARMS DN" onclick="toggle_dn_printing_menu('{$gra_list[i].id}', '{$gra_list[i].branch_id}');">
			{elseif $gra_list[i].print_arms_dn}
				<a href="?a=print_arms_dn&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}" target="_blank">  		
					<img src="ui/icons/page_copy.png" border="0" title="Print ARMS DN">
				</a>
			{/if}
		{/if}
    {else}
	    {if $form.is_summary}
			<a href="goods_return_advice.php?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}"  target="_blank">
			<img src="ui/rejected.png" border="0" title="Cancel"></a>
	    {else}
			<a href="?a=view&id={$gra_list[i].id}&branch_id={$gra_list[i].branch_id}">
			<img src="ui/rejected.png" border="0" title="Cancel">
			</a>
	    {/if}
	{/if}
	</td>
	<td>{$gra_list[i].report_prefix}{$gra_list[i].id|string_format:"%05d"}</td>
	{if $smarty.request.t eq 0 && $BRANCH_CODE eq 'HQ'}
		<td>{$gra_list[i].branch_code}</td>
	{/if}
	<td>{$gra_list[i].vendor_code}</td>
	{if $config.enable_vendor_account_id}
		<td>{$gra_list[i].account_id}</td>
	{/if}
	<td>
		{$gra_list[i].vendor}
		{if preg_match('/\d/',$gra_list[i].approvals)}
			<div class=small>Approvals: <font color="#0000ff">{get_user_list list=$gra_list[i].approvals aorder_id=$gra_list[i].approval_order_id}</font></div>
		{/if}
	</td>
	<td>{$gra_list[i].transport}</td>
	{if $form.is_summary}
		<td align=center>{$gra_list[i].misc_info.dn_no|default:"-"}</td>
		<td align=right>{$gra_list[i].misc_info.dn_amount|default:0|number_format:2}</td>
	{/if}
	<td align=center>{$gra_list[i].sku_type}</td>
	{if $sessioninfo.show_cost}
		{if $got_rounding_adj}
			<td class="r">
				{if $gra_list[i].currency_code}{$gra_list[i].currency_code}{/if} {$gra_list[i].amount-$gra_list[i].rounding_adjust|number_format:2}
			</td>
			<td class="r">
				{$gra_list[i].rounding_adjust|number_format:2}
			</td>
		{/if}
	
		{if $form.is_summary}
			{if $gra_list[i].currency_code}
				<td align="right">{$gra_list[i].currency_code} {$gra_list[i].amount|number_format:2}</td>
				<td align="right">{$gra_list[i].currency_rate}</td>
				{assign var=row_myr_amt value=$gra_list[i].amount*$gra_list[i].currency_rate}
			{else}
				{assign var=row_myr_amt value=$gra_list[i].amount}
				{if $have_fc}
					<td align="right">-</td>
					<td align="right">-</td>
				{/if}
			{/if}
			{assign var=row_myr_amt value=$row_myr_amt|round2}
		{/if}
	
		<td align=right {if $form.is_summary && $gra_list[i].currency_code}class="converted_base_amt"{/if}>
			{if !$gra_list[i].currency_code}
				{$gra_list[i].amount|number_format:2}
			{else}
				{if $form.is_summary}
					{$row_myr_amt|number_format:2}{if $gra_list[i].currency_code}*{/if}
				{else}
					{$gra_list[i].currency_code} {$gra_list[i].amount|number_format:2}
					<br />
					{assign var=base_grr_amount value=$gra_list[i].amount*$gra_list[i].currency_rate}
					{assign var=base_grr_amount value=$base_grr_amount|round2}
					<span class="converted_base_amt">{$config.arms_currency.code} {$base_grr_amount|number_format:2}*</span>
				{/if}
			{/if}
		</td>
		{if $form.is_summary && $is_under_gst}
			<td align=right>{$gra_list[i].gst|number_format:2}</td>
			<td align=right {if $gra_list[i].currency_code}class="converted_base_amt"{/if}>
				{$row_myr_amt+$gra_list[i].gst|number_format:2}{if $gra_list[i].currency_code}*{/if}
			</td>
		{/if}
 	{/if}
	<td align=center>{$gra_list[i].added}</td>
	<td align=center>{$gra_list[i].last_update}</td>
	{if $mode=='cancel'}
	<td align=center>{$gra_list[i].remark}</td>
	{/if}
	{if $form.is_summary && $config.gra_enable_disposal}
	<td align=center>{$gra_list[i].disposal_date}</td>
	{/if}
</tr>
{/section}
</table>
{/if}
{if $form.is_summary}
</div>
{/if}
