{*
10/24/2014 4:42 PM Justin
- Enhanced to have terms, order qty (ctn / pcs), item location and part code.
- Enhanced to show po no under "Your Preference" and debtor's term under "Terms".

7/1/2015 3:19 PM Justin
- Enhanced to take off the crop for description.

4/27/2016 9:50 AM Andy
- Enhanced to put in T&C for Cash Sales DO.
*}
{if !$skip_header}
{include file='header.print.tpl'}
<style>
{if $config.do_printing_no_item_line}
{literal}
.no_border_bottom td{
	border-bottom:none !important;
}
.total_row{
    border-top:1px solid black !important;
}
.td_btm_got_line td,.td_btm_got_line th{
    border-bottom:1px solid black !important;
}
{/literal}
{/if}

{literal}
body{
  font-size:9pt;
}
.tb{
	font-size: 9pt;
}
.tb th,.tb td{
}
.artno_col{
	padding: 0 20px;
	text-align:left;
}
{/literal}
</style>
<script type="text/javascript">
var doc_no = '{$form.do_no}';
{literal}
function start_print(){//return;
	document.title = doc_no;
	window.print();
}
{/literal}
</script>

<body onload="start_print();">
{/if}

{if $form.do_type eq 'transfer' and $config.do_transfer_have_discount}
	{assign var=show_invoice value=1}
{elseif $form.do_type eq 'open' and $config.do_cash_sales_have_discount}
    {assign var=show_invoice value=1}
{elseif $form.do_type eq 'credit_sales' and $config.do_credit_sales_have_discount}
    {assign var=show_invoice value=1}
{/if}

{assign var=currency_multiply value=1}
{if $form.do_type eq 'transfer' and $config.consignment_modules and $config.masterfile_branch_region and $config.consignment_multiple_currency and $form.exchange_rate>1}
	{assign var=is_currency_mode value=1}
	{assign var=exchange_rate value=$form.exchange_rate}
	
	{if $form.price_indicate ne 1}
		{assign var=currency_multiply value=$currency_multiply*$exchange_rate}
	{/if}
{/if}

<!-- print sheet -->
<div class=printarea>
<table width=100% cellspacing=0 cellpadding=0 border=0 class="" style="font-size:11pt;">
<tr>
	<td colspan="2">
		<img src="/ui/pixel.gif" width="600" height="100" /><br />
		<!--<span style="font-size: 8pt">
		{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}
		{$from_branch.address|nl2br}<br>
		Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
		{if $from_branch.phone_3}&nbsp;&nbsp; Fax: {$from_branch.phone_3}{/if}
		</span>-->
	</td>
	<td rowspan="2" align="right" valign="bottom" style="padding-bottom:5px; width:200;">
	    <table width="100%" cellspacing="0" cellpadding="5" border="0">
		<tr>
			<td colspan="2" nowrap>
				<div style="font-size:26pt; padding:4px;color:#666" align="right">
					<b>DELIVERY ORDER</b><br />
				</div>
				<div style="font-size:16pt; padding:4px;color:#666" align="center">
					NO.{$form.do_no}
				</div>
			</td>
		</tr>
		<tr height=22>
			<td style="border-top:1px solid #000;border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;" width="40%" nowrap><b>Account No.:</b></td>
			<td style="border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;" width="60%" nowrap>
				{if !$form.do_branch_id && $form.open_info.name}
					{$form.open_info.name}
				{elseif $form.do_type eq 'credit_sales'}
					{$form.debtor_code}
				{else}
					{$from_branch.description}
				{/if}
				&nbsp;
			</td>
		</tr>
		<tr height=22>
			<td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;" nowrap><b>Your Preference:</b></td>
			<td style="border-right:1px solid #000; border-bottom:1px solid #000; border-bottom:1px solid #000;" nowrap>{$form.po_no|default:'&nbsp;'}</td>
		</tr>
		<tr height=22>
			<td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;" nowrap><b>Date</b></td>
			<td style="border-right:1px solid #000; border-bottom:1px solid #000;" nowrap>{$form.do_date|date_format:$config.dat_format}</td>
		</tr>
		{if $form.do_type eq 'credit_sales'}
			<tr height=22>
				<td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;" nowrap><b>Terms</b></td>
				<td style="border-right:1px solid #000; border-bottom:1px solid #000;" nowrap>{$to_debtor.term|default:'&nbsp;'}</td>
			</tr>
		{/if}
		<tr height=22>
			<td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;" nowrap><b>Sales Person</b></td>
			<td style="border-right:1px solid #000;border-bottom:1px solid #000; border-bottom:1px solid #000;" nowrap>{$form.sa_code_list|default:"--"}</td>
		</tr>
		<tr height="22"><td style="border-left:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;" colspan="2" align="center"><b>{$page}</b></tr>
	  	</table>
	</td>
</tr>
<tr>
<td colspan=2>
	{if !$form.do_branch_id && $form.open_info.name}
		<table width=100% cellspacing=5 cellpadding=0 border=0 height="100px">
		<tr>
			<td valign=top style="width:50%; border:1px solid #000; padding:5px">
				<h5>Sold To:</h5>
				{$form.open_info.name}
				{$form.open_info.address|nl2br}
			</td>
			<td valign=top style="width:50%; border:1px solid #000; padding:5px">
				<h5>Delivery To: </h5>
				{$form.remark|default:$form.checkout_remark|nl2br}
			</td>
		</tr>
		</table>
	{elseif $form.do_type eq 'credit_sales'}
	    <table width=100% cellspacing=5 cellpadding=0 border=0 height="100px">
		<tr>
			<td valign=top style="width:50%; border:1px solid #000; padding:5px">
				<h5>Sold To:</h5>
				{$form.debtor_description}<br />
				{$form.debtor_address|nl2br}
			</td>
			<td valign=top style="width:50%; border:1px solid #000; padding:5px">
				<h5>Delivery To: </h5>
				{$form.remark|default:$form.checkout_remark|nl2br}
			</td>
		</tr>
		</table>
	{else}
		<table width=100% cellspacing=5 cellpadding=0 border=0 height="100px">
		<tr>
			<td valign=top style="width:50%; border:1px solid #000; padding:5px;">
			<h5>Sold To:</h5>
				{$to_branch.code} - {$to_branch.description}<br />
				{if !$form.use_address_deliver_to || (!$form.address_deliver_to && $form.use_address_deliver_to)}
					{$to_branch.address|nl2br}
				{else}
					{$form.address_deliver_to|nl2br}
				{/if}
				<br>
				Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
				{if $to_branch.phone_3}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax: {$to_branch.phone_3}{/if}
			</td>
			<td valign=top style="width:50%; border:1px solid #000; padding:5px;">
				<h5>Delivery To:</h5>
				{$form.remark|default:$form.checkout_remark|nl2br}
			</td>
		</tr>
		</table>	
	{/if}

</td>
</tr>
</table>
<br />
<table border=0 cellspacing=0 cellpadding=4 width=100% class="tb" style="border-left:none;">
<tr>
	<th style="border-left:1px solid #000;" width=5>ITEM</th>
	<th nowrap>PART CODE</th>
	<th nowrap>Q.C.NO.</th>
	<th nowrap>LOC</th>
	<th width="80%">DESCRIPTION</th>
	<th width="40">PACKING</th>
	<th width=50 align="center">ORDER<br />QTY</th>
	<th width=50 align="center">TOTAL<br />QTY</th>
</tr>

{assign var=counter value=0}

{foreach from=$do_items key=item_index item=r name=i}
	<!-- {$counter++} -->
	<tr class="no_border_bottom {if $smarty.foreach.i.iteration eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
		<td style="border-left:1px solid #000;" align=center>
			{if !$page_item_info.$item_index.not_item}
				{$r.item_no+1}.
			{else}
				&nbsp;
			{/if}
		</td>
		{assign var=sku_item_code value=$r.sku_item_code}
		<td class="left" nowrap>{$r.parent_artno|default:'&nbsp;'}</td>
		<td class="left" nowrap>{$r.artno|default:'&nbsp;'}</td>
		<td align="center">{$r.location|default:'&nbsp;'}</td>
		<td width="90%">{$r.description}</td>
		
		{if !$page_item_info.$item_index.not_item}
			{assign var=cost_price value=$r.cost_price}
			
			<!-- DO Markup -->
			{if $form.do_markup_arr.0}
				{assign var=adjust_cost value=$form.do_markup_arr.0*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}
			{if $form.do_markup_arr.1}
				{assign var=adjust_cost value=$form.do_markup_arr.1*$cost_price/100}
				{assign var=cost_price value=$cost_price+$adjust_cost}
			{/if}

			{assign var=p_markup value=$markup+100}
			{assign var=p_markup value=$p_markup/100}
			{assign var=row_qty value=$r.ctn*$r.uom_fraction+$r.pcs}
			{assign var=cost_price value=`$p_markup*$cost_price`}
			<td align="center">{$r.uom_code}</td>
			<td align="center" nowrap>{if $r.ctn}{$r.ctn|qty_nf} Ctn{/if}{if $r.ctn && $r.pcs} / {/if}{if $r.pcs}{$r.pcs|qty_nf} Pcs{/if}</td>
			<td align="right">{$row_qty|qty_nf}</td>
			
			{assign var=amt_ctn value=$cost_price*$r.ctn}
			{assign var=amt_pcs value=$cost_price/$r.uom_fraction*$r.pcs}
			{assign var=total_row value=$amt_ctn+$amt_pcs|round2}
			{assign var=total_row value=$total_row|round2}
			{assign var=total value=$total+$total_row}
			{assign var=total_qty value=$row_qty+$total_qty}
		{else}
			<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
		{/if}
	</tr>
{/foreach}

{assign var=s2 value=$counter}
{section name=s start=$counter loop=$PAGE_SIZE}
<!-- filler -->
{assign var=s2 value=$s2+1}
<tr height=20 class="no_border_bottom {if $s2 eq $PAGE_SIZE and !$is_lastpage}td_btm_got_line{/if}">
	<td style="border-left:1px solid #000;">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
{/section}

{if $is_lastpage}
<tr class="total_row" style="border-left:none;">
	<td align=left colspan="6" class="total_row" style="border-bottom:none;border-right:none;border-left:none;">
		Goods received in good order & condition.
	</td>
	<td align="right" class="total_row" style="border-bottom:none;" nowrap><b>TOTAL QTY</b></td>
	<td align="right" class="total_row">{$total_qty|qty_nf}</td>
</tr>
{assign var=total value=0}
{assign var=total_qty value=0}
{/if}
</table>

{if $is_lastpage}
<br />
<table>
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="float:left;">
				<tr>
					<td style="width:200px; padding-bottom:80px; border-left:1px solid #000; border-top:1px solid #000;">&nbsp;</td>
					<td style="width:200px; vertical-align:top; padding-bottom:30px; border-left:1px solid #000; border-top:1px solid #000;">
						<p style="font-size:12px; padding-top:5px; padding-left:5px;">
						Lorry No.:<br />
						I/C No.:
						</p>
					</td>
					<td style="width:200px; padding-bottom:30px; border-left:1px solid #000; border-top:1px solid #000;">&nbsp;</td>
					<td style="width:200px; padding-bottom:30px; border-left:1px solid #000; border-top:1px solid #000; border-right:1px solid #000;">&nbsp;</td>
				</tr>
				<tr>
					<td align="center" style="border:1px solid #000; border-right:0px;"><b>CHECKED BY</b></td>
					<td align="center" style="border:1px solid #000; border-right:0px;"><b>SIGNATURE OF LORRY DRIVER</b></td>
					<td align="center" style="border:1px solid #000; border-right:0px;"><b>CONSIGNEE'S SIGNATURE & CHOP</b></td>
					<td align="center" style="border:1px solid #000;"><b>APPROVED SIGNATURE</b></td>
				</tr>
			</table>
		</td>
		<td>
			{if $form.do_type eq 'open'}
				<div>
					N.E<br />
					1. Goods sold are not returnable or exchangeable.<br />
					2. Upon delivery, please check goods are in good condition before signoff on delivery order.<br />
					3. All cheque must be made payable to <b>"Tiles City Sdn Bhd"</b>.<br />
				</div>
			{/if}
		</td>
	</tr>
</table>




{/if}
</div>
