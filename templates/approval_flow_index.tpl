{*
revision history
================
2009-1-12 4:36 pm Andy - add filter, if v=='INVOICE', disabled dept selection, enable sku type

1/15/2010 4:24:16 PM Andy
- add more approver order selection in settings

3/17/2010 4:37:02 PM Andy
- Fix a bugs when "No Approver" is choose and din't add any user as approval, system block user to save.

3/18/2010 10:40:35 AM Andy
- Change approval flow order default only have "Follow Sequences" & "Anyone", others will be available if have config

6/16/2010 4:27:57 PM Justin
- Added config for Approval Flow - $config['do_approval_by_department'].
- Added the checking function on JS as below:
  -> If found user selected DO and config is being set, system will allow user to select Department.
  -> if found without either DO or config, system will remain display Department.

10/22/2010 11:32:41 AM Justin
- disable sku_category_id field when value is equal to membership redemption

10/22/2010 11:32:41 AM Justin
- Enhanced to disable category selection when choosing Future Change Price approval.

7/4/2013 3:14 PM Justin
- Enhanced to enable back that GRA can assign approver.

7/16/2013 4:49 PM Andy
- Enhance the edit approval to have selection of send PM/Email/SMS, and also minimum document amount.
- Change the search user to search by autocomplete.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

4/24/2015 10:04 AM Justin
- Enhanced to allow user can choose all approval orders while add/edit GRN approval flow.

10/6/2015 3:33 PM Andy
- Enhanced to check approval type "CN".

10/23/2018 3:08 PM Justin
- Enhanced the module to compatible with new SKU Type.
- Enhanced to load SKU Type list from database instead of hardcoded it.

6/3/2019 9:42 AM Andy
- Added new approval flow "CYCLE COUNT".
*}

{include file=header.tpl}

<style>
{literal}
input.chx-approval-min_doc_amt[readonly]{
	background-color: #DCDCDC;
}
{/literal}
</style>

<script type="text/javascript">
var lastn = '';
var do_config_by_department = int("{$config.do_approval_by_department}");
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
	resetlist();
	sel_type(document.f_b.type.value);
	document.f_b.changed_fields.value = '';
	document.f_b.branch_id.focus();
}

function ed(n)
{
	document.getElementById('abtn').style.display = 'none';
	document.getElementById('ebtn').style.display = '';
	document.getElementById('bmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	showdiv('ndiv');
	document.f_b.id.value = n;
	_irs.document.location = '?a=e&id='+n;
	lastn = n;

	document.f_b.a.value = 'u';
	document.f_b.branch_id.focus();
}

function add()
{
	showdiv('ndiv');
	sel_type(document.f_b.type.value);
	document.getElementById('abtn').style.display = '';
	document.getElementById('ebtn').style.display = 'none';
	document.getElementById('bmsg').innerHTML = 'Enter the following and click ADD';
	document.f_b.reset();
	document.f_b._approvals.value = '';
	document.f_b.id.value = 0;
	document.f_b.a.value = 'a';
	$('tbl_approver').show();
	resetlist();
	document.f_b.branch_id.focus();
}

function save_as()
{
	if (!confirm('Save as new approval flow?')) return;

	if (check_b())
	{
		document.f_b.a.value = 'a';
	    document.f_b.id.value = 0;
	    document.f_b.submit();
	}
}

function act(n, s)
{
	_irs.document.location = '?a=v&id='+n+'&v='+s;
}

function check_b()
{
	sel1 = document.f_b.elements["sel_approvals[]"];
	sel2 = document.f_b.elements["sel_notify[]"];
	var aorder_id = document.f_b['aorder'].value;
	
	if (sel1.options.length <= 0 && !$('move_approvals').disabled)
	{
	    if(aorder_id!=4){   // 4 == no need approval
            alert('You must select at least one user for approval');
			return false;
		}
		
	}	
	if (sel2.options.length <= 0 && $('move_approvals').disabled)
	{
		alert('You must select at least one user for notification');
		return false;
	}
	for (i=0;i<sel1.options.length;i++)
	{
	    sel1.options[i].selected = true;
	}
	for (i=0;i<sel2.options.length;i++)
	{
	    sel2.options[i].selected = true;
	}

	return true;
}

function mdn(src)
{
    if (src.selectedIndex == -1)
	{
		alert('Select an item to move');
		return;
	}
	if (src.selectedIndex < src.options.length-1)
	{
	    // move it
		var t = src.options[src.selectedIndex+1].text;
		var v = src.options[src.selectedIndex+1].value;
		src.options[src.selectedIndex+1].text = src.options[src.selectedIndex].text;
		src.options[src.selectedIndex+1].value = src.options[src.selectedIndex].value;
		src.options[src.selectedIndex].text = t;
		src.options[src.selectedIndex].value = v;
        src.selectedIndex++;
	}

}

function mup(src)
{
    if (src.selectedIndex == -1)
	{
		alert('Select an item to move');
		return;
	}
	if (src.selectedIndex > 0)
	{
	    // move it
		var t = src.options[src.selectedIndex-1].text;
		var v = src.options[src.selectedIndex-1].value;
		src.options[src.selectedIndex-1].text = src.options[src.selectedIndex].text;
		src.options[src.selectedIndex-1].value = src.options[src.selectedIndex].value;
		src.options[src.selectedIndex].text = t;
		src.options[src.selectedIndex].value = v;
        src.selectedIndex--;
	}

}

function mv(src, dst)
{
	var i = 0;
	while (i<src.options.length)
	{
	    if (src.options[i].selected)
	        dst.appendChild(src.options[i])
		else
		    i++;
	}
}

function mvall(src, dst)
{
	while (src.options.length>0)
	{
		dst.options[dst.options.length] = new Option(src.options[0].text, src.options[0].value)
		src.options[0] = null;
	}
}

// reset the src list according to _approvals field
function resetlist()
{
	str1 = document.f_b._approvals.value;
	str2 = document.f_b._notify_users.value;
	sel1 = document.f_b.elements["sel_approvals[]"];
	sel2 = document.f_b.elements["sel_notify[]"];
	src = document.f_b.src_approvals;

	sel1.options.length=0;
	sel2.options.length=0;
	src.options.length=0;

	if (str1 != '' || str2 != '')
	{
		// populate unselected users
		for (i in user_list)
		{
		    if (!parseInt(i)) continue;
			if (str1.indexOf('|'+i+'|') < 0 && str2.indexOf('|'+i+'|') < 0)
				src.options[src.options.length] = new Option(user_list[i], i);
		}

		// populate selected users
		ww = new String(str1);
		arr = ww.split("|");
		for (i=0; i<arr.length;i++)
		{
		    if (arr[i]=='') continue;
		    sel1.options[sel1.options.length] = new Option(user_list[arr[i]], arr[i]);
		}

		// populate selected users
		ww = new String(str2);
		arr = ww.split("|");
		for (i=0; i<arr.length;i++)
		{
		    if (arr[i]=='') continue;
		    sel2.options[sel2.options.length] = new Option(user_list[arr[i]], arr[i]);
		}
	}
	else
	{
		// no selection, default all to src
		for (i in user_list)
		{
		    if (!parseInt(i)) continue;
			src.options[src.options.length] = new Option(user_list[i], i);
		}
	}
}

function sel_type(v)
{
	$('tremark').innerHTML = '';

    //$('move_approvals').disabled = (v == 'GOODS_RETURN_ADVICE');
    
	if (v == 'SKU_APPLICATION' || v=='INVOICE')
	{
	    //$('st').style.display = '';
	    document.f_b.sku_type.disabled=false;
	}
	else
	{
	    //$('st').style.display = 'none';
	    document.f_b.sku_type.disabled=true;
	}
	if (v=='E_FORM'|| v == 'MKT1' || v=='MKT3' || v=='MKT5' || v=='DO' || v=='PROMOTION' || v=='COUNTER_COLLECTION' || v=='INVOICE' || v=='ADJUSTMENT' || v=='SALES_ORDER' || v=='CREDIT_NOTE' || v=='DEBIT_NOTE' || v=='MEMBERSHIP_REDEMPTION' || v=='MST_FUTURE_PRICE' || v=='CN')
	{
	    //$('dpt').style.display = 'none';
	    document.f_b.sku_category_id.disabled=true;
	}
	/*else if(v=='MKT5'){
		document.f_b.branch_id.disabled=true;
	}*/
	else
	{
	    //$('dpt').style.display = '';
	    document.f_b.sku_category_id.disabled=false;
	}
  aorder_changed();
}

function reload_table()
{
	new Ajax.Updater("udiv", "approval_flow.php",{
	    parameters: 'a=ajax_reload_table&'+Form.serialize('f_f')
	});
}

function aorder_changed(){
  var v = document.f_b['type'].value;
  if(v=='GOODS_RECEIVING_NOTE'){  // GRN can only use "Anyone"
    //document.f_b['aorder'].value = 3;
    //$('span_aorder_sel').hide();  
    //$('span_aorder_str').update('Anyone').show();
  }else{
    $('span_aorder_sel').show();
    $('span_aorder_str').hide();  
  }
  
  var id = document.f_b['aorder'].value;
  if(id==4){
    $('tbl_approver').hide();
  }else $('tbl_approver').show();
  
  if(do_config_by_department && document.f_b['type'].value == 'DO'){
  	document.f_b['sku_category_id'].disabled = false;
  }
}

{/literal}
var user_list = new Array();{section name=i loop=$users}user_list[{$users[i].id}] = '{$users[i].u}';{/section}
{literal}

var APPROVAL_FLOW = {
	f: undefined,
	user_autocomplete: undefined,
	initialize: function(){
		new Draggable('div_approval_flow_popup',{ handle: 'div_approval_flow_popup_header'});
	},
	// function to add new approval flow
	add: function(){
		this.open(0);
	},
	// function to edit approval flow
	open: function(id){
		if(!id)	id = 0;
		
		$('div_approval_flow_popup_content').update(_loading_);

		jQuery('#div_approval_flow_popup').modal('show');

		var params = {
			'a': 'open_approval_flow',
			'id': id
		};
				
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	 					$('div_approval_flow_popup_content').update(ret['html']);
	 					THIS.f = document.f_af;
	 					THIS.flow_type_changed();
	 					THIS.check_all_approval_arrow();
	 					THIS.reset_user_autocomplete();
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
			    $('div_approval_flow_popup_content').update(err_msg);
			}
		});
	},
	// function when user change approval order
	aorder_changed: function(){

		if(this.f['aorder'].value == 4){	// No Approver
			$('div_approval_curtain').show();
		}else{
			$('div_approval_curtain').hide();
		}
		
	},
	// function when user change flow type
	flow_type_changed: function(){
		var choose_dept = true;
		var choose_sku_type = true;
		var can_edit_doc_amt = true;	
		var flow_type = this.f['type'].value;
		
		
		this.f['sku_category_id'].disabled = false;
		$('rq_img3').show();
		this.f['sku_type'].disabled = false;
		$('rq_img4').show();
		
		for(var i=0,len=this.f['aorder'].length; i<len; i++){
			this.f['aorder'].options[i].disabled = false;
		}
		
		if(flow_type){
			// check sku type
			if (flow_type != 'SKU_APPLICATION' && flow_type !='INVOICE' && flow_type != 'GOODS_RETURN_ADVICE')	choose_sku_type = false;
			
			// check dept
			if (flow_type =='E_FORM'|| flow_type == 'MKT1' || flow_type =='MKT3' || flow_type =='MKT5' || flow_type =='PROMOTION' || flow_type =='COUNTER_COLLECTION' || flow_type =='INVOICE' || flow_type =='ADJUSTMENT' || flow_type =='SALES_ORDER' || flow_type =='CREDIT_NOTE' || flow_type =='DEBIT_NOTE' || flow_type =='MEMBERSHIP_REDEMPTION' || flow_type =='MST_FUTURE_PRICE' || flow_type == 'CN' || flow_type == 'CYCLE_COUNT'){
			   choose_dept = false;
			}
			if(flow_type == 'DO' && !do_config_by_department)	choose_dept = false;

			// check doc amt			
			if(flow_type != 'ADJUSTMENT' && flow_type != 'PURCHASE_ORDER' && flow_type != 'PURCHASE_ORDER_REQUEST' && flow_type != 'DO'  && flow_type != 'INVOICE' && flow_type != 'SALES_ORDER' && flow_type != 'GOODS_RETURN_ADVICE' && flow_type != 'CN'){
				can_edit_doc_amt = false;
			}
	
			if(!choose_dept){	// not allow to choose dept
				this.f['sku_category_id'].value = '';
				this.f['sku_category_id'].disabled = true;
				$('rq_img3').hide();
			}
			
			if(!choose_sku_type){	// not allow to choose sku type
				this.f['sku_type'].value = '';
				this.f['sku_type'].disabled = true;
				$('rq_img4').hide();
			}
			
			if(flow_type == 'GOODS_RECEIVING_NOTE'){  // GRN can only use "Anyone"
			    //this.f['aorder'].value = 3;	// auto change to chhose anyone
			    /*for(var i=0,len=this.f['aorder'].length; i<len; i++){
			    	if(this.f['aorder'].options[i].value != 3){
			    		this.f['aorder'].options[i].disabled = true;
			    	}
				}*/
				this.aorder_changed();
			}
		}
		
		$$('#div_approvals input.chx-approval-min_doc_amt').each(function(ele){
			if(can_edit_doc_amt){	// can edit min doc amt
				ele.readOnly = false;
			}else{	// not allow to edit min doc amt
				ele.readOnly = true;
			}
		});
	},
	// function when user change minimum document amount
	min_doc_amt_changed: function(type, user_id){
		if(type == 'approval'){
			mfz(this.f['approval_settings[approval]['+user_id+'][min_doc_amt]'], 2);
		}
	},
	// function when user tick/untick all send type
	update_all_user_approval_notify_settings: function(approval_or_notify, send_type, checked){
		$(this.f).getElementsBySelector('input.chx-'+approval_or_notify+'-'+send_type).each(function(ele){
			ele.checked = checked;
		});
	},
	// function when user tick/untick send type by user
	update_user_approval_notify_settings: function(approval_or_notify, uid, checked){
		$$('#tr-'+approval_or_notify+'-'+uid+' input.chx-'+approval_or_notify).each(function(ele){
			ele.checked = checked;
		});
	},
	// function when user click to assign all user minimum doc amt
	update_all_user_min_doc_amt: function(){
		var amt = prompt('Please key in the Minimum Document Amount for all approval.');
		if(amt == undefined)	return;
		
		amt = float(round(amt, 2));
		if(amt<=0)	amt = '';
		
		$(this.f).getElementsBySelector('input.chx-approval-min_doc_amt').each(function(ele){
			ele.value = amt ? round(amt,2) : '';
		});
	},
	// function when user click to remove approval user
	delete_approval: function(uid){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr-approval-'+uid).remove();
		this.check_all_approval_arrow();
	},
	// function when user click to remove notify user
	delete_notify: function(uid){
		if(!confirm('Are you sure?'))	return false;
		
		$('tr-notify-'+uid).remove();
	},
	// function to auto check all approval arrow
	check_all_approval_arrow: function(){
		var approval_list = $$('#tbl_approvals tr.tr-approval');
		for(var i=0,len=approval_list.length; i<len; i++){
			var uid = this.get_approval_user_id_by_ele(approval_list[i]);
			
			var img_up = $('img-approval-up-'+uid);
			var img_down = $('img-approval-down-'+uid);
			
			if(i == 0){	// first row
				img_up.style.visibility = 'hidden';
			}else{
				img_up.style.visibility = '';
			}
			
			if(i == len-1){	// last row
				img_down.style.visibility = 'hidden';
			}else{
				img_down.style.visibility = '';
			}
		}
	},
	// function to return approval user_id by getting element
	get_approval_user_id_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr-approval')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var uid = parent_ele.id.split('-')[2];
		return uid;
	},
	// function to return notify user id by getting element
	get_notify_user_id_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr-notify')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var uid = parent_ele.id.split('-')[2];
		return uid;
	},
	// function when user move approval up/down
	move_approval: function(uid, arrow){
		var tr = $('tr-approval-'+uid);
		var target_tr;
		
		if(arrow == 'up'){
			target_tr = $(tr).previous();
		}else{
			target_tr = $(tr).next();
		}
		if(!target_tr)	return false;
		
		swap_ele(tr ,target_tr);
		this.check_all_approval_arrow();
	},
	// function to reset user autocomplete
	reset_user_autocomplete: function(){
		var THIS = this;
		this.user_autocomplete = new Ajax.Autocompleter('inp_search_username','div_autocomplete_username','ajax_autocomplete.php?a=ajax_search_user',
			{
				'paramName': 'search_username',
				indicator: 'span_autocomplete_loading',
				afterUpdateElement:function(sel,li) {
					var uid = li.title;
					var username = $(li).readAttribute('u');
					$('inp_selected_user_id').value = uid;
					$('inp_selected_username').value = username;
				}
			}
		);
	},
	// function when user click add autocomplete user
	add_autocomplete_user_clicked: function(type){
		var uid = $('inp_selected_user_id').value;
		if(!uid){
			alert('Please search and select a user first.');
			return false;
		}
		
		if(type == 'approval'){
			this.add_approval(uid);	// add approval user
		}else if(type == 'notify'){
			this.add_notify(uid);	// add notify user
		}else{
			alert('Invalid Type.');
			return false;
		}
	},
	// function to add new approval user
	add_approval: function(uid){
		if(!uid)	return false;
		
		// find duplicate
		var tr_approval_list = $$('#tbody_approval_list tr.tr-approval');
		for(var i=0, len=tr_approval_list.length; i<len; i++){
			var tmp_uid = this.get_approval_user_id_by_ele(tr_approval_list[i]);
			
			if(uid == tmp_uid){
				alert('This user already in Approvals');
				return false;
			}
		}
		
		$('span_approval_loading').show();
		$('btn_add_appvoral').disabled = true;
		
		var params = $(this.f).serialize();
		
		var params2 = {
			'a': 'ajax_add_approval_user',
			'user_id': uid
		};
		params += '&'+$H(params2).toQueryString();
		var THIS = this;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				// hide the loading icon
			    $('span_approval_loading').hide();
			    // enable back the button
			    $('btn_add_appvoral').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    new Insertion.Bottom('tbody_approval_list', ret['html']);
	                    THIS.flow_type_changed();
	                    THIS.check_all_approval_arrow();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click add notify
	add_notify: function(uid){
		if(!uid)	return false;
		
		// find duplicate
		var tr_notify_list = $$('#tbody_notify_list tr.tr-notify');
		for(var i=0, len=tr_notify_list.length; i<len; i++){
			var tmp_uid = this.get_notify_user_id_by_ele(tr_notify_list[i]);
			
			if(uid == tmp_uid){
				alert('This user already in Notify');
				return false;
			}
		}
		
		$('span_notify_loading').show();
		$('btn_add_notify').disabled = true;
		
		var params = $(this.f).serialize();
		
		var params2 = {
			'a': 'ajax_add_notify_user',
			'user_id': uid
		};
		params += '&'+$H(params2).toQueryString();
		var THIS = this;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				// hide the loading icon
			    $('span_notify_loading').hide();
			    // enable back the button
			    $('btn_add_notify').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    new Insertion.Bottom('tbody_notify_list', ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'No response from server';
			    alert(err_msg);
			}
		});
	},
	// function when user click update
	update_clicked: function(){
		this.save_approval_flow();
	},
	// function when user click save as
	save_as_clicked: function(){
		this.save_approval_flow({'is_save_as':1});
	},
	// core function to save approval flow
	save_approval_flow: function(opt){
		if(!this.check_form())	return false;
		
		var params = $(this.f).serialize();
		
		if(opt){
			if(opt['is_save_as'])	params += '&is_save_as=1';
		}
		params += '&a=ajax_save_approval_flow';
		
		$$('#div_approval_flow_processing input').invoke('disable');
		$('span_approval_flow_processing').show();
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				$$('#div_approval_flow_processing input').invoke('enable');
				$('span_approval_flow_processing').hide();
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
	                	reload_table();
	                	alert('Save Successfully');
	                	default_curtain_clicked();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'No response from server';
			    alert(err_msg);
			}
		});
	},
	// function to check form before submit
	check_form: function(){
		// check branch
		if(!this.f['branch_id'].value){
			alert('Please select branch.');
			this.f['branch_id'].focus();
			return false;
		}
		
		// check flow type
		if(!this.f['type'].value){
			alert('Please select Flow Type.');
			this.f['type'].focus();
			return false;
		}
		
		// check dept
		if(!this.f['sku_category_id'].disabled){
			if(!this.f['sku_category_id'].value){
				alert('Please select Department.');
				this.f['sku_category_id'].focus();
				return false;
			}
		}
		
		// sku type
		if(!this.f['sku_type'].disabled){
			if(!this.f['sku_type'].value){
				alert('Please select SKU Type.');
				this.f['sku_type'].focus();
				return false;
			}
		}
		
		// check approval order
		if(!this.f['aorder'].value){
			alert('Please select Approval Order.');
			this.f['aorder'].focus();
			return false;
		}
		
		if(this.f['aorder'].value != 4){	// not no approver
			var tr_approval_list = $$('#tbody_approval_list tr.tr-approval');
			if(tr_approval_list.length<=0){
				alert('You must add at least 1 approval');
				$('inp_search_username').focus();
				return false;
			}
		}
		
		return true;
	}
}
</script>
{/literal}

<div class="modal" id="div_approval_flow_popup">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header" id="div_approval_flow_popup_header">
					<h6 class="modal-title">Approval Flow Settings</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
				</div>
			<div class="modal-body tx-center">
				<div id="div_approval_flow_popup_content"></div>
			</div>
		</div>
	</div>
</div>


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Approval Flows</h4>
			<span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div>
			<a href="javascript:void(APPROVAL_FLOW.add())" class="btn btn-sm btn-success"><i class="fas fa-calendar-plus"></i> Add Flow</a>
		</div><br>

		<form id=f_f>
		<b class="form-label">Flow Type</b> <select name=type onchange="reload_table()" class="form-control">
		{section name=i loop=$flow_set}
		<option value="{$flow_set[i].type}">{if $flow_set[i].description}{$flow_set[i].description}{else}{$flow_set[i].type}{/if}</option>
		{/section}
		</select>
		<b>Branch</b> <select name=branch_id onchange="reload_table()" class="form-control">
		<option value=0>All</option>
		{section name=i loop=$branches}
		<option value={$branches[i].id}>{$branches[i].code}</option>
		{/section}
		</select>
		<div>
		<b>Department</b> <select name=sku_category_id onchange="reload_table()" class="form-control">
		<option value=0>All</option>
		{section name=i loop=$dept}
		<option value={$dept[i].id}>{$dept[i].description}</option>
		{/section}
		</select>
		<b>SKU Type</b> <select name=sku_type onchange="reload_table()" class="form-control">
		<option value="ALL">All</option>
		{foreach from=$sku_type_list key=st_code item=st}
			<option value="{$st_code}" {if $form.sku_type eq $st_code}selected {/if}>{$st.description}</option>
		{/foreach}
		</select>

		<input type=button class="btn btn-primary mt-2" name="Refresh" value="Refresh" onclick="reload_table()">
		</div>
		</form>
	</div>
</div>

<div class="card mt-3 mx-3">
	<div class="card-body">
				<div class="alert alert-info">
					<span>A <strike>strikethrough</strike> indicate inactive user.</span>
				</div>
		<script type="text/javascript">APPROVAL_FLOW.initialize();</script>

		{include file=approval_flow_table.tpl}

		<br>

		<div class="ndiv" id="ndiv" style="position:absolute;left:150;top:150;display:none;width:650px;">
		<div class="blur"><div class="shadow"><div class="content">

		<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ndiv'))" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

		<form method=post name=f_b target=_irs onSubmit="return check_b()">
		<div id=bmsg style="padding:10 0 10 0px;"></div>
		<input type=hidden name=a value="a">
		<input type=hidden name=id value="">
		<input type=hidden name=_approvals value="">
		<input type=hidden name=_notify_users value="">

		<div class="table-responsive">
			<table id="tb" class="table table-hover mb-0 text-md-nowrap">
				<tr>
				<td><b>Branch</b></td>
				<td><select name="branch_id" class="form-control select2">
				{section name=i loop=$branches}
				<option value={$branches[i].id}>{$branches[i].code}</option>
				{/section}
				</select></td>
				</tr><tr>
				<td><b>Flow Type</b></td>
				<td><select name=type onchange="sel_type(this.value)" class="form-control select2">
				{section name=i loop=$flow_set}
				<option value={$flow_set[i].type}>{if $flow_set[i].description}{$flow_set[i].description}{else}{$flow_set[i].type}{/if}</option>
				{/section}
				</select><span id=tremark class=small style="color:#00f"></span></td>
				</tr><tr id=dpt>
				<td><b>Department</b></td>
				<td><select name=sku_category_id class="form-control select2">
				{section name=i loop=$dept}
				<option value={$dept[i].id}>{$dept[i].description}</option>
				{/section}
				</select></td>
				</tr><tr id=st>
				<td><b>SKU Type</b></td>
				<td><select name="sku_type" class="form-control select2">
					{foreach from=$sku_type_list key=st_code item=st}
						<option value="{$st_code}">{$st.description}</option>
					{/foreach}
				</select></td>
				</tr><tr>
				<td><b>Approval Order</b></td>
				<td>
				  <span id="span_aorder_sel">
				  <select name="aorder" onChange="aorder_changed();" class="form-control select2">
				  {if !$config.approval_flow_use_all_order}
				    <option value="1">{$aorder.1.description}</option> {* Follow Sequences *}
				    <option value="3">{$aorder.3.description}</option> {* Anyone *}
				  {else}
					  {foreach from=$aorder key=id item=r}
					    <option value="{$id}">{$r.description}</option>
					  {/foreach}
				  {/if}
				  
				  </select>
				  </span>
				  <span id="span_aorder_str">
				  </span>
				</td>
				</tr>
				<tr><td colspan=2><br>
				<table class=small id="tbl_approver">
				<tr>
				<td>
				<b>Approvals</b><br>
				<select name=sel_approvals[] multiple size=10 style="width:160px" class="form-control select2">
				</select>
				</td>
				<td align=center>
				<input type=button value="Up" onclick="mup(f_b.elements['sel_approvals[]'])"><br><br>
				<input type=button value="Dn" onclick="mdn(f_b.elements['sel_approvals[]'])"><br><br>
				<input type=button id="move_approvals" value="<<" onclick="mv(src_approvals, f_b.elements['sel_approvals[]'])"><br><br>
				<input type=button id="move_fyi" value=">>" onclick="mv(f_b.elements['sel_approvals[]'], src_approvals)"><br><br>
				<input type=button value="Reset" onclick="resetlist()">
				</td>
				<td>
				<b>User pool</b><br>
				<select name=src_approvals multiple size=10 style="width:160px" class="form-control select2">
				</select>
				</td>
				<td align=center>
				<input type=button value="<<" onclick="mv(f_b.elements['sel_notify[]'], src_approvals)"><br><br>
				<input type=button value=">>" onclick="mv(src_approvals, f_b.elements['sel_notify[]'])"><br><br>
				</td>
				<td>
				<b>Notify Users</b><br>
				<select name=sel_notify[] multiple size=10 style="width:160px" class="form-control select2">
				</select>
				</td>
				</tr>
				</table>
				</td>
				</tr>
			</table>	
		</div>
		<!-- bottom -->
		<div align=center id=abtn style="display:none;">
		<input type=submit class="btn btn-success" value="Add"> <input type=button  class="btn btn-danger" value="Cancel" onclick="f_b.reset(); hidediv('ndiv');">
		</div>
		<div align=center id=ebtn style="display:none;">
		<input type=submit class="btn btn-primary" value="Update"> <input type=button class="btn btn-success" value="Save As" onclick="save_as()"> <input type=button class="btn btn-indigo" value="Restore" onclick="ed(lastn)"> <input type=button value="Close" onclick="f_b.reset(); hidediv('ndiv');">
		</div>

		</form>
		</div></div></div>

		</div>
	</div>
</div>

<div style="display:none"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>

<script>
init_chg(document.f_b);
new Draggable('ndiv');
</script>

{include file=footer.tpl}
