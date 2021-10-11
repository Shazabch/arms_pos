{*
7/26/2012 5:15 PM Justin
- Enhanced to hide cancel button while in view mode but user logged on is not owner nor super admin.

7/27/2012 3:14 PM Justin
- Bug fixed on qprice that item appears again even it is being deleted after saved and return from error.
- Bug fixed of qprice table should appear even master item does not contains any sub qprice item.

8/16/2012 10:44 AM Andy
- Add when change price will also automatically copy price to item under same parent/child, same uom, same price typ. (need config sku_change_price_always_apply_to_same_uom)

8/16/2012 11:03 AM Justin
- Bug fixed on system create duplicate QPrice table.

8/16/2012 2:57 PM Andy
- Add GP % and checking only show if got privilege.
- Add need same artno only can copy price.
- Add prompt a list for user to confirm whether they want to copy price.

8/28/2012 3:04 PM Andy
- Add checking to skip own row for copy price.

4/4/2013 10:17 AM Justin
- Bug fixed on system capturing empty branch while create in sub branch.

6/11/2013 4:24 PM Justin
- Bug fixed on multi add window cannot close by click on "X".
- Enhanced multi add window to become dragable.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

5/20/2014 10:12 AM Fithri
- able to select item(s) to reject & must provide reason for each rejected item

7/17/2014 3:03 PM Justin
- Enhanced to have GP, GP(%) and Variance calculation.

10/9/2014 4:19 PM Justin
- Enhanced to have GST calculation.

3/21/2015 4:05 PM Justin
- Enhanced to have "Show More Items" button that will prompt new tab for user to view items in full.

3/23/2015 5:45 PM Justin
- Enhanced to allow user to do cancel while in saved mode.

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

12/10/2015 1:53 PM Andy
- Fix search sku not to check block po.

4/7/2016 10:46 AM Andy
- Fix "Current GP" to use selling price before gst.
- Fix copy price bug when item was deleted.

2/21/2017 2:25 PM Justin
- Bug fixed on the cancel button will still display out even the document is being cancelled.

6/2/2017 13:28 Qiu Ying
- Enhanced to add a "Add All Price Type" button in order to add SKU with different price types
- Enhanced to add a feature to allow auto copy price to other price type

6/13/2017 12:05 PM Andy
- Fixed spelling "hightlight" to "highlight".

11/9/2018 4:42 PM Justin
- Enhanced to have Remark data.

4/22/2021 5:40 PM Edward Au
- select region to tick on certain combo box
*}
{if !$form.approval_screen}
	{include file=header.tpl}
{else}
	<hr noshade size=2>
{/if}

{literal}
<style>
a{
	cursor:pointer;
}

.div_multi_select{
	border:1px solid grey;
	overflow:auto;
	overflow-x:hidden;
	display: inline-block;
	padding: 2px;
}

input[disabled] {
  color:black;
  background: white;
}

input[readonly] {
  color:black;
  background: white;
}

select[disabled] {
  color:black;
  background: white;
}

.future_selling_price {
	background: none repeat scroll 0 0 #f90;
}
.div_sp_excl{
	color: blue;
}
</style>
{/literal}
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var readonly = '{$readonly}';
var id = '{$form.id}';
var curr_bcode = '{$BRANCH_CODE}';
var sku_change_price_always_apply_to_same_uom = int('{$config.sku_change_price_always_apply_to_same_uom}');
var show_cost = '{$sessioninfo.privilege.SHOW_COST}';

{if $gst_settings}
var is_gst_active = 1;
{else}
var is_gst_active = 0;
{/if}

{literal}
var sku_autocomplete = undefined;

function curtain_clicked(){
	$('div_multiple_add_popup').hide();
	curtain(false);
}

var MST_FUTURE_PRICE_MODULE = {
	form_element: undefined,
	initialize: function(){
		this.form_element = document.f_a;
		var sku_autocomplete = undefined;
		var THIS = this;
		if(!this.form_element){
			alert('Commission module failed to initialize.');
			return false;
		}
	
		this.sac_id = this.form_element['id'].value;

		if(curr_bcode == "HQ"){
			// event to toggle branches
			//$('toggle_branches').observe('click', function(){
			//	THIS.toggle_branches_chx(this);
			//});
			// event to toggle set date by branch
			$('date_by_branch').observe('click', function(){
				THIS.toggle_date(this);
			});
		}

		if(!readonly){
			// event to save
			$('save_btn').observe('click', function(){
				THIS.process('save');
			});

			// event to confirm
			$('confirm_btn').observe('click', function(){
				THIS.process('confirm');
			});

			THIS.reset_sku_autocomplete();
			
			var curr_date = new Date();
			var curr_year = curr_date.getFullYear();
			var curr_mth = curr_date.getMonth();
			var curr_day = curr_date.getDate();
			allowed_date = new Date(curr_year, curr_mth, curr_day);
			//allowed_date.setDate(allowed_date.getDate()-1);
			Calendar.setup({
				inputField     :    "date",     // id of the input field
				ifFormat       :    "%Y-%m-%d",      // format of the input field
				button         :    "ds1",  // trigger for the calendar (button ID)
				align          :    "Bl",           // alignment (defaults to "Bl")
				singleClick    :    true,
				dateStatusFunc :    function (date) { // disable those date <= today
								return (date.getTime() < allowed_date.getTime()) ? true : false;
                            }
			});
			
			THIS.reset_row_no();
		}

		if(id <= 100000000 && $('cancel_btn') != undefined){
			// event to cancel
			$('cancel_btn').observe('click', function(){
				THIS.process('cancel');
			});
		}

		// event to close commission without save
		$('close_btn').observe('click', function(){
			if(!readonly) if(!confirm("Close without save?")) return;
            window.location = phpself;
		});

		// event to close commission without save
		if($('more_items_btn') != undefined){
			$('more_items_btn').disabled = false;
			$('more_items_btn').observe('click', function(){
				THIS.more_items_clicked();
			});
		}
		
		new Draggable('div_multiple_add_popup',{ handle: 'div_multiple_add_popup_header'});
	},

	toggle_branches_chx: function (obj){
		
		$$('.effective_branch').each(function(chx){
			if(obj.checked == true) chx.checked = true;
			else chx.checked = false;
			//if(chx.checked) sku_items_list.push(chx.value);
		});
	},
	toggle_date: function (obj){
		if(obj.checked == true){
			this.form_element['hour'].disabled = true;
			this.form_element['minute'].disabled = true;
			this.form_element['date'].disabled = true;
			$('ds1').hide();
		}else{
			this.form_element['hour'].disabled = false;
			this.form_element['minute'].disabled = false;
			this.form_element['date'].disabled = false;
			$('ds1').show();
		}
		$$('.dt').each(function(td){
			if(obj.checked == true) td.show();
			else td.hide();
			//if(chx.checked) sku_items_list.push(chx.value);
		});
		$$('.hr').each(function(td){
			if(obj.checked == true) td.show();
			else td.hide();
			//if(chx.checked) sku_items_list.push(chx.value);
		});
		$$('.min').each(function(td){
			if(obj.checked == true) td.show();
			else td.hide();
			//if(chx.checked) sku_items_list.push(chx.value);
		});
	},
	process: function(type){
		if (check_login()) {
			if(type != "save") if(!confirm("Are you sure want to "+type+"?")) return;

			if(type == "recall"){
				document.f_recall.submit();
			}else{
				document.f_a.a.value = type;
				this.form_element.submit();
			}
		}
	},
	calendar_updated: function(cal){
		alert(cal.params.inputField.value);
		var currentTime = new Date(Y-m-d);
		alert(currentTime);
		var date_id = cal.params.inputField.id.replace("date_from_", "");

		var selected_date_list = $('items').getElementsByClassName("selected_date_list");
		var selected_date_count = selected_date_list.length;

		$A(selected_date_list).each(
			function (r,idx){
				if(r.id == cal.params.inputField.id) return;
				
				if(r.value == cal.params.inputField.value){
					alert("The date ["+cal.params.inputField.value+"] is already existed in other commission.");
					cal.params.inputField.value = "";
				}
			}
		);
	},
	type_changed: function(id, obj){
		if(obj == undefined) return;
		
		if(obj.value == "qprice"){
			var e = $('tbl_item').getElementsByClassName('isi_id');
			var total_row=e.length;

			var qprice_existed = false;
			for(var i=0;i<e.length;i++){
				var id_split = e[i].id.split("_");
				var curr_id = id_split[2];
				if(document.f_a['type['+curr_id+']'].value == "qprice" && document.f_a['is_deleted['+curr_id+']'].value != 1 && e[i].id != document.f_a['si_id['+id+']'].id && e[i].value == document.f_a['si_id['+id+']'].value){
					qprice_existed = true;
					break;
				}
			}
			
			if(qprice_existed == true){
				alert("QPrice for this SKU item already existed on item list.");
				obj.value = "normal";
				return;
			}

			document.f_a['min_qty['+id+']'].disabled = false;
			document.f_a['min_qty['+id+']'].show();
			$('span_min_qty_'+id).hide();
			if(document.f_a['trade_discount_code['+id+']'] != undefined){
				document.f_a['trade_discount_code['+id+']'].disabled = true;
				document.f_a['trade_discount_code['+id+']'].hide();
				$('span_tdc_'+id).show();
			}else{
				if(document.f_a['trade_discount_code['+id+']'] != undefined) document.f_a['trade_discount_code['+id+']'].show();
				if($('span_tdc_'+id) != undefined)  $('span_tdc_'+id).hide();
			}
			
			if($('qprice_item_'+id) == undefined){
				var sid = document.f_a['si_id['+id+']'].value;
				// load qprice list
				var THIS = this;
				var prm = $(this.form_element).serialize();

				var params = {
					'a': 'ajax_add_qprice_items',
					mid: id,
					sku_item_id: sid
				};

				prm += '&'+$H(params).toQueryString();
				ajax_request(phpself, {
					parameters: prm,
					method: 'post',
					onSuccess: function(msg){
						var str = msg.responseText.trim();
						var ret = {};
						var err_msg = '';

						try{
							//ret = JSON.parse(str); // try decode json object
							eval("var json = "+msg.responseText);
							
							for(var tr_key in json){
								if(json[tr_key]['ok'] == 1 && json[tr_key]['html']){ // success
									// append html
									new Insertion.After($('item_'+id), json[tr_key]['html']);
								}
							}
						}catch(ex){ // failed to decode json, it is plain text response
							err_msg = str;
						}


						// prompt the error
						//if(err_msg.trim()) alert(err_msg);	
					},
					onComplete: function(msg){
						if(is_barcode){
							document.f_a.grn_barcode.focus();
							document.f_a.grn_barcode.value = "";
						}else{
							document.f_a.autocomplete_sku.focus();
							THIS.reset_sku_autocomplete(); // reset autocomplete for sku
						}
						
						if(!/html/.test(msg.responseText)) alert(msg.responseText);
						else THIS.reset_row_no(); // reset row num
					}
				});
			}
		}else{
			if(document.f_a['si_sku_type['+id+']'].value == "CONSIGN"){
				document.f_a['trade_discount_code['+id+']'].disabled = false;
				if(document.f_a['trade_discount_code['+id+']'] != undefined) document.f_a['trade_discount_code['+id+']'].show();
				if($('span_tdc_'+id) != undefined) $('span_tdc_'+id).hide();
			}
			document.f_a['min_qty['+id+']'].value = 0;
			document.f_a['min_qty['+id+']'].disabled = true;
			document.f_a['min_qty['+id+']'].hide();
			$('span_min_qty_'+id).show();
			
			if($('qprice_item_'+id) != undefined) $('qprice_item_'+id).remove();
		}
	},
	reset_row_no: function(){
		var e = $('items').getElementsByClassName('row_no');
		var total_row=e.length;
		var deduct_row = 0;
		
		for(var i=0;i<total_row;i++){
			var s = e[i].id.split("_");
			if(document.f_a['is_deleted['+s[2]+']'].value == 1) deduct_row += 1;
			var no_row = (i+1-deduct_row);
			td_1=(no_row)+'.';
			e[i].innerHTML=td_1;
			e[i].title='No. '+(no_row);
			document.f_a['row_no['+s[2]+']'].value = no_row;
		}
	},
	reset_qprice_row_no: function(id){
		var e = $('qprice_item_'+id).getElementsByClassName('qprice_row_no');
		var total_row=e.length;
		var deduct_row = 0;
		
		for(var i=0;i<total_row;i++){
			var s = e[i].id.split("_");
			if(document.f_a['is_deleted['+s[3]+']'].value == 1) deduct_row += 1;
			var no_row = (i+1-deduct_row);
			td_1=(no_row)+'.';
			e[i].innerHTML=td_1;
			e[i].title='No. '+(no_row);
			document.f_a['row_no['+s[3]+']'].value = no_row;
		}
	},
	ajax_add_item: function(sku_items_list, is_barcode,add_all_price_type){
		var THIS = this;
		var prm = $(this.form_element).serialize();
		
		if(add_all_price_type == 'undefined') add_all_price_type = 0; 

		var params = {
			'a': 'ajax_add_item',
			is_barcode: is_barcode,
			add_all_price_type: add_all_price_type
		};

		prm += '&'+$H(params).toQueryString();
		prm += '&'+$H({'sku_items_list[]': sku_items_list}).toQueryString();
		ajax_request(phpself, {
			parameters: prm,
			method: 'post',
			onSuccess: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				try{
	                //ret = JSON.parse(str); // try decode json object
					eval("var json = "+msg.responseText);
					
					for(var tr_key in json){
						if(json[tr_key]['ok'] == 1 && json[tr_key]['html']){ // success
							// append html
							new Insertion.Bottom('tbody_item_list', json[tr_key]['html']);
						}
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}


			    // prompt the error
			    //if(err_msg.trim()) alert(err_msg);	
			},
			onComplete: function(msg){
				if(is_barcode){
					document.f_a.grn_barcode.focus();
					document.f_a.grn_barcode.value = "";
				}else{
					document.f_a.autocomplete_sku.focus();
					THIS.reset_sku_autocomplete(); // reset autocomplete for sku
				}
				
				if(!/html/.test(msg.responseText)) alert(msg.responseText);
				else THIS.reset_row_no(); // reset row num
			}
		});
	},

	ajax_add_qprice_item: function(sid, id){
		var THIS = this;
		var prm = $(this.form_element).serialize();

		var params = {
			'a': 'ajax_add_qprice_items',
			sku_item_id: sid,
			is_sub_item: 1,
			mid: id
		};

		prm += '&'+$H(params).toQueryString();
		ajax_request(phpself, {
			parameters: prm,
			method: 'post',
			onSuccess: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				try{
	                //ret = JSON.parse(str); // try decode json object
					eval("var json = "+msg.responseText);
					
					for(var tr_key in json){
						if(json[tr_key]['ok'] == 1 && json[tr_key]['html']){ // success
							// append html
							new Insertion.Bottom('qprice_tbl_'+id, json[tr_key]['html']);
						}
					}
					THIS.reset_qprice_row_no(id);
					
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    //if(err_msg.trim()) alert(err_msg);	
			},
			onComplete: function(msg){
				if(is_barcode){
					document.f_a.grn_barcode.focus();
					document.f_a.grn_barcode.value = "";
				}else{
					document.f_a.autocomplete_sku.focus();
					THIS.reset_sku_autocomplete(); // reset autocomplete for sku
				}
				
				if(!/html/.test(msg.responseText)) alert(msg.responseText);
				else THIS.reset_row_no(); // reset row num
			}
		});
	},

	delete_item: function(id, mid){
		var THIS = this;
		if(!confirm("Are you sure want to delete?")) return;
		if(id == 0 || id == ""){
			alert("Nothing to delete!");
			return;
		}
		document.f_a['is_deleted['+id+']'].value = 1;
		$('item_'+id).hide();
		if($('qprice_item_'+id) != undefined){
			var e = $('qprice_item_'+id).getElementsByClassName('qprice_deleted');
			var total_row=e.length;

			for(var i=0;i<total_row;i++){
				e[i].value = 1;
			}

			$('qprice_item_'+id).hide();
		}

		if($('is_qprice_'+id) != undefined && $('is_qprice_'+id).value == 1 && $('qprice_item_'+id) == undefined){
			THIS.reset_qprice_row_no(mid);
		}
		
		THIS.reset_row_no();
	},
	
	reset_sku_autocomplete : function(){
		if ($('autocomplete_sku')==undefined) return;
		var THIS = this;
		
		var param_str = "a=ajax_search_sku&show_varieties=1&type="+getRadioValue(document.f_a.search_type);

		if (sku_autocomplete != undefined){
			sku_autocomplete.options.defaultParams = param_str;
		}
		else{
			sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
				afterUpdateElement: function (obj, li) {
				s = li.title.split(",");
				if(s[0]==0){
					$('autocomplete_sku').value = '';
					return;
				}
				
				document.f_a.sku_item_id.value =s[0];
				document.f_a.sku_item_code.value = s[1];
			}});
		}
		
		THIS.clear_autocomplete();
		$('autocomplete_sku').focus();
	},
	
	// SKU AUTOCOMPLETE DIALOG add
	add_autocomplete : function(){
		if(document.f_a.sku_item_id.value == 0) return;
		var sku_items_list = [];
		sku_items_list.push(document.f_a.sku_item_id.value);
		MST_FUTURE_PRICE_MODULE.ajax_add_item(sku_items_list, '');
	},
	
	clear_autocomplete : function(){
		$('sku_item_id').value = '';
		$('sku_item_code').value = '';
		$('autocomplete_sku').value = '';
		$('autocomplete_sku_choices').innerHTML = 'Loading...';
		$('autocomplete_sku_choices').style.display='none';
		$('autocomplete_sku').focus();
	},
	
	open_multi_add : function(){
		var type = getRadioValue(document.f_a['search_type']);
		if(type==5)	return false;    // handheld no multiple add

		var v = $('autocomplete_sku').value.trim();
		if(v=='')   return false;   // empty search value
		
		var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a['search_type'])+'&hide_print=1&show_multiple=1';
		
		new Ajax.Updater('div_multiple_add_popup_content','ajax_autocomplete.php?'+param_str,{
			parameters:{
				'value': v
			},
			evalScripts: true
		});
		
		curtain(true);
		$('div_multiple_add_popup_content').update(_loading_);
		center_div($('div_multiple_add_popup').show());
	},
	// function when user change selling price	
	propose_price_changed: function(item_id){
		if(!item_id)	return false;
		
		this.recalculate_gp_per(item_id);
		var price = this.form_element['future_selling_price['+item_id+']'].value;
		
		// need to copy selling price to same parent/child, same uom and same price type
		if(sku_change_price_always_apply_to_same_uom){
			var curr_price_type = this.form_element['type['+item_id+']'].value;
			if(curr_price_type != 'qprice'){
				this.update_price(item_id,curr_price_type,0);
			}			
		}
		this.update_all_price_type(item_id);
	},
	// function when user change qprice
	propose_qprice_changed: function(item_id){
		this.recalculate_gp_per(item_id);
	},
	// function to recalculate gp
	recalculate_gp_per: function(item_id){
		if(!item_id || !show_cost)	return false;
		
		var inclusive_tax = this.form_element["inclusive_tax["+item_id+"]"].value;
		var gst_rate = this.form_element["gst_rate["+item_id+"]"].value;
		if(is_gst_active && inclusive_tax == "yes" && gst_rate > 0) var selling_price = float(this.form_element["gst_selling_price["+item_id+"]"].value);
		else var selling_price = float(this.form_element['future_selling_price['+item_id+']'].value);
		var cost = float(this.form_element['cost['+item_id+']'].value);
		var gp = round(selling_price - cost, 4);
		var gp_per = 0;
		if(selling_price){
			gp_per = round(gp/selling_price*100,2);
		}
		
		// update new GP
		$('td_gp-'+item_id).update(gp);
		$('td_gp_per-'+item_id).update(gp_per);
		
		var sp_check = this.form_element['selling_price['+item_id+']'].value;
		if(is_gst_active){
			sp_check = this.form_element['selling_price_before_gst['+item_id+']'].value;
		}
		var gp_var = round(selling_price - sp_check, 4);
		var gp_per_var = round(gp_var / selling_price * 100, 2);
		
		// update GP variance
		$('td_gp_var-'+item_id).update(gp_var);
		$('td_gp_per_var-'+item_id).update(gp_per_var);
		
		// update font color depending GP on negative/positive
		if(round(gp_per,2) < 0){
			$("td_gp-"+item_id).setStyle({
				color: 'red'
			});
			$("td_gp_per-"+item_id).setStyle({
				color: 'red'
			});
		}else{
			$("td_gp-"+item_id).setStyle({
				color: 'green'
			});
			$("td_gp_per-"+item_id).setStyle({
				color: 'green'
			});
		}
		
		if(round(gp_var,2) < 0){
			$("td_gp_var-"+item_id).setStyle({
				color: 'red'
			});
			$("td_gp_per_var-"+item_id).setStyle({
				color: 'red'
			});
		}else{
			$("td_gp_var-"+item_id).setStyle({
				color: 'green'
			});
			$("td_gp_per_var-"+item_id).setStyle({
				color: 'green'
			});
		}
	},
	
	calculate_gst : function(id, obj){
		var inclusive_tax = this.form_element["inclusive_tax["+id+"]"].value;
		var gst_rate = float(this.form_element["gst_rate["+id+"]"].value);
		if(!is_gst_active || inclusive_tax == "inherit") return;

		// calculate selling price after/before GST
		if(obj != undefined && obj.name == "gst_selling_price["+id+"]"){ // found user changing GST selling price
			// calculate gst amount
			var gst_selling_price = float(obj.value);
			
			if (inclusive_tax=='no') {
				var selling_price=(gst_selling_price*100)/(100+gst_rate);
				var gst_amt=float(selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
				var selling_price=float(gst_selling_price+gst_amt);
			}

			this.form_element["future_selling_price["+id+"]"].value = round(selling_price, 2);
		}else{
			var selling_price = float(this.form_element["future_selling_price["+id+"]"].value);
			
			if (inclusive_tax=='yes') {
				var gst_selling_price=(selling_price*100)/(100+gst_rate);
				var gst_amt=float(gst_selling_price) * gst_rate / 100;
			}
			else{
				var gst_amt=float(selling_price) * gst_rate / 100;
				var gst_selling_price=float(selling_price+gst_amt);
			}
			
			this.form_element["gst_selling_price["+id+"]"].value=round(gst_selling_price,2);
		}
		
		this.form_element["gst_amount["+id+"]"].value = round(gst_amt, 2);
	},
	
	more_items_clicked: function(){
		window.open(phpself+"?a=show_full_items&id="+this.form_element['id'].value+"&branch_id="+this.form_element['branch_id'].value);
	},
	
	add_all_price_type : function(){
		if(document.f_a.sku_item_id.value == 0) return;
		var sku_items_list = [];
		sku_items_list.push(document.f_a.sku_item_id.value);
		MST_FUTURE_PRICE_MODULE.ajax_add_item(sku_items_list, '', 1);
	},
	update_all_price_type : function(item_id){
		if(!item_id) return false;
		var curr_price_type = this.form_element['type['+item_id+']'].value;
		if(!curr_price_type == 'qprice')	return false;
		this.update_price(item_id,curr_price_type,1);
	},
	update_price : function(item_id, curr_price_type, is_all_price_type){
		var str1 = " (under same parent/child, same art no, same UOM and same price type)?\n";
		if(is_all_price_type){
			str1 = " (under same ARMS Code and different price type)?\n";
		}
		
		this.recalculate_gp_per(item_id);
		var price = this.form_element['future_selling_price['+item_id+']'].value;
		var sku_id = this.form_element['si_sku_id['+item_id+']'].value;
		var uom_fraction = this.form_element['si_packing_uom_fraction['+item_id+']'].value;
		var artno = this.form_element['si_artno['+item_id+']'].value.trim();
		var item_id_list_need_to_update = [];
		var tr_item_row_list = $$('#tbody_item_list tr.tr_item_row');
		
		for(var i=0; i<tr_item_row_list.length; i++){
			var tmp_item_id = tr_item_row_list[i].id.split("_")[1];
			// skip deleted item
			var is_deleted = document.f_a['is_deleted['+tmp_item_id+']'].value;
			if(is_deleted)	continue;
			var tmp_price_type = this.form_element['type['+tmp_item_id+']'].value;
			
			if(is_all_price_type){
				var tmp_sku_item_code = this.form_element['si_code['+tmp_item_id+']'].value.trim();
				var sku_item_code = this.form_element['si_code['+item_id+']'].value;
				
				if(tmp_item_id == item_id || tmp_price_type == 'qprice')	continue;
				
				if(sku_item_code == tmp_sku_item_code){
					item_id_list_need_to_update.push(tmp_item_id);
				}
			}else{
				if(tmp_item_id == item_id)	continue;	// skip own row
				var tmp_sku_id = this.form_element['si_sku_id['+tmp_item_id+']'].value;
				var tmp_uom_fraction = this.form_element['si_packing_uom_fraction['+tmp_item_id+']'].value;
				var tmp_artno = this.form_element['si_artno['+tmp_item_id+']'].value.trim();
		
				//alert(sku_id +'=='+ tmp_sku_id +'&&'+ uom_fraction +'=='+ tmp_uom_fraction +'&&'+ curr_price_type +'=='+ tmp_price_type +'&&'+ artno +'=='+ tmp_artno);
				if(sku_id == tmp_sku_id && uom_fraction == tmp_uom_fraction && curr_price_type == tmp_price_type && artno == tmp_artno){
					item_id_list_need_to_update.push(tmp_item_id);
				}
			}
		}
		
		if(item_id_list_need_to_update.length>0){
			var str = "Do you want to apply proposed price '"+price+"' to belows SKU" + str1;
			str += "=====================================================\n";
			
			for(var i=0; i<item_id_list_need_to_update.length; i++){
				var tmp_item_id = item_id_list_need_to_update[i];
				var tmp_sku_item_code = this.form_element['si_code['+tmp_item_id+']'].value;
				var tmp_artno = this.form_element['si_artno['+tmp_item_id+']'].value;
				var tmp_desc = this.form_element['si_description['+tmp_item_id+']'].value;
				var tmp_row_no = this.form_element['row_no['+tmp_item_id+']'].value;
				var tmp_type = this.form_element['type['+tmp_item_id+']'].value;
				
				if(!tmp_artno)	tmp_artno = '-';
				var str2 = "\n";
				if(is_all_price_type) str2 = ", Price Type: " + tmp_type + "\n";
				str += tmp_row_no + ". ARMS Code: "+tmp_sku_item_code+", ArtNo: "+tmp_artno+", Desc: "+tmp_desc + str2;
			}
			
			if(confirm(str)){
				for(var i=0; i<item_id_list_need_to_update.length; i++){
					var tmp_item_id = item_id_list_need_to_update[i];
					
					this.form_element['future_selling_price['+tmp_item_id+']'].value = price;
					this.recalculate_gp_per(tmp_item_id);
					this.calculate_gst(tmp_item_id);
				}
			}
		}
	}
}

// SKU MUTLIADD DIALOG add
function submit_multi_add(ele){
	var sku_items_list = [];
	$$('#tbl_multi_add input.chx_sid_list').each(function(chx){
		if(chx.checked) sku_items_list.push(chx.value);
	});
	if(sku_items_list.length<=0) return;
	ele.value = 'Adding...';
	ele.disabled = true;
	MST_FUTURE_PRICE_MODULE.ajax_add_item(sku_items_list, '');
	default_curtain_clicked();
}

function add_grn_barcode_item(){
	if(!document.f_a['grn_barcode'].value) return;
	MST_FUTURE_PRICE_MODULE.ajax_add_item('', 1);
}

function check_branch_by_group()
{
	var sl_brn_grp = $('sel_brn_grp');
	var sel_grp_val = sl_brn_grp.options[sl_brn_grp.selectedIndex].value;
	if (sel_grp_val){
    var sel_brn_list = sel_grp_val.split(',');
	for (i=0, len=sel_brn_list.length; i<len; i++){	
			if (!$('dt_'+sel_brn_list[i]).checked){
				$('dt_'+sel_brn_list[i]).checked = true;
			}
		}
	}

	else
	{
		var ele=document.getElementsByClassName('effective_branch');  
                for(var i=0; i<ele.length; i++){  
                    if(ele[i].type=='checkbox')  
                        ele[i].checked=true;  
                }
	}
	
}

function uncheck_branch_by_group()
{
	var sl_brn_grp = $('sel_brn_grp');
	var sel_grp_val = sl_brn_grp.options[sl_brn_grp.selectedIndex].value;
	if (sel_grp_val){
    var sel_brn_list = sel_grp_val.split(',');
	for (i=0, len=sel_brn_list.length; i<len; i++){	
			if ($('dt_'+sel_brn_list[i]).checked){
				$('dt_'+sel_brn_list[i]).checked = false;
			}
		}
	}

	else
	{
		var ele=document.getElementsByClassName('effective_branch');  
                for(var i=0; i<ele.length; i++){  
                    if(ele[i].type=='checkbox')  
                        ele[i].checked=false;  
                }
	}
}

</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$PAGE_TITLE} ({if is_new_id($form.id)}New){else}#{$form.id|string_format:"%05d"})
					<h5>Status:
					{if $form.expired}
						Expired
					{elseif $form.status eq 1}
						{if $form.approved}
							Fully Approved
						{else}
							In Approval Cycle
						{/if}
					{elseif $form.status eq 2}
						Rejected
					{elseif $form.status eq 5}
						Cancelled/Terminated
					{else}
						Draft
					{/if}
					</h5>
				{/if}
			
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>



{if $approval_history}
	<br />
	<div class="stdframe" style="background:#fff">
		<h4>Approval History</h4>
		{section name=i loop=$approval_history}
		<p>
			{if $approval_history[i].status==0}
				<img src="ui/notify_sku_reject.png" width="16" height="16" align="absmiddle" title="Reset">
			{elseif $approval_history[i].status==1}
				<img src="ui/approved.png" width="16" height="16">
			{elseif $approval_history[i].status==2}
				<img src="ui/rejected.png" width="16" height="16">
			{else}
				<img src="ui/terminated.png" width="16" height="16">
			{/if}
			{$approval_history[i].timestamp} by {$approval_history[i].u}<br>
			{$approval_history[i].log}
		</p>
		{/section}
	</div>
	<br />
{/if}

<div id="div_multiple_add_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_multiple_add_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Multiple Add SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_multiple_add_popup_content" style="padding:2px;"></div>
</div>

{if $form.approval_screen}
	<form name="f_b" method="post">
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="a">
		<input type="hidden" name="comment">
		<input type="hidden" name="approvals" value="{$form.approvals}">
		<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
	</form>
{/if}

{if $readonly}
	<form name="f_recall" method="post">
		<input type="hidden" name="branch_id" value="{$form.branch_id}" />
		<input type="hidden" name="id" value="{$form.id}" />
		<input type="hidden" name="a" value="recall">
	</form>
{/if}

<div id=err>
	<div class="errmsg" id="errmsg">
		<ul>
		{foreach from=$errm.mst item=e}
			<li> {$e}
		{/foreach}
		</ul>
	</div>
</div>

<form name="f_a" method="post" onsubmit="return false;">
	<input type="hidden" name="a">
	<input type="hidden" name="id" value="{$form.id}">
	<input type="hidden" name="branch_id" value="{$form.branch_id}">
	<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
	<input type="hidden" name="active" value="{$form.active}">
	<input type="hidden" name="status" value="{$form.status}">
	<input type="hidden" name="approved" value="{$form.approved}">
	<input type="hidden" name="gst_settings" value="{$gst_settings}">
	{if $form.approval_screen}
		<input type="hidden" name="comment" value="">
		<input type="hidden" name="approvals" value="{$form.approvals}">
	{/if}
<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe">
			<h4>General Information</h4>
			<table border="0" cellspacing="0" cellpadding="4">
				{if $BRANCH_CODE eq 'HQ'}
					<tr class="form-label">
						<td><b>Set Date by Branch</b></td>
						<td>
							<input type="checkbox" name="date_by_branch" id="date_by_branch" value="1" {if $form.date_by_branch}checked{/if} {if $form.approval_screen}onclick="toggle_date(this);"{/if} />
						</td>
					</tr>
				{/if}
				<tr>
					<td><b class="form-label">Date<span class="text-danger"> *</span> </b></td>
				
						<td>
						<div class="form-inline">
						<input class="form-control" type="text" name="date" id="date" value="{$form.date|ifzero:''}" class="date" readonly>
						{if !$readonly || $form.approval_screen}
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="ds1" style="cursor: pointer; {if $form.date_by_branch}display:none{/if}" title="Select Date">&nbsp;
						{/if}
						H: 
						&nbsp;<select class="form-control" 	 name="hour" {if $form.date_by_branch}disabled{/if}>
							{section name=hr loop=24 start=0}
								{assign var=hour value=$smarty.section.hr.iteration-1}
								<option value="{$hour}" {if $form.hour eq $hour}selected{/if}>{$hour}</option>
							{/section}
						</select>
					&nbsp;	M: 
						&nbsp;<select class="form-control" name="minute" {if $form.date_by_branch}disabled{/if}>
							<option value="0" {if !$form.minute}selected{/if}>0</option>
							<option value="30" {if $form.minute eq "30"}selected{/if}>30</option>
						</select> 
					</div>
					</td>
				
				</tr>
				<tr>
					<td valign="top"><b class="form-label">Branch</b></td>
					<td>
						{if $BRANCH_CODE eq 'HQ'}
						<!-- Branch -->
							<div class="div_multi_select" id="div_multi_select">
							You may select multiple branches to deliver <span class="text-danger"> *</span><br>
						<br />
								<ul style="list-style:none;">
									<table width="100%">
										{if !$form.approval_screen}
											Select by: 
												<select class="form-control" name="sel_brn_grp" id="sel_brn_grp" >
													<option value="" >-- All --</option>
														{section name=j loop=$brn_grp_list}
													<option value="{$brn_grp_list[j].grp_items}" >{$brn_grp_list[j].code} - {$brn_grp_list[j].description}</option>
														{/section}
												</select>&nbsp;&nbsp;
						
												<input class="btn btn-info mt-2" type="button"  value="Select " onclick="check_branch_by_group();" />&nbsp;
												<input class="btn btn-danger mt-2" type="button"  value="De-select" onclick="uncheck_branch_by_group();" /><br /><br />
		
											<tr>
												{*
												<td><input type="checkbox" id="toggle_branches" /></td>
												<td colspan="3"><b>All</b></td>
												*}
											</tr>
										{/if}
										{foreach from=$branches key=bid item=r}
											{if !$form.approval_screen || ($form.approval_screen && $form.effective_branches.$bid)}
												<tr>
													<td>
														{if $form.approval_screen}
															<input type="hidden" name="effective_branches[{$bid}]" {if $form.effective_branches.$bid}value="{$bid}"{/if} class="effective_branch" id="dt_{$bid}"/>
														{else}
															<input type="checkbox" name="effective_branches[{$bid}]" value="{$bid}" {if $form.effective_branches.$bid}checked{/if} class="effective_branch" id="dt_{$bid}"/>
														{/if}
													</td>
													<td>{$r.code}</td>
													<td class="dt" {if !$form.date_by_branch}style="display:none;"{/if}>
														<div class="form-inline">
															<input class="form-control" size="10" type="text" name="branch_date[{$bid}]" id="branch_date_{$bid}" value="{$form.effective_branches.$bid.date|ifzero:''}" class="date" readonly>
														{if !$readonly || ($form.approval_screen && $form.effective_branches.$bid)}
															<img align="absmiddle" src="ui/calendar.gif" id="ds1_{$bid}" style="cursor: pointer;" title="Select Date">&nbsp;
														{/if}
														</div>
													</td>
													<td class="hr" {if !$form.date_by_branch}style="display:none;"{/if} nowrap>
														<div class="form-inline">
															H: &nbsp;
														<select class="form-control" name="branch_hour[{$bid}]">
															{section name=hr loop=24 start=0}
																{assign var=hour value=$smarty.section.hr.iteration-1}
																<option value="{$hour}" {if $form.effective_branches.$bid.hour eq $hour}selected{/if}>{$hour}</option>
															{/section}
														</select>
														</div>
													</td>
													<td class="min" {if !$form.date_by_branch}style="display:none;"{/if} nowrap>
													<div class="form-inline">
														&nbsp;M: &nbsp;
														<select class="form-control" name="branch_minute[{$bid}]">
															<option value="0" {if !$form.effective_branches.$bid.minute}selected{/if}>0</option>
															<option value="30" {if $form.effective_branches.$bid.minute eq "30"}selected{/if}>30</option>
														</select>
													</td>
													</div>
												</tr>
										
												{literal}
												<script>
												var curr_date = new Date();
												var curr_year = curr_date.getFullYear();
												var curr_mth = curr_date.getMonth();
												var curr_day = curr_date.getDate();
												allowed_date = new Date(curr_year, curr_mth, curr_day);
												Calendar.setup({
													inputField     :    "branch_date_"+{/literal}{$bid}{literal},     // id of the input field
													ifFormat       :    "%Y-%m-%d",      // format of the input field
													button         :    "ds1_"+{/literal}{$bid}{literal},  // trigger for the calendar (button ID)
													align          :    "Bl",           // alignment (defaults to "Bl")
													singleClick    :    true,
													dateStatusFunc :    function (date) { // disable those date <= today
																	return (date.getTime() < allowed_date.getTime()) ? true : false;
																}
												});
												</script>
												{/literal}
											{/if}
										{/foreach}
									</table>
								</ul>
							</div>
						{else}
							{$BRANCH_CODE}
							<input class="form-control" type="hidden" name="effective_branches[{$sessioninfo.branch_id}]" value="{$sessioninfo.branch_id}" />
						{/if}
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Created By</b></td>
					<td>
						{$form.username|default:$sessioninfo.u}
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Remark</b></td>
					<td>
						<textarea class="form-control" cols="40" rows="" name="remark">{$form.remark}</textarea>
					</td>
				</tr>
			</table>
			</div>
	</div>
</div>
	<br />
	<ul>
		{if $config.sku_change_price_always_apply_to_same_uom}
			<li>Change proposed price will automatically apply to all SKU under same parent/child, same art no, same UOM and same price type.</li>
		{/if}
		{if $gst_settings}
			<li>Field highlight in <label class="future_selling_price"><img width="17" src="/ui/pixel.gif" /></label> is the item's price change.</li>
			<li>This module will calculate GP using price exclusive GST, ignoring the branch GST status.</li>
		{/if}
	</ul>
	
	<div id="items" class="stdframe">
		{if $errm.dtl}
			<div id=err><div class=errmsg><ul>
			{foreach from=$errm.dtl item=e}
				<div class="alert alert-danger rounded"><li> {$e} </li></div>
			{/foreach}
			</ul></div></div>
		{/if}
			<div class="card mx-3">
				<div class="card-body">
					<div class="table-responsive">
						<table width="100%" id="tbl_item" class="report_table table mb-0 text-md-nowrap  table-hover tbl_item input_no_border body">
							<thead class="bg-gray-100">
								<tr>
									<th rowspan="2">#</th>
									{if $form.status eq 1 && !$form.approved && $form.approval_screen and $config.sku_change_price_approval_allow_reject_by_items}
									<th rowspan="2">Reject</th>
									{/if}
									<th rowspan="2">ARMS</th>
									<th rowspan="2">Artno</th>
									<th rowspan="2">Mcode</th>
									<th rowspan="2">Description</th>
									<th rowspan="2">Stock<br />Balance</th>
									{if $sessioninfo.privilege.SHOW_COST}
										<th rowspan="2">Cost</th>
									{/if}
									<th rowspan="2">Price</th>
									<th rowspan="2">Price Type</th>
									<th rowspan="2">Discount<br />Code</th>
									<th rowspan="2">Min Qty<br />(QPrice)</th>
									<th rowspan="2">Proposed<br />Price</th>
									{if $sessioninfo.privilege.SHOW_COST}
										<th colspan="2">Current</th>
										<th colspan="2">New</th>
										<th colspan="2">Variance</th>
									{/if}
								</tr>
								{if $sessioninfo.privilege.SHOW_COST}
									<tr height="32" bgcolor="#ffffff" class="small">
										<th>GP</th>
										<th>GP(%)</th>
										<th>GP</th>
										<th>GP(%)</th>
										<th>GP</th>
										<th>GP(%)</th>
									</tr>
								{/if}
							</thead>
							<tbody id="tbody_item_list" class="fs-08">
								{foreach from=$form.items key=r item=item name=i}
									{assign var=row_no value=$smarty.foreach.i.iteration}
									{*<tr bgcolor="#ffee99" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';"  id="titem{$item.id}">*}
										{include file="masterfile_sku_items.future_price.open.item.tpl"}
										{assign var=mst_si_id value=$item.sku_item_id}
										{if $item.type eq 'qprice'}
											{include file="masterfile_sku_items.future_price.open.item.qprice.tpl"}
										{/if}
									{*</tr>*}
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="card mx-3">
				<div class="card-body">
			{if !$readonly}
				<tfoot id="tbl_footer">
					<tr class="add_sku_row" bgcolor="#f3f3f0">
						<td colspan="18">
							{*include file='sku_items_autocomplete.tpl' multiple_add=1 block_list=1*}
							<input id="sku_item_id" name="sku_item_id" size="3" type="hidden">
							<input id="sku_item_code" name="sku_item_code" size="13" type="hidden">
							<br>
							<b>Search SKU</b>
							<div class="form-inline">
								&nbsp;&nbsp;<input class="form-control" id="autocomplete_sku" name="sku" style="width:500px;" onclick="this.select()">
								&nbsp;<input type="button" value="Add" onclick="MST_FUTURE_PRICE_MODULE.add_autocomplete();" class="btn btn-info">
								
								&nbsp;<input type="button" value="Multi Add" onclick="MST_FUTURE_PRICE_MODULE.open_multi_add()" class="btn btn-primary" >
								
								&nbsp;<input type="button" value="Add All Price Type" onclick="MST_FUTURE_PRICE_MODULE.add_all_price_type()" class="btn btn-info" >
								
							</div>

							<div id="autocomplete_sku_choices" class="autocomplete" style="height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
							<br>
							<img src="ui/pixel.gif" width="65" height="1">
							<input onchange="MST_FUTURE_PRICE_MODULE.reset_sku_autocomplete();" type="radio" name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
							<input onchange="MST_FUTURE_PRICE_MODULE.reset_sku_autocomplete();" type="radio" name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
							<input onchange="MST_FUTURE_PRICE_MODULE.reset_sku_autocomplete();" type="radio" name="search_type" value="3"> ARMS Code
							<input onchange="MST_FUTURE_PRICE_MODULE.reset_sku_autocomplete();" type="radio" name="search_type" value="4"> Description
							<br>
							<hr noshade size=1>
							{include file='scan_barcode_autocomplete.tpl'}
							<span id="span_autocomplete_adding" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Adding... Please wait</span>
						</td>
					</tr>
				</tfoot>
			{/if}
		</table>
		{if $item_max}
			<input type="button" id="more_items_btn" value="More Items" style="font:bold 20px Arial; background-color:#f90; color:#fff;">
		{/if}
	</div>
</div>
	<p align="center">
		{if !$readonly}
			<input type="button" id="save_btn" name="save_btn" value="Save" class="btn btn-warning">
			<input type="button" id="cancel_btn" value="Cancel" class="btn btn-danger">
			<input type="button" id="confirm_btn" value="Confirm" class="btn btn-success">
		{/if}
		
		{if !$form.approval_screen}
			{if ($form.user_id eq $sessioninfo.id || $sessioninfo.level>=$config.doc_reset_level) && $sessioninfo.branch_id eq $form.branch_id && !is_new_id($form.id)}
				{if $form.approved}
					{if $form.status!=4 && $form.status!=5 && $form.status!=0 && $form.active}
						<input type="button" id="cancel_btn" value="Cancel" class="btn btn-danger">
					{/if}
				{/if}
			{/if}
			<input type="button" id="close_btn" name="close_btn" value="Close" class="btn btn-info">
		{/if}
	</p>
</form>

<p align="center">
	{if $form.status eq 1 && !$form.approved && $form.approval_screen}
		<input type="button" value="Approve" style="background-color:#f90; color:#fff;" onclick="do_approve()">
		<input type="button" value="Reject" style="background-color:#f90; color:#fff;" onclick="do_reject()">
		<input type="button" value="Terminate" style="background-color:#900; color:#fff;" onclick="do_cancel()">
	{else}
	{/if}
</p>

<script>
{if $readonly}
	{if $form.approval_screen}
		document.f_a['date_by_branch'].disabled = false;
		
		{literal}
		var curr_date = new Date();
		var curr_year = curr_date.getFullYear();
		var curr_mth = curr_date.getMonth();
		var curr_day = curr_date.getDate();
		allowed_date = new Date(curr_year, curr_mth, curr_day);
		//allowed_date.setDate(allowed_date.getDate()-1);
		Calendar.setup({
			inputField     :    "date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "ds1",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			dateStatusFunc :    function (date) { // disable those date <= today
							return (date.getTime() < allowed_date.getTime()) ? true : false;
						}
		});
		{/literal}
	{else}
		Form.disable(document.f_a);
		// if found is approved and active from approved tab, allow to do cancel
		{if $form.approved && $form.active}
			document.f_a['a'].disabled = false;
			document.f_a['id'].disabled = false;
			document.f_a['branch_id'].disabled = false;
			document.f_a['status'].disabled = false;
			document.f_a['approved'].disabled = false;
		{/if}
	{/if}
{/if}
{if !$form.approval_screen}
	MST_FUTURE_PRICE_MODULE.initialize();
{/if}

</script>
{if !$form.approval_screen}
	{include file=footer.tpl}
{/if}
