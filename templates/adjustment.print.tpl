{*
9/15/2008 2:03:22 PM yinsee
- tweak printing, add cost total column

1/9/2009 11:05:47 AM yinsee
- remove the triple-description test (last time forgot to remove!!)

6/22/2009 5:00 PM Andy
- Add No horizontal line setting

1/13/2010 4:56:56 PM Andy
- Add config to manage item got line or not

6/18/2010 5:13:53 PM Andy
- Fix adjustment printing if remark too long will make alignment run.

7/15/2011 11:21:35 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

7/27/2011 4:19:23 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config.

8/19/2013 9:31 AM Fithri
- assign document no as filename when print

12/11/2013 2:13 PM Justin
- Enhanced to show offline ID.

4/20/2017 3:02 PM Khausalya
- Enhanced changes from RM to use config setting. 

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.

12/27/2018 10:45 AM Justin
- Enhanced to print barcode on top of the document number.

6/18/2019 3:50 PM William
- Added new Vertical print format.

10/22/2020 5:49 PM Andy
- Enhanced Adjustment Printing to can choose what sku fields to show (ARMS Code / MCode / Art No / Link Code).
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}
	
<style>
{if $config.adj_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row td, .total_row th{
    border-top: 1px solid #000;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}

{literal}
.hd {
	background-color:#ddd;
}
.rw {
	background-color:#fff;
}
.rw2 {
	background-color:#eee;
}
.ft {
	background-color:#eee;
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 7pt;
}

{/literal}
</style>

<script type="text/javascript">

var doc_no = '{$form.report_prefix}{$form.id|string_format:"%05d"}';

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
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr valign=top>
{if $system_settings.logo_vertical eq 1}
	<td colspan="2">
		<table width="100%" style="text-align: center;">
			<tr>
				<td><img style="max-width: 600px;max-height: 80px;" src="{get_logo_url mod='adjustment'}" height=80 hspace=5 vspace=5></td>
			</tr>
			{if $system_settings.verticle_logo_no_company_name neq 1}
			<tr>
				<td><h2>{$form.b_description} {if $form.b_company_no}({$form.b_company_no}){/if}</h2></td>
			</tr>
			{/if}
			<tr>
				<td>{$form.b_address}</td>
			</tr>
			<tr>
				<td>
				Tel: {$form.b_phone_1}{if $form.b_phone_2} / {$form.b_phone_2}{/if}
				{if $form.b_phone_3}
				&nbsp;&nbsp; Fax: {$form.b_phone_3}
				{/if}
				</td>
			</tr>
		</table>
	</td>
{else}
	<td><img src="{get_logo_url mod='adjustment'}" height=80 hspace=5 vspace=5></td>
	<td width=100% class="small">
	<h2>{$form.b_description} {if $form.b_company_no}({$form.b_company_no}){/if}</h2>
	{$form.b_address|nl2br}<br>
	Tel: {$form.b_phone_1}{if $form.b_phone_2} / {$form.b_phone_2}{/if}
	{if $form.b_phone_3}
	&nbsp;&nbsp; Fax: {$form.b_phone_3}
	{/if}
	</td>
{/if}
	<td rowspan="2" align=right>
	    <table>
			<tr height=22><td colspan=2 style="background:#000;padding:4px;color:#fff" align=center><b>Adjustment</b></td></tr>
			<tr bgcolor="#cccccc" height=22>
				<td nowrap>Adj No.</td>
				<td {if $config.print_document_barcode}align="center" style="padding:0;"{/if} nowrap>
					{if $config.print_document_barcode}
						<span class="barcode3of9" style="padding:0;">
							*{$form.report_prefix}{$form.id|string_format:"%05d"}*
						</span>
					{/if}
					
					<div {if $config.print_document_barcode}style="margin-top:-5px;"{/if}>
						{$form.report_prefix}{$form.id|string_format:"%05d"}
					</div>
				</td>
			</tr>
			{if $form.offline_id}
				<tr height=22 bgcolor="#cccccc"><td nowrap>Offline ID</td><td nowrap>#{$form.offline_id|string_format:"%05d"}</td></tr>
			{/if}
		    <tr height=22><td nowrap>Adj Date</td><td nowrap>{$form.adjustment_date|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed Date</td><td nowrap>{$smarty.now|date_format:$config.dat_format}</td></tr>
			<tr bgcolor="#cccccc" height=22><td nowrap>Printed By</td><td nowrap>{$sessioninfo.u|default:'&nbsp;'|upper}</td></tr>
			<tr bgcolor="#cccccc" height=22><td colspan=2 align=center>{$page}</td></tr>
	  	</table>
	</td>
</tr>
<tr>
	<td colspan="2">
		<table class="normal" border=0 cellspacing=0 cellpadding=1>
			<tr>
			<th align=left nowrap>Department : </th>
			<td align=left width=10%>{$form.dept}</td>
			<th align=left nowrap>Adjustment Type : </th>
			<td align=left width=30%>{$form.adjustment_type}</td>
			{if $form.remark}
			<th align=left nowrap>Remark : </th>
			<td align=left width=30%>{$form.remark}</td>
			{/if}
		</tr>
		</table>
	</td>
</tr>
</table>

<br>

<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb">

<tr bgcolor=#cccccc>
	<th rowspan=2>&nbsp;</th>
	{if $smarty.request.print_col.sku_item_code}
		<th rowspan=2>ARMS Code</th>
	{/if}
	
	{if $smarty.request.print_col.artno}
		<th rowspan=2>Art No</th>
	{/if}
	
	{if $smarty.request.print_col.mcode}
		<th rowspan=2>MCode</th>
	{/if}
	
	{if $smarty.request.print_col.link_code}
		<th rowspan=2>{$config.link_code_name}</th>
	{/if}
	
	<th rowspan=2 width=40%>SKU Description</th>
	<th rowspan=2>Unit<br>Cost</th>
	<th colspan=2>Qty</th>
	<th rowspan=2>Cost<br>({$config.arms_currency.symbol})</th>
</tr>

<tr bgcolor=#cccccc>
	<th>Positive</th>
	<th>Negative</th>
</tr>

{assign var=counter value=0}

{section name=i loop=$adjust_items}
	{if $adjust_items[i].doc_allow_decimal}
		{assign var=qty_dp value=$config.global_qty_decimal_points}
	{else}
		{assign var=qty_dp value=2}
	{/if}
	<!-- {$counter++} -->
	<tr class="no_border_bottom">
		<td align=center>{$start_counter+$counter}.</td>
		
		{if $smarty.request.print_col.sku_item_code}
			<td nowrap>{$adjust_items[i].sku_item_code|default:"&nbsp;"}</td>
		{/if}
		
		{if $smarty.request.print_col.artno}
			<td nowrap>{$adjust_items[i].artno|default:"&nbsp;"}</td>
		{/if}
		
		{if $smarty.request.print_col.mcode}
			<td>{$adjust_items[i].mcode|default:'&nbsp;'}</td>
		{/if}
		
		{if $smarty.request.print_col.link_code}
			<td>{$adjust_items[i].link_code|default:'&nbsp;'}</td>
		{/if}
		
		<td><div class="crop">{$adjust_items[i].description}</div></td>
		<td align=right>
		{$adjust_items[i].cost|number_format:$config.global_cost_decimal_points}
		</td>
		{assign var=qty value=$adjust_items[i].qty|round:$qty_dp}
		<td align=right>{if $qty>0}{$qty|number_format:$qty_dp}{assign var=p_qty value=$p_qty+$qty}{else}&nbsp;{/if}</td>	
		<td align=right>{if $qty<0}{$qty|abs|number_format:$qty_dp}{assign var=n_qty value=$n_qty+$qty}{else}&nbsp;{/if}</td>
		<td align=right>
		{assign var=cost_amt value=$qty*$adjust_items[i].cost}
		{$cost_amt|number_format:2}
		{assign var=totalcost value=$totalcost+$cost_amt|round:2}
		</td>
	</tr>
{/section}

{repeat s=$counter+1 e=$item_per_page}
<!-- filler -->
<tr height=20 class="no_border_bottom">
	{if $smarty.request.print_col.sku_item_code}
		<td>&nbsp;</td>
	{/if}
	
	{if $smarty.request.print_col.artno}
		<td>&nbsp;</td>
	{/if}
	
	{if $smarty.request.print_col.mcode}
		<td>&nbsp;</td>
	{/if}
	
	{if $smarty.request.print_col.link_code}
		<td>&nbsp;</td>
	{/if}
	
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/repeat}

<tr bgcolor=#cccccc align=right class="total_row">
	{assign var=cols value=3}
	{if $smarty.request.print_col.sku_item_code}{assign var=cols value=$cols+1}{/if}
	{if $smarty.request.print_col.artno}{assign var=cols value=$cols+1}{/if}
	{if $smarty.request.print_col.mcode}{assign var=cols value=$cols+1}{/if}
	{if $smarty.request.print_col.link_code}{assign var=cols value=$cols+1}{/if}
	<th colspan="{$cols}">Total</th>
	<th>{$p_qty|default:0|qty_nf}</th>
	<th>{$n_qty|default:0|abs|qty_nf}</th>
	<th>{$totalcost|number_format:2}</th>
</tr>


</table>
<!--table class="tbd small" cellpadding=4 cellspacing=0 border=0 width=100%>
<tr bgcolor=#cccccc>
	<th width=80>&nbsp;</th>
	<th>Name</th>
	<th>Signature</th>
	<th>Date</th>
	<th>Time</th>
</tr>
<tr height=50>
	<td><b>Issued By</b></td>
	<td align=center>{$sessioninfo.fullname}</td>
	<td>&nbsp;</td>
	<td align=center>{$smarty.now|date_format:"%d/%m/%Y"}</td>
	<td align=center>{$smarty.now|date_format:"%H:%M:%S"}</td>
</tr>
<tr height=50>
	<td><b>Checking By</b></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr height=50>
	<td><b>Received By</b></td>
	<td valign=top>

	Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {$form.checkout_info.name|default:'&nbsp;'}<br>
	IC No. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {$form.checkout_info.nric|default:'&nbsp;'}<br>
	Lorry No. : {$form.checkout_info.lorry_no|default:'&nbsp;'}<br>

	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
</table-->

</div>
