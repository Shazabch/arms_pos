{*
04/22/2020 11:54 AM Sheila
- Modified layout to compatible with new UI.
*}

{assign var=do_type value=$form.do_type}

{if $form.open_info || $form.deliver_branch || $form.do_branch_id || $form.debtor_id}
	{assign var=have_select_delivery value=1}
{else}
	{assign var=have_select_delivery value=0}
{/if}

{include file=header.tpl}

{literal}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
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
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}

</style>
{/literal}

<script type="text/javascript">
{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';

var active_search_box = 'autocomplete_sku';
var current_branch_code = '{$BRANCH_CODE}';
var consignment_modules = int('{$config.consignment_modules}');
var do_type = '{$smarty.request.do_type|default:$form.do_type}';
var create_type = '{$form.create_type}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var must_check_dept = int('{$config.do_must_check_dept}');
var branch_id_code = [];
var sku_bom_additional_type = int('{$config.sku_bom_additional_type}');

{foreach from=$all_branch item=b}
    branch_id_code['{$b.id}'] = '{$b.code}'
{/foreach}

// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var temp_is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');
var readonly = int('{$readonly}');

{literal}

DO_PREPARATION_MODULE = {
	f: undefined,
	sku_autocomplete: undefined,

	initialize: function(){
		this.f = document.f_a;
		
		//refresh the session each 25 minutes to avoid timeout when user take long time (>30 mins) to select sku.
		new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
		if($('tbl_sku')) this.reset_sku_autocomplete();
		this.init_calendar();
		this.calc_all_items();
		
		if(do_type == "open"){
			if($('div_choose_debtor_to_add')){
				new Draggable('div_choose_debtor_to_add',{ handle: 'div_choose_debtor_to_add_header'});
			}
			if($('div_choose_branch_to_add')){
				new Draggable('div_choose_branch_to_add',{ handle: 'div_choose_branch_to_add_header'});
			}
		}else if(do_type == "credit_sales"){
			if($('div_search_debtor')){
				new Draggable('div_search_debtor',{ handle: 'div_search_debtor_header'});
			}
		}
		
		if(readonly > 0) Form.disable(this.f);
	},
	
	init_calendar: function(){
		Calendar.setup({
			inputField     :    "added1",
			ifFormat       :    "%Y-%m-%d",
			button         :    "t_added1",
			align          :    "Bl",
			singleClick    :    true
		});
	},

	do_save: function(){
		this.f['a'].value='save';
		this.f.target = "";
		if(this.check_a() && this.chk_branch() && this.chk_open_info()){
			document.f_a.submit();
		}
	},

	chk_branch: function(){
		if(do_type != "transfer") return true; // always return true for credit and cash sales
	
		if($('delivery_branches').style.display!='none'){
			if(this.f['branch_id'].value==''){
				alert('Please Select Branch Deliver From');
				return false;
			}else if(this.f['do_branch_id']!=undefined){
				if(this.f['do_branch_id'].value==''){
					alert('Please Select Branch Deliver To');
					return false;
				}else if(this.f['branch_id'].value==this.f['do_branch_id'].value){
					alert('Cannot Delivery to Same Branch');
					return false;
				}
			}else if($('div_multi_branch_selected')){
				var b = this.f['deliver_branch[]'];
				
				var got_gst = -1;
				var got_check = 0;
				for(var i=0; i<b.length; i++){
					if(b[i].checked == true && b[i].value==this.f['branch_id'].value){
						alert('Cannot Delivery to Same Branch');
						return false;
					}
					
					if(b[i].checked && enable_gst && !consignment_modules){
						var got_gst_interbranch = int($(b[i]).readAttribute('got_gst_interbranch'));
						if(got_gst >= 0 && got_gst != got_gst_interbranch){
							alert('Cannot mixed GST and NON-GST branch');
							return false;
						}
						got_gst = got_gst_interbranch;
					}
					
					if(b[i].checked){
						got_check++;
					}
				}
				
				if (got_check <= 0){
					return false;
				}
			}
		}
		return true;
	},
	
	chk_open_info: function(){
		if(this.f['branch_id'].value==''){
			alert('Please Select Branch Deliver From');
			return false;
		}
			
		if($('delivery_open')!=undefined && $('delivery_open').style.display!='none'){
			if (empty($('oi_name'), "You must enter Company Name")){
				return false;
			}
			if (empty($('oi_address'), "You must enter Company Address")){
				return false;
			}
		}
		return true;
	},

	/*do_confirm: function(){
		if(this.check_a() && this.chk_branch() && this.chk_open_info()){
			if (confirm('Finalise DO and submit for approval?')){

				center_div('wait_popup');
				curtain(true,'curtain2');
				Element.show('wait_popup');
				
				var q = $(this.f).serialize();
				
				var params = {
					'a': 'check_tmp_item_exists',
				};
				
				q += '&'+$H(params).toQueryString();
				
				ajax_request('do.simple.php',{
					method: 'post',
					parameters: q,
					onComplete: function(e){
						if (e.responseText.trim() == 'OK') {
							document.f_a.a.value = "confirm";
							document.f_a.target = "";
							document.f_a.submit();
							return;
						}
						else {
							Element.hide('wait_popup');
							curtain(false,'curtain2');
							alert(e.responseText.trim());
							return;
						}
					}
				});
			}	
		}
	},*/

	check_a: function(){
		if (empty(this.f['do_date'], "You must enter DO Date")){
			return false;
		}
		return true;
	},

	ajax_add: function(parms){
		// remove the highlight class
		var td_bom_ref_num_list = $$('#do_items tr.highlight_row');
		for(var i=0; i<td_bom_ref_num_list.length; i++){
			var tmp_tr_ele = td_bom_ref_num_list[i];
			$(tmp_tr_ele).removeClassName('highlight_row');
		}
		
		var q = $(this.f).serialize();
		q += '&'+$H(parms).toQueryString();
		
		ajax_request("do.simple.php",{
			method:'post',
			parameters: q,
			evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function(m) {
				try{			
					eval("var json = "+m.responseText);
				}catch(ex){
					alert(m.responseText);
					return;
				}
				
				for(var tr_key in json){
					if(json[tr_key]['item_existed'] == 1 && json[tr_key]['item_id'] > 0){
						var item_id = json[tr_key]['item_id'];
						var si_code = document.f_a['sku_item_code['+item_id+']'].value;
						if(json[tr_key]['bom_ref_num'] > 0){ // it is duplicated and match with bom package items
							var bom_ref_num = json[tr_key]['bom_ref_num'];
							alert("The following BOM package is existed, please refer to highlighted area.");
							
							var td_bom_ref_num_list = $$('#do_items td.td_bom_ref_num-'+bom_ref_num);
							
							for(var i=0; i<td_bom_ref_num_list.length; i++){
								var tmp_tr_ele = td_bom_ref_num_list[i].parentNode;
								$(tmp_tr_ele).addClassName('highlight_row');
							}
							return;
						}else if(document.f_a['do_branch_id']!=undefined){ // it is single branch
							var extra_qty = prompt("SKU item '"+si_code+"' existed, please enter extra qty:\t\nNOTE: Negative qty is not allowed.");

							if(extra_qty != null && !isNumeric(extra_qty)){
								alert("Invalid qty.");
								return;
							}

							if(document.f_a['inp_item_doc_allow_decimal['+item_id+']'].value != 0){
								extra_qty = float(round(extra_qty, global_qty_decimal_points));
							}else extra_qty = float(round(extra_qty, 0));
							
							if(extra_qty > 0) document.f_a['qty_pcs['+item_id+']'].value = float(document.f_a['qty_pcs['+item_id+']'].value) + float(extra_qty);
							else if(extra_qty < 0) alert("Negative qty is not allowed.");
						}else{ // it is multi branch
							alert("SKU item '"+si_code+"' is duplicated.");
						}
						return;
					}
				
					if(json[tr_key]['error'] != undefined){
						alert(json[tr_key]['error']);
					}

					new Insertion.Bottom($('do_items'),json[tr_key]['rowdata']);
				}
			},
			onComplete: function(m) {
				DO_PREPARATION_MODULE.calc_all_items();
				DO_PREPARATION_MODULE.reset_row();
			},
		});
	},

	add_item: function(){
		if (int(this.f['sku_item_id'].value)==0){
			alert('No item selected');
			$('autocomplete_sku').value = '';
			return false;
		}
		
		if(!this.chk_branch()){
			return false;
		}
		
		var params = {
			'a': 'ajax_add_item'
		};

		this.ajax_add(params);
		active_search_box = 'autocomplete_sku';
		this.clear_autocomplete();
	},

	do_cancel: function(){
		if (check_login()) {
			if (confirm('Cancel this DO?')){
				this.f['a'].value='cancel';
				this.f.target = "";
				this.f.submit();
			}
		}
	},

	reset_sku_autocomplete: function(){
		var param_str = "a=ajax_search_sku&get_last_po=1&type="+getRadioValue(this.f['search_type'])+"&from_do=1&must_check_dept="+must_check_dept;
		if (this.sku_autocomplete != undefined){
			this.sku_autocomplete.options.defaultParams = param_str;
		}
		else{
			this.sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
			afterUpdateElement: function (obj, li) {
				s = li.title.split(",");
				document.f_a.sku_item_id.value =s[0];
				document.f_a.sku_item_code.value = s[1];
			}});
		}
		$('autocomplete_sku').focus();
		this.clear_autocomplete();
	},

	clear_autocomplete: function(){
		this.f['sku_item_id'].value = '';
		this.f['sku_item_code'].value = '';
		$('autocomplete_sku').value = '';
		$('autocomplete_sku').focus();
		if($('inp_autocomplete_qty'))   $('inp_autocomplete_qty').value = '';
	},

	delete_item: function(id){
		var confirm_str = 'Remove this SKU from DO?';
		var bom_ref_num = '';
		
		if(sku_bom_additional_type){
			if(document.f_a['bom_ref_num['+id+']'] && document.f_a['bom_ref_num['+id+']'].value.trim() != ''){
				bom_ref_num = document.f_a['bom_ref_num['+id+']'].value.trim();
				
				confirm_str += '\nThis SKU is BOM Package SKU, all related SKU will be delete together';
			}
		}
		
		if (!confirm(confirm_str)) return;
		
		var delete_id_list = [];
		 if(sku_bom_additional_type && bom_ref_num){
			$$('#do_items td.td_bom_ref_num-'+bom_ref_num).each(function(td){
				var tmp_id = td.title;
				Element.remove('titem'+tmp_id);
			});
		}else{
			delete_id_list.push(id);
			Element.remove('titem'+id);
		}

		this.calc_all_items();
		this.reset_row();
	},

	reset_row: function(){
		var e = $('do_items').getElementsByClassName('no');
		for(var i=0;i<e.length;i++)	{
			var temp_1 =new RegExp('^no_');
			if (temp_1.test(e[i].id)){
				td_1=(i+1)+'.';
				e[i].innerHTML=td_1;
				e[i].id='no_'+(i+1);
			}
		}

		$(active_search_box).value='';
		$(active_search_box).focus();
	},

	calc_all_items: function(){
		var row_ctn=0, total_ctn=0;
		var row_pcs=0, total_pcs=0;
		var total_qty=0;
		var total_rcv = 0;
		
		// get branch id list
		var do_branch_id_list = [];
		
		if(do_type=='transfer'){
			do_branch_id_list = this.get_do_branch_list();
		}
		
		if ($('do_items')==undefined) return;
		// get all item row
		var all_si_list = $$('#do_items input.sku_items_list');
		var item_len = all_si_list.length;
		
		// loop for each row
		for(var i=0; i<all_si_list.length; i++){
			var item_id = $(all_si_list[i]).title.split(',')[1];

			// get ctn
			var row_ctn = 0;
			$$('#titem'+item_id+' input.inp_qty_ctn').each(function(inp){
				row_ctn += float(inp.value);
			});
			
			// get pcs
			var row_pcs = 0;
			$$('#titem'+item_id+' input.inp_qty_pcs').each(function(inp){
				row_pcs += float(inp.value);
			});
			// get uom fraction
			item_fraction = float(this.f["uom_fraction["+item_id+"]"].value);
			var row_qty = (row_ctn * item_fraction) + row_pcs;
			total_qty += row_qty;
			//console.log("row_qty = "+row_qty);
			$('row_qty'+item_id).update(float(row_qty));
			
			if(this.f["rcv_pcs["+item_id+"]"]){
				var row_rcv = float(this.f["rcv_pcs["+item_id+"]"].value);
				total_rcv += row_rcv;
			}

			total_ctn += row_ctn;
			total_pcs += row_pcs;
		}
		
		$('t_ctn').update(float(round(total_ctn, global_qty_decimal_points)));
		$('t_pcs').update(float(round(total_pcs, global_qty_decimal_points)));
		$('total_ctn').value=float(round(total_ctn, global_qty_decimal_points));
		$('total_pcs').value=float(round(total_pcs, global_qty_decimal_points));
	},

	select_type: function(val){	
		if(val=='2'){
			$('delivery_open').style.display='';
			$('delivery_branches').style.display='none';
		}else{
			$('oi_address').value='';
			$('oi_name').value='';
			$('delivery_branches').style.display='';
			$('delivery_open').style.display='none';
		}
		
		this.active_btn();
	},

	active_btn: function(){
		if(!this.chk_branch()){
			$('srefresh').style.display='none';
			$('refresh_btn').disabled=true;
			$('refresh_btn').hide();
			return false;
		}

		if ($('new_sheets') != undefined){
			$('new_sheets').style.display='none';
			$('tbl_sku').style.display='none';
			$('submitbtn').style.display='none';
		}
		
		// enable the refresh button
		$('srefresh').style.display='';
		$('refresh_btn').disabled=false;
		$('refresh_btn').show();
	},

	chk_open_info: function(){
		if(this.f['branch_id'].value==''){
			alert('Please Select Branch Deliver From');
			return false;
		}
			
		if($('delivery_open')!=undefined && $('delivery_open').style.display!='none'){
			if (empty($('oi_name'), "You must enter Company Name")){
				return false;
			}
			if (empty($('oi_address'), "You must enter Company Address")){
				return false;
			}
		}
		return true;
	},

	refresh_tables: function(){
		//get the latest is_under_gst value
		if (do_type == "transfer"){
			if(branch_gst_register_no != '') {
				var isvalid = this.check_is_under_gst();
			
				if (isvalid) {
					window.is_under_gst = 1;
					this.f['is_under_gst'].value = 1;
				}else{
					window.is_under_gst = 0;
					this.f['is_under_gst'].value = 0;
				}
			}
			
			this.f['a'].value = "refresh";
			this.f.target = "";
			
			if(this.chk_branch()){
				this.f.submit();
			}
		}else if(do_type == "credit_sales"){
			if(check_login()) {
				needCheckExit=false;
				document.f_a.a.value = "refresh";
				document.f_a.target = "";
				document.f_a.submit();
			}
		}else{
			needCheckExit = false;
			document.f_a.a.value = "refresh";
			document.f_a.target = "";
			if(this.chk_open_info()){
				document.f_a.submit();	
			}
		}
	},

	change_do_branch_id: function(ele){
		var bid = ele.value;
		var from_bid = this.f['branch_id'].value;
		
		if(!this.chk_same_branch(ele)){
			return false;
		}

		if (branch_gst_register_no != '') {
			var isvalid = this.check_is_under_gst();
		
			if (isvalid) {
				window.is_under_gst = 1;
				this.f['is_under_gst'].value = 1;
			}else{
				window.is_under_gst = 0;
				this.f['is_under_gst'].value = 0;
			}
		}
		
		if($('new_sheets')==undefined || $('new_sheets').style.display=='none'){
			if(ele.value!='') this.active_btn();
			return;
		}else{
			if(ele.value==''){
				$('srefresh').style.display='';
				$('refresh_btn').disabled=false;
				$('refresh_btn').show();
			
				if ($('new_sheets') != undefined){
					$('new_sheets').style.display='none';
					$('tbl_sku').style.display='none';
					$('submitbtn').style.display='none';
				}
				return;
			}
		}
		
		// continue to check gst status if the form still not reload
		if(enable_gst && !consignment_modules){
			this.check_gst_date_changed();
		}
	},

	chk_same_branch: function(ele){
		var b1 = this.f['branch_id'].value;
		var b2 = this.f['do_branch_id'].value;

		if(b1!=''||b2!=''){
			if(b1==b2){
				alert('Cannot Deliver to Same Branch');
				ele.selectedIndex = 0;
				return false;
			}
		}
		
		return true;
	},

	// function when do date changed
	on_do_date_changed: function(){
		// get the object
		var inp = this.f['do_date'];
		// check max/min limit
		upper_lower_limit(inp);
		
		if (branch_gst_register_no != '') {
			var isvalid = this.check_is_under_gst();
		
			if (isvalid) {
				window.is_under_gst = 1;
				this.f['is_under_gst'].value = 1;
			}else{
				window.is_under_gst = 0;
				this.f['is_under_gst'].value = 0;
			}
		}
		
		// check gst
		if(enable_gst) this.check_gst_date_changed();
	},

	// function when do date is changed
	check_gst_date_changed: function(){
		var allow_gst = false;
		
		// gst is not enable
		if(!enable_gst || consignment_modules)	return;
		
		// gst is active and branch got register
		if(gst_is_active && branch_gst_register_no){
			if(skip_gst_validate){
				allow_gst = true;
			}else{		
				// got gst start date
				if(global_gst_start_date && branch_gst_start_date){
					// get Date
					var do_date = this.f["do_date"].value.trim();
					
					if(do_date){
						// check Date
						if(strtotime(do_date) > strtotime(global_gst_start_date) && strtotime(do_date) > strtotime(branch_gst_start_date)){
							// check gst interbranch
							if(this.f["do_branch_id"] != undefined){
								// single
								var opt = this.f["do_branch_id"].options[this.f["do_branch_id"].selectedIndex];
								var got_gst_interbranch = int($(opt).readAttribute('got_gst_interbranch'));
								if(got_gst_interbranch){
									allow_gst = true;
								}
							}else{
								// multi 
								for(var i=0; i < this.f["deliver_branch[]"].length; i++){
									if(this.f["deliver_branch[]"][i].checked){
										var got_gst_interbranch = int($(this.f["deliver_branch[]"][i]).readAttribute('got_gst_interbranch'));
										if(got_gst_interbranch){
											allow_gst = true;
										}else{
											allow_gst = false;
											break;
										}
									}
								}
							}
							allow_gst = true;
						}
					}
				}
			}
		}

		if(allow_gst){
			// date have gst
			if(!is_under_gst) this.active_btn();
		}else{
			// date no gst
			if(is_under_gst) this.active_btn();
		}
	},

	do_branch_changed: function(){
		if (branch_gst_register_no != '') {
			var isvalid = this.check_is_under_gst();
		
			if (isvalid) {
				window.is_under_gst = 1;
				this.f['is_under_gst'].value = 1;
			}else{
				window.is_under_gst = 0;
				this.f['is_under_gst'].value = 0;
			}
		}
		
		this.active_btn();
	},

	check_is_under_gst: function(){
		if (enable_gst && !consignment_modules) {
			if(global_gst_start_date && branch_gst_start_date){
				// get Date
				var do_date = this.f["do_date"].value.trim();
				
				if(do_date){
					// check Date
					if(strtotime(do_date) > strtotime(global_gst_start_date) && strtotime(do_date) > strtotime(branch_gst_start_date)){
						// check gst interbranch
						if(this.f["do_branch_id"] != undefined){
							var opt = this.f["do_branch_id"].options[this.f["do_branch_id"].selectedIndex];
							var got_gst_interbranch = int($(opt).readAttribute('got_gst_interbranch'));
							if(got_gst_interbranch){
								return 1;
							}else{
								return 0;
							}
						}else{
							for(var i=0; i < this.f["deliver_branch[]"].length; i++){
								if(this.f["deliver_branch[]"][i].checked){
									var got_gst_interbranch = int($(this.f["deliver_branch[]"][i]).readAttribute('got_gst_interbranch'));
									if(got_gst_interbranch){
										return 1;
									}else{
										return 0;
										break;
									}
								}
							}
						}
					}else{
						return 0;
					}
				}
			}else return;
		}else return;
	},

	get_do_branch_list: function(){
		var branch_id_list = [];
		
		if(this.f['deliver_branch[]']){	// got multiple branch checkbox
			inp_list = this.f['deliver_branch[]'];
			for(var i=0; i<inp_list.length; i++){
				if(inp_list[i].checked)	branch_id_list.push(inp_list[i].value);
			}
		}else if(this.f['do_branch_id']){	// only single branch
			if(this.f['do_branch_id'].value>0){
				branch_id_list.push(this.f['do_branch_id'].value);
			}
		}
		return branch_id_list;
	},
	
	show_search_debtor: function(){
		$$('#tbl_debtor_list tr.db_row').each(function(ele){
			$(ele).show();
		});
		
		curtain(true);
		center_div($('div_search_debtor').show());
		document.f_search_debtor['debtor_desc'].focus();
	},
	
	debtor_changed: function(){
		var sel = this.f['debtor_id'];
		this.active_btn(sel);
		
		// check this debtor got mprice type or not
		if(this.f['debtor_id'].value){
			var opt = document.f_a['debtor_id'].options[document.f_a['debtor_id'].selectedIndex];
			
			// check whether this debtor is special exemption
			if(enable_gst && is_under_gst){
				var special_exemption = int($(opt).readAttribute('special_exemption'));
				if(special_exemption){
					document.f_a['is_special_exemption'].checked = true;
					$('tr_special_excemption_rcr').show();
				}else{
					document.f_a['is_special_exemption'].checked = false;
					$('tr_special_excemption_rcr').hide();
				}
			}
		}
	},
	
	choose_debtor_to_add: function(){
		curtain(true);
		center_div($('div_choose_debtor_to_add').show());
		
		//if($('tbl_debtor_list'))	fxheaderInit('tbl_debtor_list',300);
	},
	
	choose_this_debtor: function(ele){
	
		if(do_type == "open"){
			var db_code = $(ele).getElementsBySelector('.db_code')[0].innerHTML;
			var db_desc = $(ele).getElementsBySelector('.db_desc')[0];
			var db_address = $(ele).getElementsBySelector('.db_address')[0];
			var db_contact = $(ele).getElementsBySelector('.db_contact')[0].innerHTML;
			var db_email = $(ele).getElementsBySelector('.db_email')[0].innerHTML;
			
			$('oi_name').value = (db_desc.textContent || db_desc.innerText);
			$('oi_address').value = (db_address.textContent || db_address.innerText);
			$('oi_contact').value = db_contact;
			$('oi_email').value = db_email;
			
			// check whether this debtor is special exemption
			if(enable_gst && is_under_gst){
				var special_exemption = int($(ele).readAttribute('special_exemption'));
				if(special_exemption){
					document.f_a['is_special_exemption'].checked = true;
					$('tr_special_excemption_rcr').show();
				}else{
					document.f_a['is_special_exemption'].checked = false;
					$('tr_special_excemption_rcr').hide();
				}
			}
		}else{
			var debtor_id = $(ele).getElementsBySelector('.db_id')[0].innerHTML;
			var address = $(ele).getElementsBySelector('.db_address')[0];
			var delivery_address = $(ele).getElementsBySelector('.db_delivery_address')[0];
		
			this.f['debtor_id'].value = debtor_id;
			this.debtor_changed();
		}
		
		default_curtain_clicked();
	},
	
	choose_branch_to_add: function(type){
		curtain(true);
		center_div($('div_choose_branch_to_add').show());
	},
	
	choose_this_branch: function(ele){
		var br_code = $(ele).getElementsBySelector('.br_code')[0].innerHTML;
		var br_desc = $(ele).getElementsBySelector('.br_desc')[0];
		var br_address = $(ele).getElementsBySelector('.br_address')[0];
		var br_delivery_address = $(ele).getElementsBySelector('.br_delivery_address')[0];
		var br_contact = $(ele).getElementsBySelector('.br_contact')[0].innerHTML;
		var br_email = $(ele).getElementsBySelector('.br_email')[0].innerHTML;
		
		if (br_desc) br_code = br_code + ' - ' + (br_desc.textContent || br_desc.innerText);

		$('oi_name').value = br_code;
		$('oi_address').value = (br_address.textContent || br_address.innerText);
		$('oi_contact').value = br_contact;
		$('oi_email').value = br_email;
		
		// check whether this debtor is special exemption
		if(enable_gst && is_under_gst){
			document.f_a['is_special_exemption'].checked = false;
			$('tr_special_excemption_rcr').hide();
		}
		
		default_curtain_clicked();
	},
	
	toggle_special_exemption: function(){
		var is_special_exemption = document.f_a["is_special_exemption"];
		if(is_special_exemption.checked == true){
			$('tr_special_excemption_rcr').show();
		}else{
			$('tr_special_excemption_rcr').hide();
		}
	},
	
	item_uom_changed: function(item_id){
		var sel = $('sel_uom'+item_id);
		var a = sel.value.split(",");
		var old_fraction = float($('uom_fraction'+item_id).value);
		var old_cost;
		var new_cost;
		
		// update new uom
		$('uom_id'+item_id).value=a[0];
		$('uom_fraction'+item_id).value=a[1];
		
		// change ctn/pcs
		DO_MODULE.check_row_ctn_pcs_input(item_id);
		
		// recalulate all
		this.calc_all_items();
	},
}

// need to put outside since this being called from do.script.tpl
function row_recalc(item_id, branch){
	if(!item_id) return;
	
	DO_PREPARATION_MODULE.calc_all_items();
}

function add_grn_barcode_item(value){
	value = trim(value);
	if (value=='')
	{
		$('grn_barcode').select();
		$('grn_barcode').focus();
		return;
	}
	$('grn_barcode').value='';
	
	var params = {
		'a': 'ajax_add_grn_barcode_item',
		grn_barcode: value
	};
	
	if(!DO_PREPARATION_MODULE.chk_branch()){
		return false;
	}
	
	DO_PREPARATION_MODULE.ajax_add(params);
	active_search_box = 'grn_barcode';
}

function curtain_clicked(){
    if( $('div_choose_branch_to_add')) $('div_choose_branch_to_add').hide();
    if( $('div_choose_debtor_to_add')) $('div_choose_debtor_to_add').hide();
    if( $('div_search_debtor')) $('div_search_debtor').hide();
}

{/literal}
</script>
{include file='do.script.tpl'}

<div id="wait_popup" style="display:none;position:absolute;z-index:10000;background:#fff;border:1px solid #000;padding:5px;width:200;height:100">
	<p align="center">
	Please wait..
	<br /><br />
	<img src="ui/clock.gif" border="0" />
	</p>
</div>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<div class="content-title mb-0 my-auto ml-4 text-primary">
				<h4>{$do_type_label} DO {if $form.do_no}(DO/{$form.do_no}){else}{if $form.id}(ID#{$form.id}){/if}{/if}</h4>
				<h5>Status:
				{if $form.status == 5}
					Cancelled
				{elseif $form.status == 4}
					Terminated
				{else}
					Draft Delivery Order
				{/if}
				</h5>

			</div><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


{include file=approval_history.tpl}

{if $form.do_type eq "open"}
	<div id="div_choose_debtor_to_add" style="display:none;position:absolute;z-index:10000;width:750px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
		<div id="div_choose_debtor_to_add_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
			<span style="float:left;">Available Debtor Details</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_choose_debtor_to_add_content" style="padding:2px;">
			<form name="f_search_debtor" onSubmit="filter_debtor_desc();return false;">
				<b>Filter by Description:</b>
				<input type="text" size="30" name="debtor_desc" />
				<input type="submit" value="Refresh" />
			</form>
			<form name="f_choose_debtor" onSubmit="return false;">
			<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
			<table id="tbl_debtor_list" width="100%">
				<tr style="background:#ffc;">
					<th width="30">&nbsp;</th>
					<th width="80">Code</th>
					<th>Description</th>
					<th width="30%">Address (Bill)</th>
					<th width="30%">Address (Deliver)</th>
				</tr>
				<tbody style="background:#fff;">
				{foreach from=$debtor key=id item=r name=f}
					<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable db_row" onClick="DO_PREPARATION_MODULE.choose_this_debtor(this);" special_exemption="{$r.special_exemption}">
						<td>{$smarty.foreach.f.iteration}.</td>
						<td>{$r.code}
							<span class="db_code" style="display:none;">{$r.code}</span>
						</td>
						<td>{$r.description|truncate:30:'...'}
							<span class="db_desc" style="display:none;">{$r.description}</span>
						</td>
						<td>{$r.address|truncate:30:'...'}
							<span class="db_address" style="display:none;">{$r.address}</span>
							<span class="db_contact" style="display:none;">{$r.phone_1|default:$r.phone_2}</span>
							<span class="db_email" style="display:none;">{$r.contact_email}</span>
						</td>
						<td>{$r.delivery_address|truncate:30:'...'}
							<span class="db_delivery_address" style="display:none;">{$r.delivery_address}</span>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			</div>
			<p align="center">
				<input type="button" class="btn btn-danger" value="Close" name="close" onClick="default_curtain_clicked();" />
			</p>
			</form>
		</div>
	</div>

	<div id="div_choose_branch_to_add" style="display:none;position:absolute;z-index:10000;width:750px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
		<div id="div_choose_branch_to_add_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
			<span style="float:left;">Available Branch Details</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_choose_branch_to_add_content" style="padding:2px;">
			<form name="f_search_branch" onSubmit="filter_branch_desc();return false;">
				<b>Filter by Description:</b>
				<input type="text" size="30" name="branch_desc" />
				<input type="submit" value="Refresh" />
			</form>
			<form name="f_choose_branch" onSubmit="return false;">
			<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
			<table id="tbl_branch_list" width="100%">
				<tr style="background:#ffc;">
					<th width="30">&nbsp;</th>
					<th width="80">Code</th>
					<th>Description</th>
					<th width="30%">Address (Bill)</th>
					<th width="30%">Address (Deliver)</th>
				</tr>
				<tbody style="background:#fff;">
				{foreach from=$branch key=id item=r name=f}
					<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable br_row" onClick="DO_PREPARATION_MODULE.choose_this_branch(this);">
						<td>{$smarty.foreach.f.iteration}.</td>
						<td class="br_code">{$r.code}</td>
						<td>{$r.description|truncate:30:'...'}
							<span class="br_desc" style="display:none;">{$r.description}</span>
						</td>
						<td>{$r.address|truncate:30:'...'}
							<span class="br_address" style="display:none;">{$r.address}</span>
							<span class="br_contact" style="display:none;">{$r.phone_1|default:$r.phone_2}</span>
							<span class="br_email" style="display:none;">{$r.contact_email}</span>
						</td>
						<td>{$r.deliver_to|truncate:30:'...'}
							<span class="br_delivery_address" style="display:none;">{$r.deliver_to}</span>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			</div>
			<p align="center">
				<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
			</p>
			</form>
		</div>
	</div>
{elseif $form.do_type eq "credit_sales"}
	<div id="div_search_debtor" style="display:none;position:absolute;z-index:10000;width:750px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;">
		<div id="div_search_debtor_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
			<span style="float:left;">Available Debtor Details</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_search_debtor_content" style="padding:2px;">
			<form name="f_search_debtor" onSubmit="filter_debtor_desc();return false;">
				<b>Filter by Description:</b>
				<input type="text" size="30" name="debtor_desc" />
				<input type="submit" value="Refresh" />
			</form>
			<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
			<table width="100%" id="tbl_debtor_list">
				<tr style="background:#ffc;" id="tr_header_debtor_list">
					<th width="30">&nbsp;</th>
					<th width="80">Code</th>
					<th>Description</th>
					<th>Address (Bill)</th>
					<th>Address (Deliver)</th>
				</tr>
				<tbody style="background:#fff;">
				{foreach from=$debtor key=id item=r name=f}
					<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable db_row" onClick="DO_PREPARATION_MODULE.choose_this_debtor(this);">
						<td>{$smarty.foreach.f.iteration}.
							<span class="db_id" style="display:none;">{$r.id}</span>
						</td>
						<td>{$r.code}
							 <span class="db_code" style="display:none;">{$r.code}</span>
						</td>
						<td>{$r.description}
							<span class="db_desc" style="display:none;">{$r.description}</span>
						</td>
						<td width="30%">{$r.address|truncate:30:'...'}
							<span class="db_address" style="display:none;">{$r.address}</span>
						</td>
						<td width="30%">{$r.delivery_address|truncate:30:'...'}
							<span class="db_delivery_address" style="display:none;">{$r.delivery_address}</span>
						</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			</div>
			<p align="center">
				<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
			</p>
		</div>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" ENCTYPE="multipart/form-data">
			<input type="hidden" name="a" value="save">
			<input type="hidden" name="old_branch_id" value="{$form.old_branch_id}">
			<input type="hidden" name="id" value="{$form.id}" id="inp_do_id">
			<input type="hidden" name="do_no" value="{$form.do_no}">
			<input type="hidden" name="branch_changed" value="">
			<input type="hidden" name="do_type" value="{$form.do_type}">
			<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}" />
			<input type="hidden" name="user" value="{$form.user}" />
			<div class="stdframe" style="background:#fff">
			<h4>General Information</h4>
			
			{if $errm.top}
			<div id="err"><div class="errmsg"><ul>
			{foreach from=$errm.top item=e}
			<div class="alert alert-danger"><li> {$e}</li></div>
			{/foreach}
			</ul></div></div>
			{/if}
			<div id="errpr"></div>
			
			<table border="0" cellspacing="0" cellpadding="4">
				{if $form.added}
					<tr>
						<th width="160" align="left"><b class="form-label">Added Date</b></th>
						<td>{$form.added}</td>
					</tr>
				{/if}
				<tr>
					<th width="160" align="left"><b class="form-label">DO Date</b> </th>
					<td>
						<div class="form-inline">
							<input class="form-control" name="do_date" id="added1"  onchange="DO_PREPARATION_MODULE.on_do_date_changed();"  value="{$form.do_date|default:$smarty.now|date_format:"%Y-%m-%d"}">
						{*if $form.status<1 || $form.status eq '2'*}
						{if !$readonly && !$form.approval_screen}
						&nbsp;&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
						{/if}
						</div>
					</td>
				</tr>
			
				{if $config.do_approval_by_department}
					<tr>
						<th width="160" align="left">
						<b class="form-label">Department<span class="text-danger"> *</span></b></th>
						<td>
							<select class="form-control" name="dept_id" id="dept_id">
								<option value=0>-- Please Select --</option>
								{foreach from=$departments item=dept}
									<option value={$dept.id} {if $form.dept_id eq $dept.id}selected{/if}>{$dept.description}</option>
								{/foreach}
							</select> 
						</td>
					</tr>
				{/if}
				{if $form.id}
					<tr>
						<td align="left"><b class="form-control">Owner</b></td>
						<td style="color:blue;">{$form.user}</td>
					</tr>
				{/if}
				<tr>
					<td valign="top"><b class="form-label">Remarks</b></td>
					<td colspan="3">
						<textarea class="form-control" rows="2" cols="68" name="remark" onchange="uc(this);">{$form.remark}</textarea>
					</td>
				</tr>
				<tr>
					<td valign="top"><b class="form-label">Deliver From</b></td>
					<td>
						{assign var=can_change_from value=0}
						{if $config.consignment_modules && is_new_id($form.id)}{assign var=can_change_from value=1}{/if}
						<select class="form-control" id="sel_branch_id" {if !$can_change_from}disabled {else}name="branch_id"{/if}>
							<option value="">-- Please Select --</option>
							{foreach from=$branch name=i item=b}
								{assign var=bid value=$b.id}
								<option value="{$bid}" {if ($form.branch_id>0 and $form.branch_id eq $bid) or (!$form.branch_id and $b.code eq $BRANCH_CODE)}selected {/if}>{$b.code} - {$b.description}</option>
							{/foreach}
						</select>
						{if !$can_change_from}
							<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
						{/if}
					</td>
				</tr>
				{if $form.do_type eq "transfer"}
					<tr style="display:none;">
						<td valign="top"><b class="form-label">Deliver To</b></td>
						<td>
							<input type="radio" name="create_type" value="1" checked onclick="select_type(this.value);" >{*Branches*}
						</td>
					</tr>
					<tr id="delivery_branches">
						<td valign="top"><b>Deliver To</b></td>
						{if (($form.deliver_branch || !$form.do_branch_id) && !$config.consignment_modules) || ($form.deliver_branch && $config.consignment_modules)}
							<td>
								<div id="div_multi_branch_selected">
									You may select multiple branches to deliver <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span><br>
									
									{if count($branch)<=10}
										<table class="small">
											{foreach from=$branch name=i item=b}
												{assign var=bid value=$b.id}
												<tr>
													<td valign="top">
														<input class="branch" onchange="DO_PREPARATION_MODULE.do_branch_changed();" type="checkbox" name="deliver_branch[]" value="{$b.id}" 
														{if is_array($form.deliver_branch) and in_array($b.id,$form.deliver_branch) or (is_array($po_multi_deliver_to) and in_array($bid,$po_multi_deliver_to))}checked {/if} 
														id="dt_{$bid}" {if $form.id<$time_value}onclick="return false;"{/if}
														got_gst_interbranch="{if $gst_interbranch.$bid}1{/if}">
														&nbsp;<label for="dt_{$bid}">{$b.code} {if $config.enable_gst && !$config.consignment_modules && $gst_interbranch.$bid}<sup class="small" style="color:red;">(GST)</sup>{/if}</label>
														&nbsp;&nbsp;
													</td>
												</tr>
											{/foreach}
										</table>
									{else}
										<div style="width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
											<table>
												{foreach from=$branch name=i item=b}
													{assign var=bid value=$b.id}
													{if $bid ne $form.branch_id}
														<tr>
															<td>
																<input class="branch" onchange="DO_PREPARATION_MODULE.do_branch_changed();" type="checkbox" name="deliver_branch[]" value="{$b.id}" {if (is_array($form.deliver_branch) and in_array($b.id,$form.deliver_branch)) or (is_array($po_multi_deliver_to) and in_array($bid,$po_multi_deliver_to))}checked {/if} id="dt_{$bid}" {if $form.id<$time_value}onclick="return false;"{/if}
																got_gst_interbranch="{if $gst_interbranch.$bid}1{/if}">&nbsp;<label for="dt_{$bid}">{$b.code} - {$b.description} {if $config.enable_gst && !$config.consignment_modules && $gst_interbranch.$bid}<sup class="small" style="color:red;">(GST)</sup>{/if}</label>
															</td>
														</tr>
													{/if}
												{/foreach}
											</table>
										</div>
									{/if}
								</div>
							</td>
						{else}
							<td>
								<div id="div_single_branch_selected">
									<select class="form-control" name="do_branch_id" id="do_branch_id" onChange="DO_PREPARATION_MODULE.change_do_branch_id(this);">
										<option value="">-- Please Select --</option>
										{foreach from=$branch name=i item=b}
											{assign var=bid value=$b.id}
											<option value="{$bid}" {if ($form.do_branch_id eq $bid)}selected {/if} got_gst_interbranch="{if $gst_interbranch.$bid}1{/if}">{$b.code} - {$b.description}</option>
										{/foreach}
									</select>
									<span id="span_branch_change_loading"></span>
								</div>
							</td>
						{/if}
					</tr>
				{elseif $form.do_type eq "credit_sales"}
					<tr>
						<td><b class="form-label">Debtor (Bill)</b></td>
						<td>
							<select class="form-control" name="debtor_id" onchange="DO_PREPARATION_MODULE.debtor_changed(this);">
								<option value="">-- Please Select --</option>
								{foreach from=$debtor item=r}
									<option value="{$r.id}" {if $form.debtor_id eq $r.id}selected {/if} debtor_mprice_type="{$r.debtor_mprice_type}" db="{$r.code|escape:html} - {$r.description|escape:html}" 
									db_address="{$r.address|escape:html}" db_delivery_address="{$r.delivery_address|escape:html}" db_contact="{$r.phone_1|default:$r.phone_2|escape:html}" db_email="{$r.contact_email|escape:html}"
									special_exemption="{$r.special_exemption}">{$r.code} - {$r.description}</option>
								{/foreach}
							</select> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
							{if !$form.approved and !$form.checkout and $form.status eq 0}
								<img src="/ui/icons/magnifier.png" align="absmiddle" title="Search by Debtor description" class="clickable" onClick="DO_PREPARATION_MODULE.show_search_debtor();" />
							{/if}
							<span id="span_debtor_change_loading"></span>
						</td>
					</tr>
				{else}
					<tr id="delivery_open">
						<td valign="top"><b class="form-label">Deliver To</b></td>
						<td colspan="4">
						<table>
							<tr>
								<td width=100><b class="form-label">Company Name</b></td>
								<td>
									<input class="form-control" id="oi_name" name="open_info[name]" value="{$form.open_info.name}" size=51 onchange="uc(this);"> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
									{if $config.do_allow_credit_sales and !$form.approved and !$form.checkout and $form.status eq 0}
										<input class="btn btn-primary" type="button" value="Choose Debtor" onclick="DO_PREPARATION_MODULE.choose_debtor_to_add();" />
									{/if}
									<input class="btn btn-primary" type="button" value="Choose Branch" onclick="DO_PREPARATION_MODULE.choose_branch_to_add();" />
								</td>
							</tr>
						
							<tr>
								<td valign="top" width="100"><b class="form-label">Address (Bill)</b></td>
								<td>
									<textarea class="form-control" id="oi_address" name="open_info[address]" rows="5" cols="38" onchange="uc(this);">{$form.open_info.address}</textarea>
									<input class="form-control" type="hidden" name="open_info[contact_no]" id="oi_contact" value="{$form.open_info.contact_no}" />
									<input class="form-control" type="hidden" name="open_info[email]" id="oi_email" value="{$form.open_info.email}" />
								</td>
							</tr>
						</table>
						</td>
					</tr>
				{/if}
				
				{if $form.do_type ne "transfer" && $config.enable_gst && $form.is_under_gst}
					<tr>
						<td>
							<b class="form-label">GST Special Exemption [<a href="javascript:void(alert('- This will automatically apply to newly added item, the items already in the document will not be change.\n
								- This setting cannot be change manually, it follow the debtor special exemption setting.'));">?</a>]</b>
						</td>
						<td>
							<input type="checkbox" name="is_special_exemption" value="1" {if $form.is_special_exemption}checked{/if} {if $form.do_type eq "credit_sales"}onClick="return false;"}{else}onclick="DO_PREPARATION_MODULE.toggle_special_exemption();"{/if} />
						</td>
					</tr>
					
					<tr id="tr_special_excemption_rcr" {if !$form.is_special_exemption}style="display:none;"{/if}>
						<td valign="top">
							<b >GST Special Exemption Relief Clause Remark</b>
						</td>
						<td>
							<textarea name="special_exemption_rcr" cols="50" rows="4" class="required"  title="Special Exemption Relief Clause Remark">{$form.special_exemption_rcr}</textarea>
						</td>
					</tr>
				{/if}
			</table>
			
			<div id="srefresh" style="{if $form.do_type ne 'open' || $form.open_info}display:none;{/if} padding-top:10px; padding-left:130px; ">
			<input class="btn btn-primary" id="refresh_btn" type="button" onclick="void(DO_PREPARATION_MODULE.refresh_tables())" value="click here to continue">
			</div>
			</td>
			</tr>
			</table>
			</div>
			
			<br>
			
			{if $errm_link_code}
			<div id="err">
			<div class="errmsg">
			<ul>
			<li>
			<div class="alert alert-danger">
				The following Link Code are INVALID :
			</li>
			</div>
			</ul>
			</div>
			{foreach from=$errm_link_code item=e}
			<b>{$e}</b><br>
			{/foreach}
			</div>
			{/if}
			
			<br>
			
			{if $errm_sku_item_code}
			<div id="err">
			<div class="errmsg">
			<ul>
			<div class="alert alert-danger">
				<li>The following ARMS Code are INVALID :</li>
			</div>
			</ul>
			</div>
			{foreach from=$errm_sku_item_code item=e}
			<b>{$e}</b><br>
			{/foreach}
			</div>
			{/if}
			
			{if $errm.item}
			<div id="err"><div class="errmsg"><ul>
			{foreach from=$errm.item item=e}
			<div class="alert alert-danger">
				<li> {$e}</li>
			</div>
			{/foreach}
			</ul></div></div>
			{/if}
			
			<br>
</div>
</div>			
			{if $have_select_delivery}
				<div id="new_sheets">
					{include file=do.simple.new.table.tpl}
				</div>
				{if (!$form.status || $form.status=='2') && $form.create_type ne '3' && !$form.approved && !$readonly}	
					<div class="card mx-3">
						<div class="card-body">
							<div id="tbl_sku" width="100%">
								{include file='scan_barcode_autocomplete.tpl' no_need_table=1 need_hr_bottom=1}
								<tr class="normal">
									<td width="90px" valign="top" nowrap>
									<input class="" name="sku_item_id" size="3" type="hidden">
									<input class="form-control" name="sku_item_code" size="13" type="hidden">
									
									<b class="form-label">Search SKU </b>
									</td>
									<td nowrap>
										<div class="form-inline">
											<input class="form-control" id="autocomplete_sku" name="sku" size="25" onclick="this.select()">
									
											&nbsp;<input type="button" class="btn btn-primary" value="Add" onclick="DO_PREPARATION_MODULE.add_item();" >
										
										</div>
									<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
									<br>
									<input onchange="DO_PREPARATION_MODULE.reset_sku_autocomplete()" type="radio" name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
									<input onchange="DO_PREPARATION_MODULE.reset_sku_autocomplete()" type="radio" name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
									<input onchange="DO_PREPARATION_MODULE.reset_sku_autocomplete()" type="radio" name="search_type" value="3"> ARMS Code
									<input onchange="DO_PREPARATION_MODULE.reset_sku_autocomplete()" type="radio" name="search_type" value="4"> Description
									</td>
								</tr>
							</div>
						</div>
					</div>
				{/if}
			{/if}
			</form>
			
	</div>
</div>
<p id="submitbtn" align="center">
	{if !$readonly}
		{if (!$form.status || $form.status==2) && $have_select_delivery}
			<input class="btn btn-success mt-3" name="bsubmit" type="button" value="Save & Close" onclick="DO_PREPARATION_MODULE.do_save()" >
		{/if}
		{*if $form.id}
			<input class="btn btn-danger mt-3" type="button" value="Delete" onclick="DO_PREPARATION_MODULE.do_delete()">
		{/if*}
	{/if}
	<input class="btn btn-danger mt-3" type="button" value="Close" onclick="document.location='/do.simple.php?do_type={$form.do_type}'">
</p>


{include file=footer.tpl}

<script type="text/javascript">
DO_MODULE.initilize();
DO_PREPARATION_MODULE.initialize();

{if $po_multi_deliver_to}
	$('srefresh').show();
{/if}

</script>
