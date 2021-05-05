{*12/7/2009 2:32:16 PM Andy- add div with class crop to description and item remark1/13/2010 4:03:12 PM Andy- Add config to manage item got line or not1/18/2010 10:12:05 AM Andy- Make description can take 2 line- superscript change to use bracket7/15/2011 2:55:50 PM Andy- Add include 'header.print.tpl' for all printing templates, added support charset utf8.9/23/2011 10:55:45 AM Justin- Modified the round up for cost to base on config.- Modified the Ctn and Pcs round up to base on config set.8/19/2013 9:31 AM Fithri- assign document no as filename when print1/26/2016 3:31 PM Qiu Ying- SKU Additional Description should show in document printing7/19/2017 11:55 AM Qiu Ying- Enhanced to use the artno and mcode in sku item table instead of artno_mcode in do_items and po_items*}{if !$skip_header}{include file='header.print.tpl'}<style>{if $config.po_printing_no_item_line}{literal}.no_border_bottom td{	border-bottom:none !important;}.total_row td, .total_row th{    border-top: 1px solid #000;}.td_btm_got_line td,.td_btm_got_line th{    border-bottom:1px solid black !important;}{/literal}{/if}{literal}td div.crop{  height:auto;  max-height:2em;  overflow:hidden;}{/literal}</style><script type="text/javascript">var doc_no = '{$form.report_prefix}{$form.id|string_format:"%06d"}(PP)';{literal}function start_print(){	document.title = doc_no;	window.print();}{/literal}</script><body onload="start_print();">{/if}<div class=printarea><h1>HQ PO Allocation List</h1><div class="xsmall">This document is for internal use only, not valid for any purchase use.</div><br><table width=100% border=0 cellpadding=0 cellspacing=0><tr>	<td valign=top>		<table class=tbd>		<tr>		<th align=left>Code</th>		<th align=left>Branch</th>		<th align=left>Delivery Date</th>		<th align=left>Cancellation Date</th>		</tr>		{section name=i loop=$form.branches}		<tr>		<td nowrap>{$form.branches[i].code}</td>		<td nowrap>{$form.branches[i].description}</td>		<td nowrap>{$form.branches[i].delivery}</td>		<td nowrap>{$form.branches[i].cancel}</td>		</tr>		{/section}		</table>	</td>	<td valign=top>		<table class=tbd align=right>		<tr><th align=left>PO No.</th><td nowrap>{$form.report_prefix}{$form.id|string_format:"%06d"}(PP)</td></tr>		<tr><th align=left>Department</th><td nowrap>{$form.department}</td></tr>		<tr><th align=left>Ordered By</th><td nowrap>{$form.username}</td></tr>		<tr><th align=left>PO Date</th><td nowrap>{$form.po_date|date_format:$config.dat_format}</td></tr>		{if !$config.po_set_max_items}		<tr><th align=left>Page</th><td nowrap>{$page}</td></tr>		{/if}		</table>	</td></tr></table><br><!--- item table --><div style="border:2px solid #000; padding:1px;"><table width=100% cellspacing=0 cellpadding=0 border=0 class="box small tb"><tr class="hd topline">	<th rowspan=2>No</th>	<th rowspan=2 nowrap>ARMS Code</th>	<th rowspan=2>Art/MCode{if $config.link_code_name}<br>{$config.link_code_name}{/if}</th>	<th width=100% rowspan=2 nowrap>SKU Description</th>	<th rowspan=2>UOM</th>	{if $from_request.with_total_qty}		<th bgcolor=#aaaaaa colspan=2>Total</th>	{else}	{foreach from=$form.branches item=br}	<th bgcolor=#aaaaaa colspan=2>{$br.code}</th>	{/foreach}	{/if}</tr><tr class="hd topline">	{if $from_request.with_total_qty}		<th bgcolor=#aaaaaa class="xsmall">Ctn</th>		<th bgcolor=#aaaaaa class="xsmall">Pcs</th>	{else}	{foreach from=$form.branches item=br}	<th bgcolor=#aaaaaa class="xsmall">Ctn</th>	<th bgcolor=#aaaaaa class="xsmall">Pcs</th>	{/foreach}	{/if}</tr><tr>{if $from_request.with_total_qty}<td colspan=7 style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>{else}<td colspan={count multi=2 offset=5 var=$form.deliver_to} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td>{/if}</tr>{assign var=counter value=0}{foreach name=t from=$po_items item=item key=item_id}{if $item.qty+$item.qty_loose+$item.foc+$item.foc_loose>0}<!-- {$counter++} --><tr height=15 class="rw{cycle name=crow values=",2"} no_border_bottom">	{if !$page_item_info.$item_id.not_item}		<td>{$item.item_no+1}</td>		<td>{$item.sku_item_code}</td>		<td>{if $item.artno}{$item.artno|default:"&nbsp;"}{else}{$item.mcode|default:"&nbsp;"}{/if}{if $config.link_code_name}<br>{$item.link_code|default:"&nbsp;"}{/if}</td>	{else}		<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>	{/if}	<td>	    <div class="crop">      		{if $item.is_foc}({$item.foc_id}){/if}		{$item.description}		{if $foc_annotations.$item_id}({$foc_annotations.$item_id}){/if}		</div>		{if $item.remark}<div class="crop"><i>{$item.remark}</i></div>{/if}		{if $item.remark2}<div class="crop"><i>{$item.remark2}</i></div>{/if}	</td>		{if !$page_item_info.$item_id.not_item}		<td align=right>			{array_find_key array=$uom find=$item.order_uom_id key='id' return='code' default='EACH'}		</td>				{if $from_request.with_total_qty}			{assign var=r_ctn value=0}			{assign var=r_pcs value=0}			{foreach from=$form.branches item=br}				{assign var=bid value=$br.id}				{assign var=r_ctn value=$r_ctn+$item.foc_allocation.$bid+$item.qty_allocation.$bid}				{assign var=r_pcs value=$r_pcs+$item.foc_loose_allocation.$bid+$item.qty_loose_allocation.$bid}			{/foreach}			<td style="background:{cycle name=cf1 values="#ddd,#ccc"}" align=right nowrap>{$r_ctn|qty_nf|ifzero:"&nbsp;"}</td>			<td style="background:{cycle name=cf2 values="#ddd,#ccc"}" align=right nowrap>{$r_pcs|qty_nf|ifzero:"&nbsp;"}</td>		{else}			{foreach from=$form.branches item=br}			{assign var=bid value=$br.id}			<td style="background:{cycle name=cf1 values="#ddd,#ccc"}" align=right nowrap>{$item.foc_allocation[$bid]+$item.qty_allocation[$bid]|qty_nf|ifzero:"&nbsp;"}</td>			<td style="background:{cycle name=cf2 values="#ddd,#ccc"}" align=right nowrap>{$item.foc_loose_allocation[$bid]+$item.qty_loose_allocation[$bid]|qty_nf|ifzero:"&nbsp;"}</td>			{/foreach}		{/if}	{else}		<td>&nbsp;</td>		{if $from_request.with_total_qty}		<td>&nbsp;</td>		<td>&nbsp;</td>		{else}		{foreach from=$form.branches item=br}		<td style="background:{cycle name=cf1 values="#ddd,#ccc"}">&nbsp;</td>		<td style="background:{cycle name=cf2 values="#ddd,#ccc"}">&nbsp;</td>		{/foreach}		{/if}	{/if}</tr>{/if}{/foreach}{repeat s=$counter+1 e=15}<!-- filler --><tr height=30 class="no_border_bottom">	<td>&nbsp;</td>	<td>&nbsp;</td>	<td>&nbsp;</td>	<td>&nbsp;</td>	<td>&nbsp;</td>	{if $from_request.with_total_qty}	<td>&nbsp;</td>	<td>&nbsp;</td>	{else}	{foreach from=$form.branches item=br}	<td style="background:{cycle name=cf1 values="#ddd,#ccc"}">&nbsp;</td>	<td style="background:{cycle name=cf2 values="#ddd,#ccc"}">&nbsp;</td>	{/foreach}	{/if}</tr>{/repeat}<tr><td colspan={count multi=2 offset=5 var=$form.deliver_to} style="padding:0;border-top:1px solid #000;border-bottom:1px solid #000"><img src=ui/pixel.gif width=1 height=1></td></tr><!-- total --><tr height=30>    <td colspan=4 height=100 style="background:none" valign=top>    <h4>Remark</h4>	{$form.remark|nl2br}<br>	<br>    <h4>Remark#2 (For Internal Use)</h4>	{$form.remark2|nl2br}	</td>	<td nowrap>		<b class=small>Total</b><br>	</td>	{if $from_request.with_total_qty}		{assign var=t_ctn value=0}		{assign var=t_pcs value=0}		{foreach from=$form.branches item=br}		{assign var=bid value=$br.id}		{assign var=t_ctn value=$t_ctn+$total.total_ctn.$bid}		{assign var=t_pcs value=$t_pcs+$total.total_pcs.$bid}		{/foreach}		<td>{$t_ctn|qty_nf|ifzero:"&nbsp;"}</td>		<td>{$t_pcs|qty_nf|ifzero:"&nbsp;"}</td>	{else}	{foreach from=$form.branches item=br}	{assign var=bid value=$br.id}	<td>{$total.total_ctn.$bid|qty_nf|ifzero:"&nbsp;"}</td>	<td>{$total.total_pcs.$bid|qty_nf|ifzero:"&nbsp;"}</td>	{/foreach}	{/if}</tr></table></div><br><br><br><table width=100%><tr>{if $config.po_internal_copy_3signatures}<td valign=bottom class=small>_________________<br>Order By<br>Name:</td><td valign=bottom class=small>_________________<br>Approved By<br>Name:</td><td valign=bottom class=small>_________________<br>Accepted By<br>Name:</td>{else}<td width=50% valign=bottom class=small>______________________________<br>Accepted By<br>Name:</td>{/if}<td valign=bottom align=right nowrap><h1>Internal Copy</h1></td></tr></table></div><!-- end loop -->