/*
6/23/2014 5:19 PM Justin
- Enhanced to have new feature that can add Serial No by range.

7/9/2014 11:26 AM Justin
- Enhanced to have new handler for S/N.
- Bug fixed on system unable to auto insert row for cash / credit sales.

7/10/2014 5:05 PM Justin
- Bug fixed on system shows javascript error while in approval screen.

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

1/27/2015 11:18 AM Justin
- Enhanced to auto assign debtor's/branch's contact no and email when having serial no.

3/29/2017 4:03 PM Justin
- Enhanced to get DO ID for function add_parent_child.

11/21/2018 3:06 PM Justin
- Enhanced to have new function to auto focus on ctn and pcs.

12/3/2018 2:11 PM Justin
- Enhanced to auto select the value when auto focus.
*/

// recalculate the current SN used from rcv qty
recalc_sn_used = function(item_id, bid){

	var do_type = document.f_a['do_type'].value;
	if(do_type == "transfer" && document.f_a.elements['sn['+item_id+']['+bid+']'] == undefined) return;
	// calculate the total SN based in branch
	var uom = document.f_a.elements['sel_uom['+item_id+']'].value;
	var uom_fraction = uom.split(',');
	var qty = $('row_qty'+item_id).innerHTML;
	var ttl_qty_used = 0;
	var ttl_bal_qty = 0;
	var sn_msg = '';
	var do_type = document.f_a['do_type'].value;

	if(document.f_a.elements['qty_ctn['+item_id+']['+bid+']'] != undefined){
		var ctn = document.f_a.elements['qty_ctn['+item_id+']['+bid+']'].value;
		var pcs = document.f_a.elements['qty_pcs['+item_id+']['+bid+']'].value;
	}else{
		var ctn = document.f_a.elements['qty_ctn['+item_id+']'].value;
		var pcs = document.f_a.elements['qty_pcs['+item_id+']'].value;
	}

	var b_rcv_qty = float(ctn) * float(uom_fraction[1]) + float(pcs);

	if(do_type == "transfer"){ // do these when it is transfer DO
		document.f_a.elements['b_sn_rcv_qty['+item_id+']['+bid+']'].value = b_rcv_qty;
		var b_sn = document.f_a.elements['sn['+item_id+']['+bid+']'].value;
		var b_split_sn = b_sn.split('\n');
		var b_ttl_qty_used = 0;
		var b_sn_msg = ' ';
	
		for(var i=0; i<b_split_sn.length; i++){
			if(b_split_sn[i].trim() != "") b_ttl_qty_used++;
		}
	
		var b_ttl_bal_qty = float(b_rcv_qty) - float(b_ttl_qty_used);
	
		if(b_ttl_bal_qty > 0) b_sn_msg = " ("+b_ttl_bal_qty+" qty remaining)";
		else if(b_ttl_bal_qty < 0) b_sn_msg = "<b><font color=\"#ff0000\">(Over "+Math.abs(b_ttl_bal_qty)+" S/N)</font></b>";
	
		var del_b = document.f_a.elements["deliver_branch[]"];
		if(typeof(del_b)!='undefined') $('sn_branch_label_'+item_id+'_'+bid).update(b_sn_msg);
	
		// calculalate total SN used
		var all_sn = $$('.sn_details textarea');
		var sn_total_rows = all_sn.length;
	
		$A(all_sn).each(
			function (r,idx){
				if(r.name.indexOf('sn['+item_id+'][') == 0){
					var sn = r.value;
					split_sn = sn.split('\n');
	
					for(var i=0; i<split_sn.length; i++){
						if(split_sn[i].trim() != '') ttl_qty_used++;
					}
				}
			}
		);
		bal_qty = float(qty) - float(ttl_qty_used);
	
		if(qty != bal_qty && bal_qty != 0){
			if(bal_qty >= 0) sn_msg = " ("+bal_qty+" qty remaining)";
			else sn_msg = " <b><font color=\"#ff0000\">(Over "+Math.abs(bal_qty)+" S/N)</font></b>";
		}
	}else{ // for cash and credit sales
		var prev_qty = document.f_a.elements['sn_rcv_qty['+item_id+']'].value;
		var curr_qty = b_rcv_qty - prev_qty;

		if(b_rcv_qty > 0) $('sn_item_table_'+item_id).show();

		if(curr_qty > 0){
			for(var i=prev_qty; i<b_rcv_qty; i++){
				add_row(item_id, i);
			}
		}else{
			for(var i=prev_qty-1; i>=b_rcv_qty; i--){
				Element.remove($('sn_item_row_'+item_id+'_'+i));
			}

			if(b_rcv_qty == 0) $('sn_item_table_'+item_id).hide();
		}
	}

	sn_msg = round(qty) + sn_msg;

	$('bal_qty_'+item_id).update(sn_msg);
	document.f_a.elements['sn_rcv_qty['+item_id+']'].value = qty;
	//document.f_a.elements['ttl_sn['+sku_item_id+']['+sbid+']'].value = ttl_qty_used;
}

sh_serial_no = function (obj){
	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		$('sn_details').show();
	}else{
		obj.src = '/ui/expand.gif';
		$('sn_details').hide();
	}
}

add_row = function(id, row){
	var new_tr = $('temp_sn_row').cloneNode(true).innerHTML;
	new_tr = new_tr.replace(/__sn__id/g, id);
	new_tr = new_tr.replace(/__sn__row/g, row);

	new Insertion.Bottom($('sn_list_'+id), new_tr);
	
	document.f_a['sn_nric['+id+']['+row+']'].value = document.f_a['mst_sn_nric'].value;
	document.f_a['sn_name['+id+']['+row+']'].value = document.f_a['mst_sn_name'].value;
	document.f_a['sn_address['+id+']['+row+']'].value = document.f_a['mst_sn_address'].value;
	document.f_a['sn_cn['+id+']['+row+']'].value = document.f_a['mst_sn_cn'].value;
	document.f_a['sn_email['+id+']['+row+']'].value = document.f_a['mst_sn_email'].value;
	document.f_a['sn_we['+id+']['+row+']'].value = document.f_a['si_sn_we['+id+']'].value;
	document.f_a['sn_we_type['+id+']['+row+']'].value = document.f_a['si_sn_we_type['+id+']'].value;
	
}

cron_val = function(obj){
	field_name = obj.name;
	field_elements = field_name.split("[");
	var name = field_elements[0];
	var id = field_elements[1].replace("]", "");
	var row = field_elements[2].replace("]", "");
	var next_row = float(row)+1;

	if(document.f_a.elements[name+"["+id+"]["+next_row+"]"]) document.f_a.elements[name+"["+id+"]["+next_row+"]"].value = obj.value;

	// auto retrieve data when user key in nric
	if(name == "sn_nric"){
		new Ajax.Request("do.php",{
			method:'post',
			parameters: 'a=find_member_info&type='+name+'&value='+obj.value,
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {
	            if(m.responseText){
					var m_info = m.responseText;
					var member_info = m_info.split("|");
					document.f_a.elements["sn_name["+id+"]["+row+"]"].value = member_info[0];
					document.f_a.elements["sn_address["+id+"]["+row+"]"].value = member_info[1];
					document.f_a.elements["sn_cn["+id+"]["+row+"]"].value = member_info[2];
					document.f_a.elements["sn_email["+id+"]["+row+"]"].value = member_info[3];

					if(document.f_a.elements[name+"["+id+"]["+next_row+"]"]){
						document.f_a.elements["sn_name["+id+"]["+next_row+"]"].value = member_info[0];
						document.f_a.elements["sn_address["+id+"]["+next_row+"]"].value = member_info[1];
						document.f_a.elements["sn_cn["+id+"]["+next_row+"]"].value = member_info[2];
						document.f_a.elements["sn_email["+id+"]["+next_row+"]"].value = member_info[3];
					}
				}
	    	}
		});
	}	
}

confirmExit = function(e) {
	if(!e) e = window.event;
	if(needCheckExit){
		//e.cancelBubble is supported by IE - this will kill the bubbling process.
		/*e.cancelBubble = true;
		e.returnValue = 'Are You sure you want to leave at this time? Sales will be in-correct if finalize does not fully complete. '; //This is displayed on the dialog
	
		//e.stopPropagation works in Firefox.
		if (e.stopPropagation) {
			e.stopPropagation();
			e.preventDefault();
		}*/
		
		return 'Data had not being saved.';
	}
}

add_sn_by_range_clicked = function(item_id, sid){
	if(!sid) return;
	
	$('div_sn_by_range_popup_content').update(_loading_);
	var do_type = document.f_a['do_type'].value;
	
	curtain(true);
	center_div($('div_sn_by_range_popup').show());

	var do_branch_list = [];
	if(do_type == "transfer"){
		do_branch_list = get_do_branch_list();
	}else{
		do_branch_list.push(document.f_a['branch_id'].value);
	}
	
	var params = {
		'a': 'ajax_show_sn_by_range',
		'item_id': item_id,
		'sid': sid,
		'deliver_branch[]': do_branch_list,
		'branch_id': document.f_a['branch_id'].value
	};
			
	var THIS = this;
	
	new Ajax.Request('do.php', {
		parameters: params,
		onComplete: function(msg){
			
			// insert the html at the div bottom
			var str = msg.responseText.trim();
			var ret = {};
			var err_msg = '';

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['html']){ // success
					$('div_sn_by_range_popup_content').update(ret['html']);
					return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			// prompt the error
			//alert(err_msg);
			$('div_sn_by_range_popup_content').update(err_msg);
		}
	});
}

add_sn_by_range = function(){
	var sn_from = $('sn_range_from').value.trim();
	var sn_to = $('sn_range_to').value.trim();
	var item_id = $('sn_range_item_id').value;
	var bid = $('sn_range_bid').value;
	var ttl_qty_used = 0;

	if(!bid){
		alert("Please select a Branch.");
		return false;
	}else if(!sn_from || !sn_to){
		alert("Please set S/N range From and To.");
		return false;
	}else if(sn_from > sn_to){
		alert("Invalid S/N range From and To.");
		return false;
	}
	
	var sn_by_range_qty = int(sn_to-sn_from);

	if(do_type == "transfer"){
		var item_qty = document.f_a['b_sn_rcv_qty['+item_id+']['+bid+']'].value;
		var b_sn = document.f_a.elements['sn['+item_id+']['+bid+']'].value;
		var b_split_sn = b_sn.split('\n');

		for(var i=0; i<b_split_sn.length; i++){
			if(b_split_sn[i].trim() != "") ttl_qty_used++;
		}

		var sn_bal_qty = int(item_qty-ttl_qty_used);
		
		// check if the total of S/N by range is it greater than current do item qty
		if(sn_by_range_qty >= sn_bal_qty){
			alert("Total qty of S/N by range cannot greater than item qty");
			return false;
		}

		for(var i=0; i<=sn_by_range_qty; i++){
			var new_sn = float(sn_from)+float(i);
			if(document.f_a['sn['+item_id+']['+bid+']'].value != "") document.f_a['sn['+item_id+']['+bid+']'].value += "\n";
			document.f_a['sn['+item_id+']['+bid+']'].value += int(new_sn);
		}
	}else{
		var item_qty = document.f_a['sn_rcv_qty['+item_id+']'].value;
		var sn_inserted_count = 0;
		
		for(var i=0; i<item_qty; i++){
			if(document.f_a['sn['+item_id+']['+i+']'].value.trim() != "") ttl_qty_used++;
		}
		
		var sn_bal_qty = int(item_qty-ttl_qty_used);
		
		// check if the total of S/N by range is it greater than current do item qty
		if(sn_by_range_qty >= sn_bal_qty){
			alert("Total qty of S/N by range cannot greater than item qty");
			return false;
		}
		
		for(var i=0; i<=item_qty; i++){
			var new_sn = float(sn_from)+float(sn_inserted_count);
			if(document.f_a['sn['+item_id+']['+i+']'].value.trim() == ""){
				document.f_a['sn['+item_id+']['+i+']'].value = int(new_sn);
				sn_inserted_count++;
			}
			
			if(sn_inserted_count > sn_by_range_qty){
				break;
			}
		}
	}
	recalc_sn_used(item_id, bid);
	default_curtain_clicked();
}

toggle_sn_details = function(obj){
	var sn_data = $('sn_details').getElementsByClassName('sn_data');

	if(sn_data.length == 0){
		$('sn_dtl_icon').src = '/ui/expand.gif';
		$('sn_details').hide();
		$('sn_title').hide();
	}else{
		$('sn_dtl_icon').src = '/ui/collapse.gif';
	    $('sn_title').show();
	    $('sn_details').show();

		var do_type = document.f_a['do_type'].value;
		
		if(do_type != "transfer"){
			if(do_type == "open"){
				var debtor_name = document.f_a['open_info[name]'].value;
				var debtor_address = document.f_a['open_info[address]'].value;
				var debtor_contact = document.f_a['open_info[contact_no]'].value;
				var debtor_email = document.f_a['open_info[email]'].value;
			}else{
				var opt = document.f_a['debtor_id'].options[document.f_a['debtor_id'].selectedIndex];
				var debtor_name = opt.readAttribute('db');
				var debtor_address = opt.readAttribute('db_address');
				var debtor_contact = opt.readAttribute('db_contact');
				var debtor_email = opt.readAttribute('db_email');
			}
			
			if(obj != undefined || (document.f_a['mst_sn_name'] != undefined && document.f_a['mst_sn_name'].value == "")){
				document.f_a['mst_sn_name'].value = debtor_name;
			}

			if(obj != undefined || (document.f_a['mst_sn_address'] != undefined && document.f_a['mst_sn_address'].value == "")){
				document.f_a['mst_sn_address'].value = debtor_address;
			}

			if(obj != undefined || (document.f_a['mst_sn_cn'] != undefined && document.f_a['mst_sn_cn'].value == "")){
				document.f_a['mst_sn_cn'].value = debtor_contact;
			}
			
			if(obj != undefined || (document.f_a['mst_sn_email'] != undefined && document.f_a['mst_sn_email'].value == "")){
				document.f_a['mst_sn_email'].value = debtor_email;
			}
			
			if(obj != undefined){
				sn_mst_info_changed(obj);
			}
		}
	}
}

sn_mst_info_changed = function(obj){
	var field_name = obj.name;
	var field_value = obj.value;
	
	if(field_name == undefined) return;
	
	field_name = field_name.replace(/mst_/g, "");
	
	var sn_data = $('sn_details').getElementsByClassName(field_name);
	
	$A(sn_data).each(
		function (r,idx){
			if(r != undefined) r.value = field_value;
		}
	);
}

add_parent_child = function(){
	var sid = int(document.f_a.sku_item_id.value);
	var do_id = int(document.f_a.id.value);

    if (sid==0){
		alert('No item selected');
		$('autocomplete_sku').value = '';
	    return false;
    }
	$('div_pc_table').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('div_pc_table');
	center_div('div_pc_table');
	curtain(true);

	new Ajax.Updater('div_pc_table','do.php',{
		    method:'post',
		    parameters: 'a=ajax_load_parent_child&sku_item_id='+sid+'&do_id='+do_id,
		    evalScripts: true
	});
}

submit_parent_child = function(ele){
	var is_checked = false;
	$$('#tbl_pc_add input.pc_checkbox').each(function(chx){
		if(chx.checked) is_checked = true;
	});

	if(!is_checked){
		alert("Please tick a item to add.");
		return false;
	}
	
	ele.disabled = true;
	ele.value = 'Processing...'
	
	parms = Form.serialize(document.f_pc)+"&"+Form.serialize(document.f_a)+"&a=ajax_parent_child_add";
	
    new Ajax.Request('do.php',{
        method: 'post',
		parameters: parms,
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

/*hide_context_menu = function(){
	$('ul_menu').onmouseout = undefined;
	$('ul_menu').onmousemove = undefined;	 
	Element.hide('item_context_menu');
}

show_context_menu = function(obj, id, item_id){
	context_info = { element: obj, id: id, sku_item_id: item_id };
	$('item_context_menu').style.left = ((document.body.scrollLeft)+mx) + 'px';
	$('item_context_menu').style.top = ((document.body.scrollTop)+my) + 'px';
	Element.show('item_context_menu');
	
	$('ul_menu').onmouseout = function() {
		context_info.timer = setTimeout('hide_context_menu()', 100);
	}
	
	$('ul_menu').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	
	return false;
}*/

qty_keypressed = function(obj, e) {
	var e = (typeof event != 'undefined') ? window.event : e; // IE : Moz 
	if (e.keyCode == 13) {
		var qty_ele = $$('#new_sheets input.qty_fields');
		for (var i = 0; i < qty_ele.length; i++) {
			var q = i + 1; // next field
			if (qty_ele[q] != undefined && obj == qty_ele[i]){
				var pass = false;
				do{ // get the next qty field which is not readonly and disable
					if(qty_ele[q].disabled == true || qty_ele[q].readOnly == true){
						q = float(q) + 1;
					}else{
						pass = true;
					}
				}while(pass!=true);
				
				qty_ele[q].focus();
				qty_ele[q].select();
				break;
			}
		}
		return false;
	}
}