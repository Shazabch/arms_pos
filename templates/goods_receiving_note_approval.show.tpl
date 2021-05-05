{*
7/8/2011 5:45:11 PM Justin
- Fixed the bugs where all the buttons are missing.

7/11/2011 11:54:42 AM Justin
- Added string format to become 5 digits for GRN ID.

7/27/2011 11:34:42 AM Justin
- Re-aligned all the fields to include ctn and return ctn column.

8/8/2011 11:05:11 AM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/21/2013 6:10 PM Justin
- Enhanced to compatible with IBT DO.

2/22/2013 4:38 PM Justin
- Enhanced to hide Account Verification option from Reject menu while found config "grn_future_skip_acc_verify" is set.

12/23/2013 10:23 AM Fithri
- new module 'Stucked Documents Approval'

11/18/2014 5:26 PM Justin
- Enhanced to show GST column and calculation.

3/25/2015 2:33 PM Justin
- Enhanced to have between nett selling price or GST selling price while doing current vs suggested selling price.

4/8/2015 4:21 PM Justin
- Bug fixed on fraction had picked up as 0.

7/16/2015 1:37 PM Joo Chia
- Remark existing table and add in same table tpl as view GRN

2/22/2017 4:40 PM Justin
- Enhanced to show "Excluded GST" message for PO amount while it is under GST.

5/4/2017 16:43 Qiu Ying
- Enhanced to remove config grn_have_tax in GRN Future

5/10/2017 10:16 AM Justin
- Enhanced to show returned items for "items not in PO" when config "use_grn_future_allow_generate_gra" is turned on.

4/20/2018 11:11 AM Justin
- Enhanced to show foreign currency information.

8/9/2018 5:36 PM Justin
- Enhanced to show images attached from GRR.

8/27/2018 2:35 PM Justin
- Enhanced to bring back the GRN Tax.

5/22/2019 10:47 AM William
- Enhance "GRR" word to use report_prefix.

6/4/2019 10:47 AM William
- Enhance "ID" word to use report_prefix.
*}

<hr>
{include file=approval_history.tpl}

<form name="f_do_reject" method="post" onsubmit="do_reject(); return false;">
	<div id="reject_dialog" style="padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:250px; left:250px; display:none;">
		<div class=small style="position:absolute; right:10px;">
			<a href="javascript:void(curtain_clicked())"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
		</div>
		<h3>Reject Options</h3>
		<div class="stdframe">
			<input type="checkbox" name="confirm_grn" value="1" onclick="reject_all()"> Confirmed GRN <br />
			{if $grr.type eq 'PO' || $grr.is_ibt_do}
				<input type="checkbox" name="div1" value="1" onclick="reject_all('div1');"> {if $grr.is_ibt_do}DO{else}PO{/if} Variance<br />
			{/if}
			<input type="checkbox" name="div2" value="1" onclick="reject_all('div2');"> SKU Apply<br />
			{if $grr.type eq 'PO' || $grr.is_ibt_do}
				<input type="checkbox" name="div3" value="1"> Price Change<br />
			{/if}
			{if !$config.grn_future_skip_acc_verify}
				<input type="checkbox" name="div4" value="1" onclick="reject_all('div4');"> Account Verification<br />
			{/if}
			<input type=hidden name="a" value="reject">
			<input type=hidden name="branch_id" value="{$form.branch_id}">
			<input type=hidden name="id" value="{$form.id}" >
			<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
		</div>
		<br />
		<div align="center">
			<input type="submit" value="Ok"> &nbsp;
			<input type="button" value="Cancel" onclick="hidediv('reject_dialog'); curtain(false);">
		</div>
	</div>
</form>

<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:120px;position:absolute; padding:10px; display:none;">
<img src=ui/print64.png hspace=10 align=left> <h3>Summary Report</h3>
GRN Report must be printed, print now?<br>
<p align=center>
	<input type=button value="Print" onclick="print_ok()">
	<input type=button value="Cancel" onclick="cancel()">
</p>
</div>

<form name="f_do_reset" method="post" style="display:none;">
<input type=hidden name="a" value="do_reset">
<input type=hidden name="branch_id" value="{$form.branch_id}">
<input type=hidden name="id" value="{$form.id}" >
<input type=hidden name=reason value="">
</form>

<!-- download grr attachment -->
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>

<h3>Status:
{if $form.active}
	{if $form.status==0 || $form.authorized==0}
		New
	{else}
		{if $form.authorized==1}
			Waiting for Approval
		{/if}
	{/if}
{else}
	Cancelled/Terminated
{/if}
({$grr.report_prefix}#{$form.id|string_format:"%05d"})
</h3>

<form name="f_a" method=post>
<div class="stdframe" style="background:#fff">
<h4>General Information</h4>
<input type="hidden" name="a" value="" />
<input type="hidden" name="comment" value="" />
<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
<input type="hidden" name="approvals" value="{$form.approvals}">
<input type="hidden" name="type" value="{$grr.type}">
<input type="hidden" name="generate_gra" value="{$form.generate_gra}">
{if $approval_on_behalf}
<input type="hidden" name="on_behalf_of" value="{$approval_on_behalf.on_behalf_of}" />
<input type="hidden" name="on_behalf_by" value="{$approval_on_behalf.on_behalf_by}" />
{/if}
<table  border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRR No</b></td><td>{$grr.report_prefix}{$grr.grr_id|string_format:"%05d"}</td>
<td><b>GRR ID</b></td><td>#{$grr.grr_item_id}</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr>
<tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|qty_nf} / Pcs:{$grr.grr_pcs|qty_nf}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr>
<tr>
<td><b>GRN Owner</b></td>
<td style="color:blue">{$form.u}</td>
</tr>
<tr>
<td><b>Department</b></td><td colspan=3>
<input type=hidden name=department_id value="{$form.department_id|default:default:$grr.department_id}">
{$form.department|default:$grr.department}
</td>
</tr>
<tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
</tr>
<tr>
<td width=100 valign="top"><b>Document Type.</b></td><td width=100 valign="top"><font color=blue>{$grr.type}</font></td>
<td width=100 valign="top"><b>Document No.</b></td><td width=150 valign="top"><font color=blue><input type="hidden" name="doc_no" value="{$grr.doc_no}">{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100 valign="top"><b>PO Amount{if $grr.po_is_under_gst}<br />(Excluded GST){/if}</b></td><td width=100 valign="top"><font color=blue>{$grr.po_amount|number_format:2}</font></td>
<td width=100 valign="top"><b>Partial Delivery</b></td><td width=150 valign="top"><font color=blue>{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}</font></td>
<input type="hidden" name="ttl_grr_amt" value="{$grr.po_amount|round2}">
{else}
	<input type="hidden" name="ttl_grr_amt" value="{$grr.grr_amount|round2}">
{/if}
</tr>

{if $config.foreign_currency || ($grr.currency_code && $grr.use_po_currency)}
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
	<td><input type="text" name="grn_tax" value="{$form.grn_tax}" class="r" size="5" readonly /> %</td>
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
	{*<tr>
	    <td><b>Generate GRA</b></td>
	    <td>
			<img src="{if $form.generate_gra}ui/checked.gif{else}ui/unchecked.gif{/if}" style="vertical-align:top;" title="{if $form.generate_gra}This GRN allow to generate all returned items become GRA.{else}This GRN will not generate GRA for those returned items.{/if}">
		</td>
	</tr>*}
	* This GRN will generate all returned items to become GRA once approved.
{/if}
</div>

<div id="tblist">
{* set tables that to be print on the list *}

{if $grr.type eq 'PO' || $grr.is_ibt_do}
	<br />
	{assign var=tbl_val value=-1}
	{assign var=tbl_loop value=4}
{else}
	{assign var=tbl_val value=0}
	{assign var=tbl_loop value=2}
{/if}

{* print different tables based on the table loop set *}
{*
{section loop=$tbl_loop name=dt}
	{assign var=ttl_po_pcs value=0}
	{assign var=ttl_po_ctn value=0}
	{assign var=ttl_ctn value=0}
	{assign var=ttl_pcs value=0}
	{assign var=ttl_rctn value=0}
	{assign var=ttl_rpcs value=0}
	{assign var=ttl_qty_var value=0}
	{assign var=row_counter value=0}
	{assign var=rowspan value=0}
	{assign var=doc_type value=$smarty.section.dt.iteration+$tbl_val}
	{assign var=have_items value=0}
	{if $doc_type eq '0'}
		{assign var=desc_width value=55%}
		{assign var=isi_colspan value=7}
		{assign var=rowspan value=2}
		<h2>Items in {if $grr.is_ibt_do}DO{else}PO{/if}</h2>
	{elseif $doc_type eq '1'}
		{assign var=isi_colspan value=7}
		{if $doc_type eq 1 && $form.is_under_gst}
			{assign var=isi_colspan value=$isi_colspan+1}
		{/if}
		{if ($grr.type eq 'PO' || $grr.is_ibt_do) && !$grr.allow_grn_without_po}
			<br><h2>SKU Return List</h2>
		{elseif $grr.type eq 'PO' || $grr.is_ibt_do}
			<br><h2>Items not in {if $grr.is_ibt_do}DO{else}PO{/if}</h2>
		{else}
			<br><h2>Received Items</h2>
		{/if}
		{assign var=rowspan value=2}
	{elseif $doc_type eq '2'}
		{assign var=code_width value=70%}
		{assign var=isi_colspan value=5}
		<br><h2>SKU not in ARMS</h2>
	{elseif $doc_type eq '3'}
		{assign var=code_width value=''}
		<br><h2>{if $grr.is_ibt_do}DO{else}PO{/if} Suggested Selling Price</h2>
	{/if}
	{* <div {if $doc_type == 0}style="display:none;"{else}style="overflow:auto;"{/if}> *}{*
	<div style="overflow:auto;">
	<table width="100%" class="tbl_item" id="tbl_item_{$doc_type}" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing=1 cellpadding=4>
	<thead>
	<tr height=32 bgcolor=#ffffff class="small">
		{if $doc_type < 3}
			<th rowspan="{$rowspan}">
				Return
			</th>
		{/if}
		<th rowspan="{$rowspan}">#</th>
		{if $doc_type ne '2'}
			<th rowspan="{$rowspan}" width="{$code_width|default:0}">ARMS Code/<br />Mcode</th>
			<th rowspan="{$rowspan}">Artno</th>
			<th rowspan="{$rowspan}" width="{$desc_width|default:'70%'}">Description</th>
			<th rowspan="{$rowspan}">Packing<br>UOM</th>
			{if $doc_type eq '0'}
				<th rowspan="{$rowspan}">{if $grr.is_ibt_do}Delivery{else}Purchase{/if}<br>UOM</th>
				<th colspan="{$rowspan}">Order Qty</th>
			{elseif $doc_type ne '0'}
				<th {if $doc_type eq '1'}rowspan="{$rowspan}"{/if}>Cost<br />Price</th>
			{/if}
			{if $doc_type eq 1 && $form.is_under_gst}
				<th rowspan="2">GST<br />Code</th>
			{/if}
		{else}
			<th>Code</th>
			<th width="{$code_width|default:0}">Description</th>
			<th>Cost<br />Price</th>
		{/if}
		{if $doc_type ne '3'}
			<th colspan="{$rowspan}">
				{if $doc_type eq '1'}
					Received
				{else}
					Rcv Qty<br />(Pcs)
				{/if}
			</th>
			{if $doc_type eq '0'}
				<th colspan="{$rowspan}">Return</th>
				<th rowspan="{$rowspan}">Var (Pcs)</th>
				<th rowspan="{$rowspan}">Remarks</th>
			{/if}
		{else}
			<th>Current<br>Selling<br>Price</th>
			<th>Suggested<br>Selling<br>Price</th>
			{if $config.grn_check_selling_price}
				<th width="200">Remark</th>
			{/if}
		{/if}
		
		{if $doc_type eq 1}
			<th rowspan="2">Amount</th>
			{if $form.is_under_gst}
				<th rowspan="2">GST</th>
				<th rowspan="2">Amount<br />Include GST</th>
			{/if}
		{/if}
	</tr>

	{if $doc_type eq '0' || $doc_type eq '1'}
		<tr height=32 bgcolor=#ffffff class="small">
			{if $doc_type eq '0'}
				<th>Ctn</th>
				<th>Pcs</th>
				<th>Ctn</th>
				<th>Pcs</th>
			{/if}
			<th>Ctn</th>
			<th>Pcs</th>
		</tr>
	{/if}

	</thead>

	<tbody id="grn_items_{$doc_type}" class="multiple_add_container_{$doc_type}">
	{if $doc_type eq '0'}
		{foreach from=$form.items item=item name=fitem}
			{if $item.item_group eq '0' || $item.item_group eq '1' || $item.item_group eq '2'}
				{assign var=ttl_po_ctn value=$ttl_po_ctn+$item.po_ctn}
				{assign var=ttl_po_pcs value=$ttl_po_pcs+$item.po_pcs}
				{assign var=ttl_ctn value=$ttl_ctn+$item.ctn}
				{assign var=ttl_pcs value=$ttl_pcs+$item.pcs}
				{assign var=ttl_rctn value=$ttl_rctn+$item.return_ctn}
				{assign var=ttl_rpcs value=$ttl_rpcs+$item.return_pcs}
				{assign var=ttl_qty_var value=$ttl_qty_var+$item.ctn*$item.uom_fraction+$item.pcs-$item.po_qty-$item.return_ctn*$item.uom_fraction-$item.return_pcs}
				{assign var=row_counter value=$row_counter+1}
				{if $item.from_isi}
					<tr bgcolor="#AFC7C7" title="{$item.sku_item_code} is new SKU item" onmouseover="this.bgColor='#CFECEC';" onmouseout="this.bgColor='#AFC7C7';"  id="{$doc_type}_titem{$item.id}">
				{else}
					<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="{$doc_type}_titem{$item.id}">
				{/if}
				{include file=goods_receiving_note_approval.show_list.tpl}
				</tr>
				{assign var=have_items value=1}
			{/if}
		{/foreach}
	{elseif $doc_type eq '1'}                         
		{foreach from=$form.items item=item name=fitem}
			{if $item.item_group eq '3'}
				{assign var=ttl_pcs value=$ttl_pcs+$item.pcs}
				{assign var=row_counter value=$row_counter+1}
				{if $item.from_isi}
					<tr bgcolor="#AFC7C7" title="{$item.sku_item_code} is new SKU item" onmouseover="this.bgColor='#CFECEC';" onmouseout="this.bgColor='#AFC7C7';"  id="{$doc_type}_titem{$item.id}">
				{else}
					<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="{$doc_type}_titem{$item.id}">
				{/if}
				{include file=goods_receiving_note_approval.show_list.tpl}
				</tr>
				{assign var=have_items value=1}
				{assign var=q value=$item.ctn+$item.pcs/$item.uom_fraction}
				{assign var=amt value=$q*$item.cost}
				{assign var=amt value=$amt|round2}	
				{assign var=ttl_amt value=$ttl_amt+$amt}
				{if $form.is_under_gst}
					{assign var=gst_amt value=$item.cost*$item.gst_rate/100}
					{assign var=gst_amt value=$gst_amt|round:$config.global_cost_decimal_points}
					{assign var=row_gst value=$gst_amt*$q}
					{assign var=row_gst value=$row_gst|round2}
					{assign var=row_gst_amt value=$amt+$row_gst}
					{assign var=row_gst_amt value=$row_gst_amt|round2}
					{assign var=ttl_gst value=$ttl_gst+$row_gst}
					{assign var=ttl_gst_amt value=$ttl_gst_amt+$row_gst_amt}
				{/if}
			{/if}
		{/foreach}
	{elseif $doc_type eq '2'}
		{if is_array($form.non_sku_items)}
			{foreach from=$form.non_sku_items.code key=sku_code item=qty name=fitem}
				{assign var=n value=$smarty.foreach.fitem.iteration-1}
				{assign var=row_counter value=$row_counter+1}
				{assign var=ttl_pcs value=$ttl_pcs+$form.non_sku_items.qty.$n}
				<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="{$doc_type}_titem{$sku_code|default:$item.sku_item_code}">
				{include file=goods_receiving_note_approval.show_list.tpl}
				</tr>
				{assign var=have_items value=1}
			{/foreach}
		{/if}
	{elseif $doc_type eq '3'}
		{foreach from=$form.items item=item name=fitem}
			{if $item.item_group eq '1' || $item.item_group eq '2'}
				{if $form.is_under_gst && $item.inclusive_tax eq 'yes'}
					{assign var=selling_price value=$item.gst_selling_price}
				{else}
					{assign var=selling_price value=$item.selling_price}
				{/if}
			
				{if $item.curr_selling_price != $selling_price && $item.po_item_id ne '' && $selling_price ne ''}
					{assign var=row_counter value=$row_counter+1}
					<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="{$doc_type}_titem{$item.id}">
					{include file=goods_receiving_note_approval.show_list.tpl}
					</tr>
					{assign var=have_items value=1}
				{/if}
			{/if}
		{/foreach}
	{/if}
	{if !$have_items}
		<tr align="center" id="no_data_{$doc_type}">
			<td colspan="15">No data</td>
		</tr>
	{/if}
	</tbody>
	{if $doc_type ne '3'}
		<tfoot>
		<tr bgcolor=#ffffff>
			<td colspan="{$isi_colspan|default:7}" align=right><b>Total</b></td>
			{if ($grr.type eq 'PO' || $grr.is_ibt_do) && $doc_type eq '0'}
				<td align=right>Ctn: {$ttl_po_ctn|qty_nf|ifzero:'-'}</td>
				<td align=right>Pcs: {$ttl_po_pcs|qty_nf|ifzero:'-'}</td>
				<td align=right id="total_po_qty_{$doc_type}">Ctn: {$ttl_ctn|qty_nf|ifzero:'-'}</td>
				<td align=right id="total_qty_{$doc_type}">Pcs: {$ttl_pcs|qty_nf}</td>
				<td align=right id="total_qty_{$doc_type}">Ctn: {$ttl_rctn|qty_nf}</td>
				<td align=right id="total_qty_{$doc_type}">Pcs: {$ttl_rpcs|qty_nf}</td>
				<td align=right><div id="total_qty_var_{$doc_type}" class={if $ttl_qty_var>0}pv{elseif $ttl_qty_var<0}nv{else}r{/if}>{$ttl_qty_var|qty_nf|ifzero:'-'}</td>
				<td>&nbsp;</td>
			{else}
				<td align=right id="total_qty_{$doc_type}" {if $doc_type eq '1'}colspan="{$rowspan}">Ctn: {$ttl_ctn|qty_nf|default:0} {else}>{/if}Pcs: {$ttl_pcs|qty_nf|default:0}</td>
				{if $doc_type eq '1'}
					<td align="right" id="total_amt_{$doc_type}" >{$ttl_amt|number_format:2}</td>
					{if $form.is_under_gst}
						<td align="right" id="total_gst_{$doc_type}" >{$ttl_gst|number_format:2}</td>
						<td align="right" id="total_gst_amt_{$doc_type}" >{$ttl_gst_amt|number_format:2}</td>
					{/if}
				{/if}
			{/if}
		</tr>
		</tfoot>
	{/if}
		
	</table>
	</div>
{/section}
*}

{if $config.use_grn_future}
	{include file=goods_receiving_note2.view.list.tpl}
{else}
	{include file=goods_receiving_note.view.list.tpl}
{/if}

</div>

</form>

{if ($form.non_sku_items || $have_grn_returned_items) && $config.use_grn_future}
	<br><h2>Returned Item(s)</h2>
	<div style="overflow:auto;">
	<table width=100% cellpadding=2 cellspacing=1 border=0 style="border:1px solid #000">
		<thead>
		<tr height=32 bgcolor="#ffee99" class="small">
			<th>#</th>
			<th width="20%">Code</th>
			<th width="60%">Description</th>
			<th>Cost Price</th>
			<th>Rcv<br />Qty (Pcs)</th>
			<th>Amount</th>
		</tr>
		</thead>
	
		<tbody id="tbditems">
			{if $form.non_sku_items}
				{foreach from=$form.non_sku_items key=sku_code item=item name=fitem}
					{assign var=n value=$smarty.foreach.fitem.iteration-1}
					{if $form.non_sku_items.code.$n}
						<!--{$ri_count++}-->
						{assign var=ttl_pcs value=$ttl_pcs+$form.non_sku_items.qty.$n}
						{assign var=curr_amt value=$form.non_sku_items.qty.$n*$form.non_sku_items.cost.$n}
						{assign var=ttl_nsi_amt value=$ttl_nsi_amt+$curr_amt|round2}
						<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
							<td nowrap width="2%" align="right">{$ri_count}.</td>
							<td>{$form.non_sku_items.code.$n}</td>
							<td>{$form.non_sku_items.description.$n}</td>
							<td align="right">{$form.non_sku_items.cost.$n|number_format:$config.global_cost_decimal_points:".":""}</td>
							<td class=r width="5%">{$form.non_sku_items.qty.$n|default:0}</td>
							<td class=r width="5%">{$curr_amt|round2}</td>
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
						{assign var=row_amt value=`$item.cost*$row_qty/$item.uom_fraction`}
						{assign var=row_amt value=$row_amt|round2}
						{assign var=ttl_nsi_amt value=$ttl_nsi_amt+$row_amt|round2}
						<tr height="24" {cycle name=r2 values=",bgcolor=#eeeeee"}>
							<td nowrap width="2%" align="right">{$ri_count}.</td>
							<td>{$item.sku_item_code}</td>
							<td>{$item.description}</td>
							<td align="right">{$item.cost|number_format:$config.global_cost_decimal_points:".":""}</td>
							<td class=r width="5%">{$row_qty|qty_nf}</td>
							<td class=r width="5%">{$row_amt|round2}</td>
						</tr>
					{/if}
				{/foreach}
			{/if}
		</tbody>
	
		<tfoot>
			<tr height="24" bgcolor="#ffee99">
				<td colspan="4" align=right><b>Total</b></td>
				<td align="right" id="total_qty">{$ttl_pcs|default:0}</td>
				<td align="right" id="total_amt">{$ttl_nsi_amt|default:0}</td>
			</tr>
		</tfoot>
	</table>
	</div>
{/if}

<br>
<p align=center>
<input type=button value="Approve" style="font:bold 20px Arial; background-color:#090; color:#fff;" onclick="do_approve()">
<input type=button value="Reject" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_reject_dialog()">
<input type=button value="Cancel" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_cancel()">
</p>

<script>
new Draggable('reject_dialog');
</script>
