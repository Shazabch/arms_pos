{*
7/16/2009 3:24:02 PM Andy
- Hide some form element

11/10/2009 3:04:32 PM Andy
- add input to store do type

1/26/2011 4:00:54 PM Justin
- Added serial number enhancements.
- Enhanced the ajax call and insertion for multiple row insert by using JSON. 

6/15/2011 3:56:50 PM Justin
- Added currency code to be used in php.

6/20/2011 12:25:21 PM Justin
- Modified the Multi add to show notification for batch no.

8/19/2011 2:59:21 PM Justin
- Added clear search SKU engine feature.

6/23/2017 9:38 AM Justin
- Enhanced to disable SKU not allowed to add when it is BOM Package SKU.
*}

<script>

var currency_code = "{$currency_code}";

{literal}
toggle_multi_add = function(ele){
	var status = ele.checked;
	
	$$('#tbl_multi_add input.mul').each(function(chx){
		chx.checked = status;
	});
}

submit_multi = function(ele){
	ele.disabled = true;
	ele.value = 'Processing...'
	
	var del_b = document.f_a.elements["deliver_branch[]"];
	var deliver_branch = [];
	if(typeof(del_b)!='undefined'){
        for(var i=0; i<del_b.length; i++){
		    if(del_b[i].checked)	deliver_branch.push(del_b[i].value);
		}
	}
	
	parms = Form.serialize(document.f_a)+'&a=save_multi_add&'+Form.serialize(document.f_mul)+'&show_discount='+show_discount;
	
	if(do_type == "transfer" && consignment_modules && masterfile_branch_region && consignment_multiple_currency) parms += "&currency_code="+branch_currency_code[document.f_a['do_branch_id'].value];
	
	if ($('opt_label_id')) parms+= "&price_indicator="+$('opt_label_id').value;
	
    new Ajax.Request('do.php',{
        method: 'post',
		parameters: parms,
		/*onComplete: function(e){
			new Insertion.Bottom($('do_items'), e.responseText);
			var spans_no = $$('#new_sheets span.no');
			
			for(var i=0; i<spans_no.length; i++){
                spans_no[i].id = 'no_'+(i+1);
                spans_no[i].update(' '+(i+1)+'.');
			}
			
			default_curtain_clicked();
		}*/
		onSuccess: function (m) {
            eval("var json = "+m.responseText);

			for(var tr_key in json){
				if(json[tr_key]['bn_notify'] != undefined){
					if(!confirm(json[tr_key]['bn_notify'])) break;
				}
        		new Insertion.Bottom($('do_items'),json[tr_key]['rowdata']);
        		if($('sn_details').innerHTML.trim() == ''){
	        		$('sn_dtl_icon').src = '/ui/collapse.gif';
	        		$('sn_title').show();
	        		$('sn_details').show();
	        	}
        		if(json[tr_key]['sn']) new Insertion.Bottom($$('.sn_details').first(), json[tr_key]['sn']);
			}
		},
		onComplete: function (m) {
			calc_all_items();
            reset_row();
		},
	});
	default_curtain_clicked();
	clear_autocomplete();
}

{/literal}
</script>

<div style="float:right"><img src="ui/closewin.png" onClick="default_curtain_clicked();" /></div>
<h2>Multi Add</h2>
<ul style="color:#0000ff;">
Note:<br />
<li>BOM SKU [Package] cannot be added from Multi Add.</li>
</ul>
<form name="f_mul" id="f_mul">
<input type=hidden name=id value="{$smarty.request.do_id}">
{*<input type=hidden name=branch_id value="{$smarty.request.branch_id}" />
<input type=hidden name=do_branch_id value="{$smarty.request.do_branch_id}" />
<input type=hidden name=price_indicate value="{$smarty.request.price_indicate}" />
<input type="hidden" name="open_info[name]" value="{$smarty.request.open_info_name}" />*}
<input type="hidden" name="do_type" value="{$smarty.request.do_type}" />

<table width="100%" id="tbl_multi_add">
<tr style="background:#fe9;">
	<th><input type="checkbox" id="inp_chx_all" onClick="toggle_multi_add(this);"></th>
	<th>ARMS Code</th>
	<th>Description</th>
	<th>Art. No</th>
	<th>Mcode</th>
	<th>Stock<br>Balance</th>
	<th>Selling Price</th>
	<th>Discount Code</th>
</tr>

<tbody style="overflow-x:hidden;overflow-y:auto;height:370px;">

{foreach from=$items key=sid item=r}
	<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
		<td><input type="checkbox" name="sid[]" value="{$sid}" {if $r.is_bom && $r.bom_type eq 'package'}disabled{else}class="mul"{/if}  /></td>
		<td class="small">{$r.sku_item_code}</td>
		<td>
			{$r.description}
			{if $r.is_bom && $r.bom_type eq 'package'}
				<span style="color:#0000ff;">[BOM PACKAGE]</span>
			{/if}
		</td>
		<td class="small">{$r.artno|default:'-'}</td>
		<td class="small">{$r.mcode|default:'-'}</td>
		<td class="small" align="right">{$r.qty|default:'-'}</td>
		<td class="small" align="right">{$r.price|default:'-'}</td>
		<td class="small" align="center">{$r.discount_code|default:'-'}</td>
	</tr>
{/foreach}
</tbody>
</table>
</form>

<p align="center"><input type="button" value="Add" onClick="submit_multi(this);" /></p>
