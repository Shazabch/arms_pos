{*
10/7/2011 4:02:12 PM Justin
- Added to capture deleted item ID.
- Added hidden field to store deleted item IDs.

9/22/2014 11:43 AM Justin
- Enhanced to have decimal points while adding batch no depending on SKU item settings.

2/22/2017 4:40 PM Justin
- Enhanced to show "Excluded GST" message for PO amount while it is under GST.

5/4/2017 16:43 Qiu Ying
- Enhanced to remove config grn_have_tax in GRN Future

5/20/2019 9:11 AM William
- Enhance "GRR" word to use report_prefix.

06/26/2020 Sheila 01:58 PM
- Updated button css.
*}

{include file=header.tpl}

{assign var=time_value value=1000000000}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

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

#tbl_item input, # select{
	border:1px solid #999;
	font-size: 10px;
	padding:2px;
}
input[disabled],input[readonly],select[disabled], textarea[disabled]{
	color:black;
	background:#ddd;
}

#temp_new_row{
	display:none;
}

</style>
{/literal}
<script>
var grn_have_tax = '{$config.grn_have_tax}';
var phpself = '{$smarty.server.PHP_SELF}';
var batch_status = '{$form.batch_status}';
var t = '{$smarty.request.t}';

{literal}

function do_close(){
	if(batch_status == 0){
		if (!confirm('Discard changes and close?')){
		    return;
		}
	}
	document.location = phpself+"?t="+t;
}

function check_batch_items(){

	var sku_item_code = '';
	var prev_sku_item_code = '';
	var ttl_qty = 0;
	var err_msg = '';
	var curr_grn_item_qty = 0;
	var count_rows = 0;
	var skip_compare = false;
	var is_proceed = false;
	var curr_batch_no = '';
	var si_title = '';

	var sku_batch_items = $('grn_items').getElementsByClassName('batch_id');
	var bi_total_rows = sku_batch_items.length;

	if(bi_total_rows == 0){
		alert("Unable to save/confirm, there is no Batch item inserted.");
		return false;
	}

	// validation
	var grn_items = $('grn_items').getElementsByTagName('INPUT');
	var gi_total_rows = grn_items.length;

	if(gi_total_rows > 0 && document.f_a.a.value == "confirm"){
		$A(grn_items).each(
			function(r,idx){
				count_rows += 1;
				if(r.name.indexOf("grn_item_qty[")==0){
					if(curr_grn_item_qty < ttl_qty){
						err_msg += "* SKU Code "+sku_item_code+" having invalid total sum up of Batch Qty.\n";
					}
					ttl_qty = 0;
					curr_grn_item_qty = r.value;
					sku_item_code = r.title;
				}

				if(prev_sku_item_code != sku_item_code) si_title = "* Found SKU Code "+sku_item_code+" having errors as below:\n";
				
				if(r.name.indexOf("batch_no[")==0){
					if(!r.value){
						err_msg += si_title+"  - Having empty Batch No.\n";
						si_title = "";
					}
				}

				if(r.name.indexOf("expired_date[")==0){
					if(!r.value){
						err_msg += si_title+"  - Having empty Expired Date.\n";
						si_title = "";
					}
				}

				if(r.name.indexOf("batch_qty[")==0){
					if(!r.value || r.value==0){
						err_msg += si_title+"  - Having zero Qty.\n";
						si_title = "";
					}
					ttl_qty += float(r.value);
				}
				if(count_rows == gi_total_rows){
					if(curr_grn_item_qty < ttl_qty){
						err_msg += si_title+" having invalid total sum up of Batch Qty.\n";
						si_title = "";
					}
				}

				prev_sku_item_code = sku_item_code;
			}
		);
	}

	if(err_msg){
		alert("You have encountered below errors:\n\n"+err_msg+"\n");
		return false;
	}
}

function do_save(){
	//check_batch_items();
	document.f_a.submit();
}

function do_confirm(){
	//check_batch_items();
	if(!confirm("Are you sure want to confirm?")) return false;

	new Ajax.Request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_a),
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			if(m.responseText){
				$("err").innerHTML = m.responseText;
				return;
			}
			document.location = phpself+"?t=2&print_bn=1&id="+document.f_a.id.value+"&branch_id="+document.f_a.branch_id.value;
		}
	});
}

var deleted_batch_item_list = [];
function del_row(ele, batch_id){
	if(!confirm('Are you sure?')) return;
	var parent_row=ele.parentNode.parentNode;

	if(batch_id < 1000000000){
		deleted_batch_item_list.push(batch_id);
		document.f_a['deleted_batch_item_list'].value = deleted_batch_item_list;
	}
	if($('delete_msg'))	$('delete_msg').remove();
	$(parent_row).remove();
}

function ajax_add_batch_item(id, sid, obj){
	if(obj.src.indexOf('clock')>0) return false;

	var param_str = Form.serialize(document.f_a)+'&a=ajax_add_batch_item&id='+id+'&sid='+sid;
	
	
	obj.src = '/ui/clock.gif';	
	new Ajax.Request(phpself, {
		method:'post',
		parameters: param_str,
		evalScripts: true,
		onFailure: function(m){
			alert(m.responseText);
		},
		onSuccess: function(m){
			eval("var json = "+m.responseText);
			for(var tr_key in json){
			
				var sku_batch_items = $$('.batch_items'+id+' input');
				var sbi_total_rows = sku_batch_items.length;
				var last_batch_id = '';

				if(sbi_total_rows > 0){
					$A(sku_batch_items).each(
						function(r,idx){
							if(r.name.indexOf("batch_id[")==0){
								last_batch_id = r.value;
							}
						}
					);
				}

				if(last_batch_id) new Insertion.After($('batch_items'+last_batch_id),json[tr_key]['html']);
				else new Insertion.After($('titem'+id),json[tr_key]['html']);
				obj.src = '/ui/add_child.png';
			}
		}

	});
}

{/literal}
</script>

<h1>{$PAGE_TITLE} ({$grr.report_prefix}{$smarty.request.id|string_format:"%05d"})<br />
Status: {if $form.batch_status eq '1'}Confirmed{else}Awaiting for Setup{/if}
</h1>

<form name="f_a" method="post">
<input type=hidden name=a value="save">
<input type=hidden name=id value="{$form.id}">
<input type=hidden name=branch_id value="{$form.branch_id}">
<input type=hidden name=vendor_id value="{$form.vendor_id}">
<input type=hidden name=grr_id value="{$form.grr_id}">
<input type=hidden name=grr_item_id value="{$form.grr_item_id}">
<input type=hidden name=type value="{$grr.type}">
<input type="hidden" name="grn_get_weight" value="{$grr.grn_get_weight}" />

<br>
<div class="stdframe">
<h4>General Information</h4>
<table  border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>GRR No</b></td><td>{$grr.report_prefix}{$grr.grr_id|string_format:"%05d"}/{$grr.grr_item_id}</td>
<td colspan=2>&nbsp;</td>
<td><b>GRR Date</b></td><td>{$grr.added|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.u}</td>
</tr><tr>
<td><b>GRR Amount</b></td><td>{$grr.grr_amount|number_format:2}</td>
<td><b>Received Qty</b></td><td>Ctn:{$grr.grr_ctn|number_format} / Pcs:{$grr.grr_pcs|number_format}</td>
<td><b>Received Date</b></td><td>{$grr.rcv_date|date_format:$config.dat_format}</td>
<td><b>By</b></td><td>{$grr.rcv_u}</td>
</tr><tr>
<td><b>Department</b></td>
<td colspan=3>
<input type=hidden name=department_id value="{$form.department_id}">
{$grr.department}
</td>
</tr><tr>
<td><b>Vendor</b></td><td colspan=3>{$grr.vendor}</td>
<td><b>Lorry No</b></td><td>{$grr.transport}</td>
</tr><tr>
<td width=100><b>Document Type.</b></td><td width=100><font color=blue>{$grr.type}</font></td>
<td width=100><b>Document No.</b></td><td width=100><font color=blue>{$grr.doc_no}</font></td>
{if $grr.type eq 'PO'}
<td width=100><b>PO Amount{if $grr.po_is_under_gst}<br />(Excluded GST){/if}</b></td><td width=100><font color=blue>{$grr.po_amount|number_format:2}</font></td>
<td width=100><b>Partial Delivery</b></td><td width=100><font color=blue>{if $config.use_grn_future}{if $grr.pd_po}{$grr.pd_po} (Not Allowed){else}Allowed{/if}{else}{if $grr.partial_delivery}Allowed{else}Not Allowed{/if}{/if}</font></td>
{/if}
</tr>
{if $form.grn_tax}
	<tr>
	    <td><b>Tax</b></td>
	    <td>{$form.grn_tax|default:0}%</td>
	</tr>
{/if}
</table>
</div>

<div id="err"></div>

<br>

<input type="hidden" name="deleted_batch_item_list" value="">
<div style="overflow:auto;">
<table width=100% id=tbl_item style="border:1px solid #000; padding:1px;" class="input_no_border body" cellspacing=1 cellpadding=4>
<thead>
<tr height=32 bgcolor="#ffee99" class="small">
	<th>#</th>
	<th>ARMS</th>
	<th>Artno</th>
	<th>Mcode</th>
	<th width=75%>Description</th>
	<th>Recv<br>Qty</th>
</tr>
</thead>

<tbody id="grn_items" class="grn_items">
{foreach from=$form.items item=mitem name=mfitem}
	{assign var=qty value=$mitem.ctn*$mitem.uom_fraction+$mitem.pcs}
	<tr id="titem{$mitem.id}" class="multiple_add_container" bgcolor="{cycle name=r1 values=",#eeeeee"}" onmouseout="this.bgColor='{cycle name=r2 values=",#eeeeee"}';" onmouseover="this.bgColor='#ffffcc';">
		<td nowrap>
			{if $qty > 0}
				<img src=ui/add_child.png style="vertical-align:top;" class=clickable title="Add Batch No for {$mitem.sku_item_code}" onclick="ajax_add_batch_item('{$mitem.id}', '{$mitem.sku_item_id}', this)" align=absmiddle>
			{/if}
			{$smarty.foreach.mfitem.iteration}.
		</td>
		<td>{$mitem.sku_item_code}</td>
		<td align="center">{$mitem.artno|default:"-"}</td>
		<td align="center">{$mitem.mcode|default:"-"}</td>
		<td>{$mitem.description}</td>
		<td align="right">
			{$qty|default:0}
			<input type="hidden" name="grn_item_qty[{$mitem.id}]" value="{$qty}" title="{$mitem.sku_item_code}">
			<input type="hidden" name="sku_item_code[{$mitem.id}]" value="{$mitem.sku_item_code}">
			<input type="hidden" name="sku_id[{$mitem.id}]" value="{$mitem.sku_id}">
		</td>
	</tr>
	{foreach from=$form.batch_items item=item name=fitem}
		{if $mitem.id eq $item.grn_item_id && $mitem.branch_id eq $item.branch_id}
			{include file="masterfile_sku_items.batch_no_setup.detail_row.tpl"}
		{/if}
	{/foreach}
{/foreach}
</tbody>

</table>
</div>

<p align=center>
	<input class="btn btn-success" type=button id="save_btn" value="Save" onclick="document.f_a.a.value='save'; do_save();">
	
	<input class="btn btn-primary" type=button id="confirm_btn" value="Confirm" onclick="document.f_a.a.value='confirm'; do_confirm();">
<input class="btn btn-error" type=button value="Close" onclick="do_close()">
</p>

</form>

<div id="sel_vendor_sku" style="position:absolute;left:0;top:0;display:none;width:600px;height:400px;padding:10px;border:1px solid #000;background:#fff">
</div>

<script>

{literal}
if(batch_status == 1){
	$('save_btn').hide();
	$('confirm_btn').hide();
	
	$$('.multiple_add_container img').each(function (ele,index){
		ele.remove();
	});
	
	$$('.grn_items input').each(function (ele,index){
		ele.disable();
		ele.style.background = "#fff";
	});

	$$('.grn_items img').each(function (ele,index){
		ele.remove();
	});
}
{/literal}
</script>
{include file=footer.tpl}
