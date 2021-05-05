{*
6/10/2011 2:47:22 PM  Justin
- Make the description auto chop when it is too long.

6/20/2011 11:34:21 AM  Justin
- Amended the days expired not to show "-" when the days is "today".

7/15/2011 1:58:13 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

5/20/2019 9:39 AM William
- Enhance "GRR" word to use report_prefix.
*}

{if !$skip_header}
{include file='header.print.tpl'}

<body onload="window.print()">
{/if}

<!-- print sheet -->
<div class=printarea>
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
	<tr bgcolor=#cccccc>
		<td align=center><b>Batch No<br>Summary by SKU Item Report</b><br>{$page}</td>
	</tr>
</table>
<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>

<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
	<tr>
		<td nowrap><b>GRR No</b></td>
		<td nowrap>{$branch.report_prefix}{$form.grr_id|string_format:"%05d"}</td>
		<td nowrap><b>Print By</b></td>
		<td nowrap>{$sessioninfo.u}</td>
		<td nowrap><b>Print Date</b></td>
		<td nowrap>{$smarty.now|date_format:$config.dat_format}</td>

	</tr>
	<tr>
		<td nowrap width=100><b>Supplier</b></td>
		<td colspan=5>{$form.vendor}</td>
	</tr>
	<tr>
		<td nowrap><b>Lorry No</b></td>
		<td colspan=2 nowrap>{$form.transport}</td>
		<td nowrap><b>Department</b></td>
		<td colspan=2 nowrap>{$form.department|default:"&nbsp;"}</td>
	</tr>
	<tr>
		<td nowrap><b>Total Received</b></td>
		<td colspan=2 nowrap>
			Ctn: {$form.grr_ctn|number_format} /
			Pcs: {$form.grr_pcs|number_format}
		</td>
		<td nowrap><b>Total Amount</b></td>
		<td colspan=2 nowrap>{$form.grr_amount|number_format:2}</td>
	</tr>
</table>
<br>
<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
	<tr bgcolor=#cccccc>
		<th width="3%">#</th>
		<th width="7%">ARMS Code</th>
		<th width="50%">Description</th>
		<th width="10%">Location</th>
		<th width="12%">Batch No</th>
		<th width="7%">Expired<br />Date</th>
		<th width="7%">Day(s)<br />Expired</th>
		<th width="7%">Batch<br />Qty</th>
	</tr>
	{assign var=n value=0}
	{foreach name=i from=$items item=item key=iid name=bn}
		<!-- {$n++} -->
		<tr bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
			<td align="center">{$smarty.foreach.bn.iteration}.</td>
			<td align="center">{$item.sku_item_code}</td>
			<td><div class="crop">{$item.sku_description}</div></td>
			<td>{$item.location}</td>
			<td>{$item.batch_no}</td>
			<td align="center">{$item.expired_date|default:"&nbsp;"}</td>
			<td align="right">{$item.days_remain}</td>
			<td align="right">{$item.batch_qty|ifzero:'-'}</td>
		</tr>
		{assign var=ttl_batch_qty value=$ttl_batch_qty+$item.batch_qty}
	{/foreach}

	{assign var=s2 value=$n}
	{section name=s start=$n loop=$PAGE_SIZE}
		<!-- filler -->
		{assign var=s2 value=$s2+1}
		<!-- filler -->
		<tr bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	{/section}

	<tr>
		<td colspan="7" align="right">Total&nbsp;</td>
		<td align=right>{$ttl_batch_qty|number_format}</td>
	</tr>
</table>
{* if $is_last_page}
	<br>
	<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
		<tr class="small" bgcolor=#cccccc>
			<th>Received By</th>
			<th>Name</th>
			<th>Signature</th>
			<th>Date</th>
			<th>Time</th>
		</tr>
		
		<tr height=50>
			<td><b>At Warehouse</b></td>
			<td>{$items[0].rcv_fullname}</td>
			<td align=center>&nbsp;</td>
			<td align=center>{$items[0].rcv_date|date_format:$config.dat_format}</td>
			<td align=center>&nbsp;</td>
		</tr>
		
		<tr height=50>
			<td><b>Key In By</b></td>
			<td>{$items[0].keyin_fullname}</td>
			<td align=center>&nbsp;</td>
			<td align=center>{$items[0].added|date_format:$config.dat_format}</td>
			<td align=center>{$items[0].added|date_format:"%H:%M%p"}</td>
		</tr>
		
		<tr height=50>
			<td><b>At Department</b></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		
		<tr height=50>
			<td><b>Remarks</b></td>
			<td colspan=4>&nbsp;</td>
		</tr>
	</table>
{/if *}
</div>
