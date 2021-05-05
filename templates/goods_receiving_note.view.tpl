{*

8/3/2009 3:33:51 PM Andy
- add reset function

12/13/2010 3:48:11 PM Justin
- Added Document No to show PO No when found config['use_grn_future'].

7/11/2011 11:49:21 AM Justin
- Added a config check for use grn future to differentiate the message between Verification and Approval.

8/15/2011 3:42:21 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

9/5/2011 11:25:43 AM Justin
- Added missing of form assigned.
- Added form disable while found the GRN is approved.

6/28/2012 3:50:54 PM Justin
- Added to use different parameter while using future GRN.

8/24/2012 12:01 PM Justin
- Enhanced to show branch code and related invoice as if found it is PO and config is set.

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

3/31/2014 11:16 AM Justin
- Bug fixed on printing option that link to wrong report.

4/3/2014 11:29 AM Justin
- Enhanced to add a new report option "GRN Summary".

4/7/2014 2:07 PM Justin
- Bug fixed on GRN Summary showing the wrong report.

4/8/2014 3:05 PM Justin
- Enhanced to rename the report GRN Summary to GRN Summary (ACC Copy).

4/8/2015 1:49 PM Justin
- Enhanced to allow user can print returned items under account summary.

6/1/2015 11:30 AM Justin
- Enhanced to show invoice no. list while found the GRR is having PO & invoice.

9/4/2015 3:47 PM Andy
- Change to always generate gra when got config "use_grn_future_allow_generate_gra"

03/24/2016 17:15 Edwin
- Modified on rules to show "Reset" button
- Added privilege to reset GRN although user level is lower than reset level required

2/22/2017 4:40 PM Justin
- Enhanced to show "Excluded GST" message for PO amount while it is under GST.

5/4/2017 16:43 Qiu Ying
- Enhanced to remove config grn_have_tax in GRN Future.

5/10/2017 10:16 AM Justin
- Enhanced to show returned items for "items not in PO" when config "use_grn_future_allow_generate_gra" is turned on.

5/22/2017 10:49 AM Justin
- Enhanced to have gst information for returned items.

5/22/2017 10:49 AM Justin
- Removed the testing wordings.

5/23/2017 10:29 AM Justin
- Enhanced to move gst code column next to cost price.
- Enhanced to show gst rate.

4/20/2018 11:11 AM Justin
- Enhanced to show foreign currency information.

8/9/2018 5:36 PM Justin
- Enhanced to show images attached from GRR.

8/27/2018 2:35 PM Justin
- Enhanced to bring back the GRN Tax.

9/26/2018 10:22 AM Justin
- Bug fixed on missing of GRN Tax hidden field.

5/17/2019 2:17 PM William
- Enhance "GRN" word to use report_prefix.

06/22/2020 03:47 PM Sheila
- Updated button color.
*}

{include file=header.tpl}
{literal}
<style>
.sh
{
    background-color:#ff9;
}

.stdframe.active
{
 	background-color:#fea;
	border: 1px solid #f93;
}

#tbditems input
{
	margin:0;
	padding:0;
}

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
function do_print(id,bid){
	document.f_prn.id.value=id;
	document.f_prn.branch_id.value=bid;
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
	$('print_dialog').style.display = 'none';
	document.f_prn.target = "_blank";
	document.f_prn.submit();
	curtain(false);
}

function print_cancel()
{
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function do_reset(){
	if(!allow_reset) {
		alert("You have no privilege to reset GRN.");
		return false;
	}
	
    document.f_do_reset['reason'].value = '';
	var p = prompt('Enter reason to Reset :');
	if (p==null || p.trim()=='' ) return false;
	document.f_do_reset['reason'].value = p;

	if(!confirm('Are you sure to reset?'))  return false;

	document.f_do_reset.submit();
	return false;
}

function curtain_clicked(){
	$('print_dialog').hide();
	curtain(false);
}

function download_image(obj,fp){
	$('_download').src = "goods_receiving_record.php?a=download_photo&f="+fp;
}
{/literal}
var allow_reset = int('{$allow_reset}');
</script>

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name=reason value="">
<input type=hidden name=curr_date value="{$grr.rcv_date}">
</form>

<!-- download grr attachment -->
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>

<h1>GRN (Goods Receiving Note) (ID#{$form.id})</h1>

<h3>Status:
{if $form.active}
	{if $form.status==0}
		New
	{else}
		{if $form.approved==0}
			Waiting for {if $config.use_grn_future}Approval{else}Verification{/if}
		{else}
			Verified
		{/if}
	{/if}
{else}
	Cancelled/Terminated
{/if}
</h3>

{if $form.unfinish_gra_list}
	<div class="stdframe" style="background-color:#FFE4E1;">
		<h5>Unfinish GRA:</h3> 
			You need to finish following GRA:
			{foreach from=$form.unfinish_gra_list item=gra name=fgra}
				{if !$smarty.foreach.fgra.first}, {/if}
				<a href="goods_return_advice.php?a=open&id={$gra.id}&branch_id={$gra.branch_id}" target="_blank">
					{$grr.report_prefix}{$gra.id|string_format:"%05d"}
				</a>
			{/foreach}
	</div>
{/if}


{if $form.finished_gra_list}
	<div class="stdframe" style="background-color:#F0FFF0;">
		<h5>Finished GRA:</h3> 
			{foreach from=$form.finished_gra_list item=gra name=fgra}
				{if !$smarty.foreach.fgra.first}, {/if}
				<a href="goods_return_advice.php?a=view&id={$gra.id}&branch_id={$gra.branch_id}" target="_blank">
					{$grr.report_prefix}{$gra.id|string_format:"%05d"}
				</a>
			{/foreach}
	</div>
{/if}

{include file=approval_history.tpl}

<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
	<table border="0" width="100%">
		<tr>
			<td align="center" rowspan="2"><img src="ui/print64.png"></td>
			<td><h3>Print Options</h3></td>
		</tr>
		<tr>
			<td>
				{if $config.use_grn_future}
					<input type="checkbox" name="print_grn_summary" value="1" checked> GRN Summary (ACC Copy)<br />
					<img src="ui/pixel.gif" width="19"><input type="checkbox" name="print_returned_items" value="1"> Print Returned Items<br />
				{/if}
				<input type=checkbox name="{if !$config.use_grn_future}print_grn_report{else}print_gra_report{/if}"> GRN Report<br />
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<br />
				<input class="btn btn-primary" type="button" value="Print" onclick="print_ok()"> 
				<input class="btn btn-warning" type=button value="Cancel" onclick="print_cancel()">
			</td>
		</tr>
	</table>
</form>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name=f_a>
<div class="stdframe" style="background:#fff">
<h4>General Information</h4>
<table  border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRR No</b></td><td>{$grr.report_prefix}{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|qty_nf} / Pcs:{$grr.grr_pcs|qty_nf}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr>
<tr>
<td><b>Branch</b></td>
<td>{$form.branch_code}</td>
<td><b>GRN Owner</b></td>
<td style="color:blue">{$form.u}</td>
{if $grr.invoice_no}
	<td><b>Invoice No.</b></td>
	<td style="color:blue" colspan="3">{$grr.invoice_no}</td>
{/if}
</tr>
<tr>
<td><b>Department</b></td><td colspan=3>{$form.department|default:$grr.department}</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
{if $config.grn_summary_show_related_invoice && $grr.type eq 'PO'}
<td valign="top"><b>Related Invoice</b></td><td>{$grr.related_invoice}</td>
{/if}
</tr><tr>
<td width=100 valign="top"><b>Document Type.</b></td><td width=100 valign="top"><font color=blue>{$grr.type}</font></td>
<td width=100 valign="top"><b>Document No.</b></td><td width=150 valign="top"><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100 valign="top"><b>PO Amount{if $grr.po_is_under_gst}<br />(Excluded GST){/if}</b></td><td width=100 valign="top"><font color=blue>{$grr.po_amount|number_format:2}</font></td>
<td width=100 valign="top"><b>Partial Delivery</b></td><td width=100 valign="top"><font color=blue>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</font></td>
{/if}
</tr>
{if $grr.currency_code}
	<tr>
		<td><b>Currency</b></td>
		<td>
			<font color="blue">
				{if !$grr.currency_code}
					Base Currency
				{else}
					{$grr.currency_code}
				{/if}
			</font>
		</td>
		{if $grr.currency_code}
			<td><b>Exchange Rate</b></td>
			<td>
				<font color="blue">{$grr.currency_rate|default:1}</font>
			</td>
		{/if}
	</tr>
{/if}

<tr>
	<td><b>Tax</b> <a href="javascript:void(alert('{$LANG.GRR_TAX_PERCENT_INFO|escape:javascript}'));">[?]</a></td>
	<td>
		{$form.grn_tax|number_format:2} %
		<input type="hidden" name="grn_tax" value="{$form.grn_tax}" />
	</td>
	<td><b>Total GRR Tax</b></td>
	<td>
		{$grr.grr_tax|number_format:2|default:0}
	</td>
</tr>

{if $photo_items}
	<tr>
		<td colspan="8">
			{foreach from=$photo_items item=p name=i}
				<div class="imgrollover">
					<div align="center" width="auto" height="auto">
						<img width="110" height="100" align="absmiddle" vspace="4" hspace="4" alt="Photo #{$smarty.foreach.i.iteration}" src="/thumb.php?w=110&h=100&cache=1&img={$p.image_file|urlencode}" border="0" style="cursor:pointer" onClick="show_sku_image_div('{$p.image_file|escape:javascript}');" title="View">
					</div>
					
					{if $form.grr_id}
						<img src="/ui/application_put.png" align="absmiddle" valign="right" onclick="download_image(this.parentNode,'{$p.download_file|urlencode}')"> Download
					{/if}
				</div>
			{/foreach}
		</td>
	</tr>
{/if}
</table>


{if $config.use_grn_future_allow_generate_gra}
	* This GRN will generate all returned items to become GRA once approved.
{/if}

</div>


<div id=tblist>
{if $form.approved}{assign var=manager_col value=1}{/if}
{if $config.use_grn_future}
	{include file=goods_receiving_note2.view.list.tpl}
{else}
	{include file=goods_receiving_note.view.list.tpl}
{/if}
</div>
</form>
{if ($form.non_sku_items || $have_grn_returned_items) && $config.use_grn_future}
	{assign var=ttl_pcs value=0}
	{assign var=ttl_nsi_gross_amt value=0}
	{assign var=ttl_nsi_gst_amt value=0}
	{assign var=ttl_nsi_amt value=0}
	<br><h2>Returned Item(s)</h2>
	<div style="overflow:auto;">
	<table width=100% cellpadding=2 cellspacing=1 border=0 style="border:1px solid #000">
		<thead>
		<tr height=32 bgcolor="#ffee99" class="small">
			<th>#</th>
			<th width="20%">Code</th>
			<th width="60%">Description</th>
			<th>Cost Price</th>
			{if $form.is_under_gst}
				<th>GST Code</th>
			{/if}
			<th>Rcv Qty<br />(Pcs)</th>
			<th>Amount</th>
			{if $form.is_under_gst}
				<th>GST</th>
				<th>Amount<br />Include GST</th>
			{/if}
		</tr>
		</thead>
	
		<tbody id="tbditems">
			{if $form.non_sku_items}
				{foreach from=$form.non_sku_items key=sku_code item=item name=fitem}
					{assign var=n value=$smarty.foreach.fitem.iteration-1}
					{if $form.non_sku_items.code.$n}
						<!--{$ri_count++}-->
						{assign var=ttl_pcs value=$ttl_pcs+$form.non_sku_items.qty.$n}
						{assign var=row_gross_amt value=$form.non_sku_items.qty.$n*$form.non_sku_items.cost.$n}
						{assign var=row_gross_amt value=$row_gross_amt|round2}
						{assign var=ttl_nsi_gross_amt value=$ttl_nsi_gross_amt+$row_gross_amt}
						<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
							<td nowrap width="2%" align="right">{$ri_count}.</td>
							<td>{$form.non_sku_items.code.$n}</td>
							<td>{$form.non_sku_items.description.$n}</td>
							<td align="right">{$form.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points:".":""}</td>
							{if $form.is_under_gst}
								<td nowrap>{$form.non_sku_items.gst_code.$n} ({$form.non_sku_items.gst_rate.$n|default:'0'}%)</td>
							{/if}
							<td class="r" width="5%">{$form.non_sku_items.qty.$n|default:0}</td>
							<td class="r" width="5%">{$row_gross_amt|round2}</td>
							{if $form.is_under_gst}
								{assign var=row_gst_rate value=$form.non_sku_items.gst_rate.$n}
								{assign var=row_gst_amt value=$row_gross_amt*$row_gst_rate/100}
								{assign var=row_gst_amt value=$row_gst_amt|round:2}
								{assign var=row_amt value=$row_gross_amt+$row_gst_amt}
								{assign var=ttl_nsi_gst_amt value=$ttl_nsi_gst_amt+$row_gst_amt}
								{assign var=ttl_nsi_amt value=$ttl_nsi_amt+$row_amt}
								
								<td class="r">{$row_gst_amt|number_format:2}</td>
								<td class="r">{$row_amt|number_format:2}</td>
							{/if}
						</tr>
					{/if}
				{/foreach}
			{/if}

			{if $have_grn_returned_items}
				{foreach from=$form.items item=item name=i key=iid}
					{if $item.item_check}
						<!--{$ri_count++}-->
						{assign var=row_qty value=`$item.ctn*$item.uom_fraction+$item.pcs`}
						{assign var=ttl_pcs value=$ttl_pcs+$row_qty}
						{assign var=row_gross_amt value=`$item.cost*$row_qty/$item.uom_fraction`}
						{assign var=row_gross_amt value=$row_gross_amt|round2}
						{assign var=ttl_nsi_gross_amt value=$ttl_nsi_gross_amt+$row_gross_amt|round2}
						<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
							<td nowrap width="2%" align="right">{$ri_count}.</td>
							<td>{$item.sku_item_code}</td>
							<td>{$item.description}</td>
							<td align="right">{$item.cost|number_format:$config.global_cost_decimal_points:".":""}</td>
							{if $form.is_under_gst}
								<td nowrap>{$item.gst_code} ({$item.gst_rate|default:'0'}%)</td>
							{/if}
							<td class="r" width="5%">{$row_qty|qty_nf}</td>
							<td class="r" width="5%">{$row_gross_amt|round2}</td>
							{if $form.is_under_gst}
								{assign var=row_gst_rate value=$item.gst_rate}
								{assign var=row_gst_amt value=$row_gross_amt*$row_gst_rate/100}
								{assign var=row_gst_amt value=$row_gst_amt|round:2}
								{assign var=row_amt value=$row_gross_amt+$row_gst_amt}
								{assign var=ttl_nsi_gst_amt value=$ttl_nsi_gst_amt+$row_gst_amt}
								{assign var=ttl_nsi_amt value=$ttl_nsi_amt+$row_amt}
								
								<td class="r">{$row_gst_amt|number_format:2}</td>
								<td class="r">{$row_amt|number_format:2}</td>
							{/if}
						</tr>
					{/if}
				{/foreach}
			{/if}
		</tbody>
	
		<tfoot>
			<tr height="24" bgcolor="#ffee99">
				{assign var=colspan value=4}
				{if $form.is_under_gst}
					{assign var=colspan value=$colspan+1}
				{/if}
				<td colspan="{$colspan}" align="right"><b>Total</b></td>
				<td align="right" id="total_qty">{$ttl_pcs|default:0}</td>
				<td align="right" id="total_amt">{$ttl_nsi_gross_amt|default:0}</td>
				{if $form.is_under_gst}	
					<td align="right">{$ttl_nsi_gst_amt|number_format:2|default:0}</td>						
					<td align="right">{$ttl_nsi_amt|number_format:2|default:0}</td>						
				{/if}
			</tr>
		</tfoot>
	</table>
	</div>
{/if}

<p align=center>
{if $form.status==1 && $form.active==1 && $form.approved==1}
<input class="btn btn-primary" type=button value="Print" onclick="do_print({$form.id},{$form.branch_id})">
	{if $form.approved and $allow_reset}
	    <input class="btn btn-warning" type=button value="Reset" onclick="do_reset();">
	{/if}
{/if}
<input class="btn btn-error" class="btn btn-error" type=button value="Close" onclick="close_window('/goods_receiving_note.php')">
</p>
{include file=footer.tpl}
{if $config.use_grn_future && $form.approved}
<script>
Form.disable(document.f_a);
</script>
{/if}
