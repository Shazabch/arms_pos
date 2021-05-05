{*
12/2/2010 3:28:26 PM Justin
- Amended to include the changes for calculation of pagination.
- Added the table to print empty row when the rows is not fully occupied.

7/15/2011 1:45:25 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

5/8/2015 3:14 PM Justin
- Enhanced to have GST information.

5/13/2015 6:08 PM Justin
- Enhanced to have GST summary.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

12/26/2018 11:46 AM Justin
- Enhanced to print barcode on top of the document number.

5/20/2019 4:00 PM William
- Enhance "GRR" word to use report_prefix value.
*}

{if !$skip_header}
{include file='header.print.tpl'}

<script type="text/javascript">
var doc_no = '{$items[0].report_prefix}{$items[0].grr_id|string_format:"%05d"}';
{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

<!-- print sheet -->
<div class=printarea>
<table class=small align=right cellpadding=4 cellspacing=0 border=0>
<tr bgcolor=#cccccc>
	<td align=center><b>GOODS RECEIVING RECORD</b><br>{$page}</td>
</tr>
</table>
<h2>{$branch.description} {if $branch.company_no}({$branch.company_no}){/if}</h2>
<div class=small style="padding-bottom:10px">{$branch.address|nl2br}</div>

<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr>
	<td nowrap><b>GRR No</b></td>
		<td {if $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>
		{if $config.print_document_barcode}
			<span class="barcode3of9" style="padding:0;">
				*{$items[0].grr_id|string_format:"%05d"}*
			</span>
		{/if}
		
		<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
			{$items[0].report_prefix}{$items[0].grr_id|string_format:"%05d"}
		</div>
	</td>
	<td nowrap><b>Print By</b></td>
	<td nowrap>{$sessioninfo.u}</td>
	<td nowrap><b>Print Date</b></td>
	<td nowrap>{$smarty.now|date_format:$config.dat_format}</td>

</tr>
<tr>
	<td nowrap width=100><b>Supplier</b></td>
	<td colspan=5>{$items[0].vendor}</td>
</tr>
<tr>
	<td nowrap><b>Lorry No</b></td>
	<td colspan=2 nowrap>{$items[0].transport}</td>
	<td nowrap><b>Department</b></td>
	<td colspan=2 nowrap>{$items[0].department|default:"&nbsp;"}</td>
</tr>
<tr>
	<td nowrap><b>Total Received</b></td>
	<td {if !$items[0].is_under_gst}colspan=2{/if} nowrap>
		Ctn: {$items[0].grr_ctn|number_format} /
		Pcs: {$items[0].grr_pcs|number_format}
	</td>
	<td nowrap><b>Total Amount</b></td>
	<td {if !$items[0].is_under_gst}colspan=2{/if} nowrap>{$items[0].grr_amount|number_format:2}</td>
	{if $items[0].is_under_gst}
		<td nowrap><b>Total GST Amount</b></td>
		<td colspan=2 nowrap>{$items[0].grr_gst_amount|number_format:2}</td>
	{/if}
</tr>
</table>
<br>
<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr bgcolor=#cccccc>
	<th width=10>&nbsp;</th>
	<th width=10%>Document Type</th>
	<th>Reference No.</th>
	<th>Document Date</th>
	<th width=10%>Ctn</th>
	<th width=10%>Pcs</th>
	<th width=10%>Amount</th>
	{if $items[0].is_under_gst}
		<th width=10%>GST Amount</th>
		<th width=10%>GST Code</th>
	{/if}
	<th>Remarks</th>
</tr>
{assign var=tc value=0}
{assign var=tp value=0}
{assign var=tt value=0}
{assign var=tt_gst value=0}
{assign var=n value=0}
{foreach name=i from=$items item=item key=iid}
	<!-- {$n++} -->
	<tr bgcolor="{cycle name=r1 values=",#eeeeee"}" class="no_border_bottom">
		<td>{$start_counter+$smarty.foreach.i.iteration}.</td>
		<td>{$item.type|default:"&nbsp;"}</td>
		<td>{$item.doc_no|default:"&nbsp;"}</td>
		<td>{$item.doc_date|ifzero:"&nbsp;"}</td>
		<td align=right>{$item.ctn|qty_nf|ifzero:"-"}</td>
		<td align=right>{$item.pcs|qty_nf|ifzero:"-"}</td>
		<td align=right>{$item.amount|number_format:2|ifzero:"-"}</td>
		{if $items[0].is_under_gst}
			<td align=right>{$item.gst_amount|number_format:2|ifzero:"-"}</td>
			<td align=right>{$item.gst_code|default:"-"} {if $item.gst_code}({$item.gst_rate|default:'0'}%){/if}</td>
		{/if}
		<td>{$item.remark|default:"&nbsp;"}</td>
	</tr>
{assign var=tc value=`$tc+$item.ctn`}
{assign var=tp value=`$tp+$item.pcs`}
{assign var=tt value=`$tt+$item.amount`}
{assign var=tt_gst value=`$tt_gst+$item.gst_amount`}
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
	{if $items[0].is_under_gst}
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	{/if}
</tr>
{/section}

<tr>
	<td colspan=4 align=right>Total&nbsp;</td>
	<td align=right>{$tc|qty_nf}</td>
	<td align=right>{$tp|qty_nf}</td>
	<td align=right>{$tt|number_format:2}</td>
	{if $items[0].is_under_gst}
		<td align=right>{$tt_gst|number_format:2}</td>
		<td>&nbsp;</td>
	{/if}
	<td>&nbsp;</td>
</tr>
</table>
{if $is_last_page}
	<br>
	<table class="tbd" cellpadding=4 cellspacing=0 border=0 width=100%>
	<tr class="small" bgcolor=#cccccc>
		{if $items[0].is_under_gst}
			<td valign=top style="padding-right:10px; font-size:small;" width="30%" rowspan="5" bgcolor="white">
				{include file='consignment.gst_summary.tpl'}
			</td>
		{/if}
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
{/if}
</div>
