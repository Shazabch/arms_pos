{*
6/27/2019 1:33 PM Andy
- Enhanced to auto connect to server every 25 minutes to prevent logout.
- Enhanced to check login status when users save.

7/2/2019 2:36 PM William
- Added new checkbox "Disable Auto Parent & Child Distribution".

10/16/2019 11:39 AM William
- Enhanced to update skip variance setting.

06/02/2020 03:19 PM Sheila
- Updated button color.
*}
{include file=header.tpl}

{literal}
<script type="text/javascript" src="js/do.js"></script>

<style>
.sh{
    background-color:#ff9;
}

.stdframe.active{
 	background-color:#fea;
	border: 1px solid #f93;
}

td.xc{
	border-bottom: 1px dashed #aaa;
}
.input_no_border input, .input_no_border select{
	border:1px solid #999;
	background: #fff;
	font-size: 10px;
	padding:2px;
}
#div_sku_details{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
}
#div_sku_details_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#div_sku_details_content{
    padding:2px;
}
</style>
{/literal}
<script>
var id = '{$form.id}';
var bid = '{$form.branch_id}';
var phpself = '{$smarty.server.PHP_SELF}';
var total_row = 0;
var hidden_del_row_no = 0;

{literal}

var DO_CHECKOUT_MODULE = {
	form_element: undefined,

	initialize: function(){
		this.form_element = document.f_a;
		var THIS = this;

		if(!this.form_element){
			alert('DO Checkout module failed to initialize.');
			return false;
		}
		
		// event for skip button
		$('skip_btn').observe('click', function(){
            THIS.do_skip();
		});

		// event to save button
		$('save_btn').observe('click', function(){
			THIS.do_save();
		});

		// event to close button
		$('close_btn').observe('click', function(){
			THIS.do_close();
		});
		
		THIS.add_row(total_row);
		
		// prevent logout
		new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	},

	add_row: function(curr_row, obj){

		if(hidden_del_row_no != 0 && ($("delete_row_"+curr_row).style.display != "none" || this.form_element['barcode['+curr_row+']'].value.trim() == "")){
			var next_tr = $("row_"+curr_row).next();
			
			if(next_tr != undefined && obj.name == "qty["+curr_row+"]"){
				var next_row_id = next_tr.readAttribute("row_id");
				
				this.form_element["barcode["+next_row_id+"]"].focus();
			}else{
				if(this.form_element["barcode["+curr_row+"]"].value.trim() == "") this.form_element["barcode["+curr_row+"]"].focus();
				else{
					this.form_element["qty["+curr_row+"]"].focus();
					this.form_element["qty["+curr_row+"]"].select();
				}
			}
			return;
		}
		
		if(hidden_del_row_no > 0){
			$("delete_row_"+hidden_del_row_no).show();
		}
		
		total_row = float(total_row) + 1;
		var new_tr = $('tmp_row').cloneNode(true).innerHTML;
		new_tr = new_tr.replace(/__row__id/g, total_row);
		var THIS = this;

		hidden_del_row_no = total_row;
		new Insertion.Bottom($('items'), new_tr);
		THIS.reset_row_no();
		
		if(curr_row > 0 && this.form_element["qty["+curr_row+"]"].value == "") this.form_element["qty["+curr_row+"]"].focus();
		else this.form_element["barcode["+total_row+"]"].focus();
	},
	
	do_skip: function(){
		if(!confirm("Are you sure want to skip?")) return;
		this.form_element['a'].value = "skip_barcode";
		this.form_element.submit();
	},
	
	do_save: function(){
		if(!confirm("Are you sure want to save?")) return;
		
		if (check_login()){
			this.form_element['a'].value = "save_barcode";
			this.form_element.submit();
		}		
	},
	
	do_close: function(){
		if(!confirm("Close without save?")) return;
        window.location = phpself;
	},
	
	reset_row_no: function(){
	    // reset row number
		$$('#item_tbl span.row_no').each(function(ele, i){
			if($(ele) != undefined ) $(ele).update((i+1)+".");
		});
	},
	
	delete_row: function(row){
		var THIS = this;
		if(!confirm("Delete this row?")) return;
		
		$("row_"+row).remove();
		THIS.reset_row_no();
	},
	
	check_key: function(event, curr_row, obj){
		var THIS = this;
		if(event.keyCode==13){
			THIS.add_row(curr_row, obj);
		}
	}
}

{/literal}
</script>
<h1>DO Checklist (DO/{$form.do_no})</h1>

<table id="tmp_row" style="display:none;">
	<tr id="row___row__id" row_id="__row__id" >
		<td align="center" nowrap>
			<img src="ui/remove16.png" class="clickable" id="delete_row___row__id" title="Delete Row" onclick="DO_CHECKOUT_MODULE.delete_row(__row__id);" align="absmiddle" alt="Delete this row" style="display:none;">
			<span class="row_no"></span>
		</td>
		<td align="center"><input type="text" name="barcode[__row__id]" value="" onkeypress="DO_CHECKOUT_MODULE.check_key(event, __row__id, this);" onchange="DO_CHECKOUT_MODULE.add_row(__row__id, this);" size="30" /></td>
		<td align="center"><input type="text" name="qty[__row__id]" class="r" value="" onkeypress="DO_CHECKOUT_MODULE.check_key(event, __row__id, this);" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points})); DO_CHECKOUT_MODULE.add_row(__row__id, this);" size="10" /></td>
	</tr>
</table>

<form name="f_a" method="post">
<input type="hidden" name="a" value="save_barcode">
<input type="hidden" name="branch_id" value="{$form.branch_id}">
<input type="hidden" name="id" value="{$form.id}">
<input type="hidden" name="reason" value="">
<input type="hidden" name="do_no" value="{$form.do_no}">
<input type="hidden" name="do_branch_id" value="{$form.do_branch_id}">
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
<input type="hidden" name="debtor_id" value="{$form.debtor_id}">
<input type="hidden" name="price_indicate" value="{$form.price_indicate}">
<input type="hidden" name="discount" value="{$form.discount}">

<div class="stdframe" style="background:#fff">
<h4>General Informations</h4>

<table border=0 cellspacing=0 cellpadding=4>
<tr align=left>
<td>
	<table>
	<tr>
	<th width=80 align=left>DO Date</th>
	<td width=150>
	{$form.do_date|date_format:"%Y-%m-%d"}
	</td>
	
	{if $form.po_no}
		<th align=left width=80>PO No.</th>
		<td width=150>
		{$form.po_no}
		</td>
	{/if}	
	
	<th align=left width=80>Owner</th>
	<td style="color:blue;" align=left width="150">
	{$form.user}
	</td>
	{if $config.do_enable_do_markup}
	    <th align="left">DO Markup(+) / Discount(-)</th>
	    <td><input type="text" value="{$form.do_markup}" size="3" style="text-align:right;" readonly />%</td>
	{/if}
	</tr>
	</table>
</td>
</tr>

<tr>
<td>
	<table>
	{if $form.do_type eq 'transfer'}
	    <td valign=top width=80><b>Deliver From</b></td>
		<td valign=top>
		{$form.from_branch_name}
		- {$form.from_branch_description}
		</td>
	{/if}
	<tr>
		<td valign=top width=80><b>Deliver To</b></td>
		<td valign=top>
		{if $form.do_type eq 'credit_sales'}
            {assign var=debtor_id value=$form.debtor_id}
	    	Debtor: {$debtor.$debtor_id.code} - {$debtor.$debtor_id.description}
	    {elseif $form.do_type eq 'open'}
	    	Company Name: {$form.open_info.name} - {$form.open_info.address}
		{else}
			{$form.do_branch_name|default:$form.open_info.name}{if $form.do_branch_description} - {$form.do_branch_description}{/if}
		{/if}
		</td>
	</tr>
	{if $config.consignment_modules && $config.masterfile_branch_region && $form.do_type eq 'transfer'}
		<tr>
			<td>&nbsp;</td>
			<td>
				{if $form.use_address_deliver_to}
					<img src="/ui/checked.gif" id="sn_dtl_icon" align="absmiddle">
				{else}
					<img src="/ui/unchecked.gif" id="sn_dtl_icon" align="absmiddle">
				{/if}
				<b> Use Deliver To Address from Branch</b>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td id="span_adt" {if !$form.use_address_deliver_to}style="display:none;"{/if}>
				{$form.address_deliver_to|default:"-"}
			</td>
		</tr>
		{if $config.consignment_multiple_currency}
			<tr>
				<td><b>Exchange Rate</b></td>
				<td>
					{$form.exchange_rate|default:"-"}
				</td>
			</tr>
		{/if}
	{/if}
	{if $config.masterfile_enable_sa && $form.mst_sa}
		<tr>
			<td><b>Sales Agent</b></td>
			<td>
				<div style="width:400px;height:100px;border:1px solid #ddd;overflow:auto;" id="do_sa_list">
					{foreach from=$form.mst_sa name=i key=r item=sa_id}
						{$sa_list.$sa_id.code} - {$sa_list.$sa_id.name}<br />
					{/foreach}
				</div>
			</td>
		</tr>
	{/if}
	</table>
</td>
</tr>
</table>

</div>

<br>

{if $err}
<div id=err><div class=errmsg><ul>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

<div><input value="1" {if $checklist_disable_parent_child eq 1}checked{/if} type="checkbox" name="checklist_disable_parent_child" />  Disable Auto Parent & Child Distribution</div>
<br>
<table id="item_tbl" width="30%" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing="1" cellpadding="1">
<!--START HEADER-->
<thead>
<tr bgcolor="#ffffff">
	<th width=20>#</th>
	<th nowrap>Barcode</th>
	<th nowrap>Qty</th>
</tr>
	<tbody id="items">
	{foreach from=$items key=row item=item}
		<tr id="row_{$row}" row_id="{$row}" {if $item.is_error}class="highlight_row"{/if}>
			<td align="center" nowrap>
				<img src="ui/remove16.png" class="clickable" id="delete_row_{$row}" title="Delete Row" onclick="DO_CHECKOUT_MODULE.delete_row({$row})" align="absmiddle" alt="Delete this row">
				<span class="row_no">{$row}.</span>
			</td>
			<td align="center"><input type="text" name="barcode[{$row}]" value="{$item.barcode}" onkeypress="DO_CHECKOUT_MODULE.check_key(event, {$row}, this);" onchange="DO_CHECKOUT_MODULE.add_row({$row}, this);" size="30" /></td>
			<td align="center"><input type="text" name="qty[{$row}]" class="r" value="{$item.qty}" onkeypress="DO_CHECKOUT_MODULE.check_key(event, {$row}, this);" onchange="this.value=float(round(this.value, {$config.global_qty_decimal_points})); DO_CHECKOUT_MODULE.add_row({$row}, this);" size="10" /></td>
		</tr>
		<!--{$total_row++}-->
	{/foreach}
	</tbody>
</table>
</form>

<p id="submitbtn" align="center">   
<input class="btn btn-success" type="button" value="Save" id="save_btn" >
<input class="btn btn-primary" type="button" value="Skip" id="skip_btn" >
<input class="btn btn-error" type="button" value="Close" id="close_btn">
</p>

{include file=footer.tpl}
<script>
total_row = '{$total_row}';
DO_CHECKOUT_MODULE.initialize();
hidden_del_row_no = total_row;
</script>
