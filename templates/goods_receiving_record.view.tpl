{*
REVISION HISTORY
================
3/5/2008 11:39:35 AM gary
- change old po link to new po link.

8/20/2009 3:22:06 PM Andy
- add reset function

7/27/2010 5:23:02 PM Alex
- add active checking on reset button

8/8/2011 11:05:11 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

9/15/2011 2:08:43 PM Justin
- Added to a message show "inactive" when found the GRR is being deleted.

3/5/2012 3:33:31 PM Justin
- Added to show GRN list.

4/14/2015 9:55 AM Justin
- Enhanced to show GST information.

5/6/2015 5:09 PM Justin
- Enhanced to have document date and GST code info.

4/18/2017 09:20 AM Qiu Ying
- Enhanced to prompt error message when PO cancellation date overdue & upload image

7/18/2017 14:31  Qiu Ying
- Enhanced to download the saved attachment

4/23/2018 1:55 PM Justin
- Enhanced to show foreign currency info for GRN.

8/28/2018 3:25 PM Andy
- Add SST feature.

5/16/2019 3:44 PM William
- Enhance "GRR" and "GRN" word to use report_prefix.

06/22/2020 2:14 Sheila
- Updated button color
*}

{include file=header.tpl}
{literal}
<style>
div.imgrollover
{
	float:left;
	height:105px;
	overflow:hidden;
	border:1px solid transparent;
	padding:2px;
}

div.imgrollover:hover
{
	background:#fff;
	height:130px;
	border:1px solid #999;
	padding:2px;
}
</style>
<script>
function view_po(id,bid)
{
	$('view_po').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	//new Ajax.Updater('view_po', 'purchase_order.php?a=view&ajax=1&id='+id+'&branch_id='+bid);
	new Ajax.Updater('view_po', 'po.php?a=view&ajax=1&id='+id+'&branch_id='+bid);
}

function do_reset(){
    document.f_do_reset['reason'].value = '';
	var p = prompt('Enter reason to Reset :');
	if (p==null || p.trim()=='' ) return false;
	document.f_do_reset['reason'].value = p;

	if(!confirm('Are you sure to reset?'))  return false;

	document.f_do_reset.submit();
	return false;
}

function download_image(obj,fp)
{
	$('_download').src = "goods_receiving_record.php?a=download_photo&f="+fp;
}
</script>
{/literal}

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$smarty.request.branch_id}">
<input type=hidden name="id" value="{$smarty.request.id}" >
<input type=hidden name=reason value="">
<input type=hidden name="rcv_date" value="{$items[0].rcv_date}">
</form>

<h1>GRR Detail ({$items[0].report_prefix}{$items[0].grr_id|string_format:"%05d"} {if !$items[0].active}- Inactive{/if})</h1>
{if $errm.top}
<div id=err><ul class=errmsg>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div>
{/if}
<br><div class="stdframe" style="background:#fff">

<table cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
	<td nowrap><b>GRR No</b></td>
	<td nowrap>{$items[0].report_prefix}{$items[0].grr_id|string_format:"%05d"}</td>
	<td nowrap><b>Received By</b></td>
	<td nowrap>{$items[0].rcv_u}</td>
	<td nowrap><b>Date Received</b></td>
	<td nowrap>{$items[0].rcv_date|date_format:"%Y-%m-%d"}</td>
</tr>
<tr>
	<td nowrap width=100><b>Supplier</b></td>
	<td>{$items[0].vendor}</td>
	<td nowrap><b>Key In By</b></td>
	<td nowrap>{$items[0].keyin_u}</td>
	<td nowrap><b>Date Added</b></td>
	<td nowrap>{$items[0].added|date_format:"%Y-%m-%d"}</td>
</tr>
<tr>
	<td nowrap><b>Department</b></td>
	<td nowrap>{$items[0].department|default:"&nbsp;"}</td>
	<td nowrap><b>Lorry No</b></td>
	<td nowrap>{$items[0].transport}</td>
	{if $items[0].grn_list}
		<td valign="top"><b>GRN No</b></td>
		<td valign="top">
			{foreach from=$items[0].grn_list key=r item=grn name=grn_list}
				<a href="goods_receiving_note.php?a=view&id={$grn.id}&branch_id={$grn.branch_id}" target="_blank">{$items[0].report_prefix}{$grn.id|string_format:"%05d"}</a>{if !$smarty.foreach.grn_list.last},{/if}
			{/foreach}
		</td>
	{/if}
</tr>
<tr>
	<td nowrap><b>Total Amount</b></td>
	<td nowrap>{$items[0].grr_amount|number_format:2}</td>
	{if $items[0].is_under_gst}
		<td nowrap><b>Total GST Amount</b></td>
		<td nowrap>{$items[0].grr_gst_amount|number_format:2}</td>
	{/if}
	<td nowrap><b>Total Received</b></td>
	<td nowrap>
		Ctn: {$items[0].grr_ctn|qty_nf} /
		Pcs: {$items[0].grr_pcs|qty_nf}
	</td>
</tr>

<tr style="{if $items[0].is_under_gst}display:none;{/if}">
	<td nowrap><b>Tax</b></td>
	<td nowrap>{$items[0].tax_percent|default:0} %</td>
	<td nowrap><b>Total GRR Tax</b></td>
	<td nowrap>{$items[0].grr_tax|number_format:2}</td>
</tr>

{if $items[0].currency_code}
	<tr>
		<td nowrap><b>Currency Code</b></td>
		<td nowrap>{$items[0].currency_code}</td>
		<td nowrap><b>Exchange Rate</b></td>
		<td nowrap>{$items[0].currency_rate}</td>
	</tr>
{/if}
{if $photo_items}
	<tr>
		<td width="100" colspan="3">
			<h5>Photo Attachment</h5>
			<div id="item_photos">
				{foreach from=$photo_items item=p name=i}
					<div class="imgrollover">
						<div align="center" width="auto" height="auto">
							<img width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache=1&img={$p.image_file|urlencode}" border="0" style="cursor:pointer" onClick="show_sku_image_div('{$p.image_file|escape:javascript}');" title="View">
						</div>
						<img src="/ui/application_put.png" align="absmiddle" valign="right" onclick="download_image(this.parentNode,'{$p.download_file|urlencode}')"> Download
					</div>
				{/foreach}
			</div>
		</td>
	<tr>
{/if}
</table>
</div>

<br>
<table cellpadding=4 cellspacing=1 border=0 width=100% style="border:1px solid #000;padding:1px;">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>Document Type</th>
	<th>Reference No.</th>
	<th>Reference Date</th>
	<th>Ctn</th>
	<th>Pcs</th>
	<th>Amount Incl Tax</th>
	{if $items[0].is_under_gst}
		<th>GST Amount</th>
		<th>GST Code</th>
	{else}
		<th>Tax Amount</th>
	{/if}
	<th>Remarks</th>
</tr>
{assign var=tc value=0}
{assign var=tp value=0}
{assign var=tt value=0}
{section name=i loop=$items}
<tr bgcolor="{cycle values='#ffffff,#eeeeee'}">
	<td>{$start_counter+$smarty.section.i.iteration}.</td>
	<td>
		{if $items[i].type eq 'PO'}<a href="javascript:void(view_po({$items[i].po_id},{$items[i].po_branch_id}))"><img src=/ui/view.png align=absmiddle border=0></a>{/if}
		{$items[i].type|default:"&nbsp;"}
	</td>
	<td>{$items[i].doc_no|default:"&nbsp;"}</td>
	<td>{$items[i].doc_date|ifzero:"&nbsp;"}</td>
	<td align=right>{$items[i].ctn|qty_nf}</td>
	<td align=right>{$items[i].pcs|qty_nf}</td>
	<td align=right>{$items[i].amount|number_format:2}</td>
	{if $items[0].is_under_gst}
		<td align=right>{$items[i].gst_amount|number_format:2}</td>
		<td align=right>{$items[i].gst_code} ({$items[i].gst_rate|default:'0'}%)</td>
	{else}
		<td align="right">{$items[i].tax|number_format:2}</td>
	{/if}
	<td>{$items[i].remark|default:"&nbsp;"}</td>
</tr>
{assign var=tc value=$tc+$items[i].ctn|round:$config.global_qty_decimal_points}
{assign var=tp value=$tp+$items[i].pcs|round:$config.global_qty_decimal_points}
{assign var=tt value=$tt+$items[i].amount|round:2}
{assign var=ttl_gst value=$ttl_gst+$items[i].gst_amount|round:2}
{/section}
<tr bgcolor=#ffee99>
	<th colspan="4" align="right">Total&nbsp;{if $items[0].currency_code}({$items[0].currency_code}){/if}</th>
	<td align="right">{$tc|qty_nf}</td>
	<td align="right">{$tp|qty_nf}</td>
	<td align="right">{$tt|number_format:2}</td>
	{if $items[0].is_under_gst}
		<td align="right">{$ttl_gst|number_format:2}</td>
		<td align="right">&nbsp;</td>
	{else}
		<td align="right">&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
</tr>
</table>

<p align=center>
{if ($sessioninfo.level>=$config.doc_reset_level) and $items[0].active == 1}
    <input type=button class="btn btn-warning" value="Reset" onclick="do_reset();">
{/if}

<input type=button class="btn btn-error" value="Close" onclick="close_window('/goods_receiving_record.status.php')">
</p>

<div id=view_po style="padding:10px;border:1px solid #eee;"></div>
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>
{include file=footer.tpl}
