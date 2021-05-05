<script>
{literal}
select_vendor_for_po = function(){
	var vid = $('sel_vendor_id_for_po').value;
	if(vid==''){
		$('div_items_to_po').update('');
		return;
	}
	
	$('sel_vendor_id_for_po').disabled = true;
	$('div_items_to_po').update(PROCESSING);
	new Ajax.Updater('div_items_to_po',phpself+'?a=ajax_load_vendor_sku&ajax=1',{
		method: 'post',
		parameters:{
			vendor_id: vid,
			from_branch: branch_id
		},
		onComplete: function(){
			$('sel_vendor_id_for_po').disabled = false;
		}
	});
}

get_price_history = function(obj,id){
	$('price_history').show();
	center_div('price_history');
	$('price_history_list').update(_loading_);
	new Ajax.Updater('price_history_list','ajax_autocomplete.php',{
		parameters: 'a=sku_cost_history&id='+id
	});
}

generate_po = function(){
	var all_chx = $$('#f_sku_to_po input.chx_item_for_po');
	var selected_count = 0;
	for(var i=0; i<all_chx.length; i++){
		if(all_chx[i].checked){
			selected_count++;
			break;
		}	
	}
	if(selected_count<=0){
		alert('No Item Selected.');
		return;
	}
	
	if(!confirm("Click OK to generate PO"))	return;
	
	$('inp_generate_po').disabled = true;
	$('sel_vendor_id_for_po').disabled = true;
	$('span_generating_po').update(PROCESSING);
	new Ajax.Request(phpself+'?a=ajax_generate_po&ajax=1',{
		parameters: $('f_sku_to_po').serialize(),
		onComplete: function(msg){
			eval('var json='+msg.responseText);
			$('inp_generate_po').disabled = false;
			$('sel_vendor_id_for_po').disabled = false;
			$('span_generating_po').update('');
			
			if(json['success']){
				window.open('po.php?a=open&id='+json['po_id']+'&branch_id='+json['branch_id']);
				list_sel();
				select_vendor_for_po();
			}else{
				alert(json['error']);
			}
		}
	});
}
{/literal}
</script>

<p>
<b>Select Vendor</b>
<select id="sel_vendor_id_for_po" onChange="select_vendor_for_po();">
	<option value="">-- Please Select --</option>
	{foreach from=$vendor_info key=vid item=v}
		<option value="{$vid}">{$v.code} - {$v.description}</option>
	{/foreach}
</select></p>

<div id="div_items_to_po" style="border:1px solid #eee;height:400px;overflow-x:hidden;overflow-y:auto;background-color:#fff;">
</div>
