{*
7/16/2012 4:33:43 PM Justin
- Fixed bug of system unable to add new commission while delete a new commission added from the empty list.

4/21/2017 8:46 AM Khausalya 
- Enhanced changes from RM to use config setting. 

6/12/2017 16:52 Qiu Ying
- Bug fixed on qty values are listed with currency symbols

10/23/2018 3:41 PM Justin
- Enhanced the module to compatible with new SKU Type.

11/22/2019 9:30 AM Justin
- Added new note for the guidelines to add commission by sales or qty range.

11/25/2019 9:36 AM Justin
- Enhanced to highlight and sharpen the notes.
*}

06/29/2020 11:31 AM Sheila
- Updated button css.
{include file=header.tpl}

{literal}
<style>
a{
	cursor:pointer;
}

.mthly_comm_note{
	background-color:yellow; 
	font-weight:bold;
	color: red;
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

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var currency_symbol = '{$config.arms_currency.symbol}';

{literal}
function curtain_clicked(){
	$('div_sa_cc_dialog').hide();
	document.f_condition_type.reset();
	curtain(false);
}

var SA_COMMISSION_MODULE = {
	form_element: undefined,
	sac_id: undefined,
	date_item_id: undefined,
	sac_item_count: undefined,
	initialize: function(){
		this.form_element = document.f_a;
		if(!this.form_element){
			alert('Commission module failed to initialize.');
			return false;
		}
	
		this.sac_id = this.form_element['id'].value;

		new Draggable('div_sa_cc_dialog');

		// event to close commission without save
		$('title').observe('change', function(){
            uc(this);
		});
		
		// event for create new commission table
		$('sac_add_link').observe('click', function(){
            SA_COMMISSION_MODULE.add_commission();
		});

		// event to save commission
		$('save_btn').observe('click', function(){
			SA_COMMISSION_MODULE.save_commission();
		});

		// event to close commission without save
		$('close_btn').observe('click', function(){
			if(!confirm("Close without save?")) return;
            window.location = phpself;
		});

		SA_COMMISSION_CONDITION_DIALOG.initialize();
		//reset_autocomplete();
	},
	// event for open condition window
	table_appear: function(){
		/*if(type == "add"){
			$('bmsg').update("Complete below form and click Add");
			$('abtn').show();
			$('ebtn').hide();
			document.f_b.reset();
			document.f_b.id.value = 0;
			document.f_b.ticket_btn.onclick = function() { SALES_AGENT_MODULE.sa_ticket_activation(0, 1, 1); };
			document.f_b.ticket_btn.value = "Generate";
		}else{
			$('bmsg').update("Edit and click Update");
			$('abtn').hide();
			$('ebtn').show();
		}
		$('err_msg').update();
		hidediv('err_msg');*/

		Effect.toggle('div_sa_cc_dialog', 'blind', {
			duration: 0.5
		});
		center_div('div_sa_cc_dialog');
		curtain(true);
	},
	// even when click on "create new commission"
	add_commission: function(){
		var new_tr = "";
		if(!this.sac_item_count){
			sac_item_count = sac_item_count+1;
			this.sac_item_count = sac_item_count;
		}else this.sac_item_count += 1;
		
		var date_item_id = this.sac_item_count;
		var item = $('items').getElementsByClassName('sac_items');
		var item_count = item.length;
		
		$A(item).each(
			function (r,idx){
				if(r.style.display == "none"){
					item_count = item_count - 1;
				}
			}
		);

		if(item_count == 0){ // found did not have any commission setup before
			new_tr = $('temp_sac_row').cloneNode(true).innerHTML;
			new_tr = new_tr.replace(/__saci__id/g, date_item_id);
			new Insertion.Top($('items'), new_tr);
			Effect.Appear('sac_item_'+date_item_id);
		}else{ // found having commission items
			var prm = $(this.form_element).serialize();

			var params = {
				'a': 'ajax_add_commission',
				date_item_id: date_item_id,
				new_item: 1
			};

			prm += '&'+$H(params).toQueryString();
			new Ajax.Request(phpself, {
				parameters: prm,
				method: 'post',
				onComplete: function(msg){
					var str = msg.responseText.trim();
					var ret = {};
					var err_msg = '';

					ret = JSON.parse(str); // try decode json object
					if(ret['ok']==1 && ret['html']){ // success
						// append html
						new Insertion.Top('items', ret['html']);
						Effect.Appear('sac_item_'+date_item_id);
						return;
					}else{  // save failed
						if(ret['err_msg'])	err_msg = ret['err_msg'];
						else err_msg = str;
					}

					// prompt the error
					if(err_msg) alert(err_msg);	
				}
			});
		}
	},
	// even when click on "add commission item"
	toggle_condition_dialog: function(id){
		if(!this.form_element['selected_date_from['+id+']'].value.trim()){
			alert("Please select Date Start before add commission item.");
			this.form_element['selected_date_from_'+id].focus();
			return;
		}
		this.date_item_id = id;
		SA_COMMISSION_MODULE.table_appear();
		curtain(true);
	},
	// even when click on "delete commission"
	delete_commission: function(id){
		if(!confirm("Are you sure want to delete?")) return;
		if(id == 0 || id == ""){
			alert("Nothing to delete!");
			return;
		}

		Effect.DropOut("sac_item_"+id, {
			duration:0.5,
			afterFinish: function() {
				var THIS = this;
				var tmp_sac_items = $('items').getElementsByClassName('is_deleted_'+id);
				var tmp_sac_item_count = tmp_sac_items.length;

				$A(tmp_sac_items).each(
					function (r,idx){
						r.value = 1;
					}
				);

				//$("sac_item_"+id).className = "";
				var sac_items = $('items').getElementsByClassName('sac_items');
			}
		});
	},
	save_commission: function(){
		if(!this.form_element.title.value.trim()){
			alert("The Commission title is empty, please fill in.");
			return;
		}
		
		this.form_element.submit();
	},
	
	commission_method_changed: function(obj, saci_id){
		if(saci_id == undefined) return;
		else{
			if(obj.value == "Flat"){
				$("span_cm_range_field_"+saci_id).hide();
				$("span_cm_range_table_"+saci_id).hide();
			}else{
				var amt_label = "";
				if(obj.value == "Sales Range"){
					this.form_element['cm_range_from['+saci_id+']'].value = round(this.form_element['cm_range_from['+saci_id+']'], 2);
					this.form_element['cm_range_to['+saci_id+']'].value = round(this.form_element['cm_range_from['+saci_id+']'], 2);
					this.form_element['cm_range_from['+saci_id+']'].onchange = function(){ mf(this); };
					this.form_element['cm_range_to['+saci_id+']'].onchange = function(){ mf(this); };
					amt_label = currency_symbol;
				}else{
					this.form_element['cm_range_from['+saci_id+']'].onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
					this.form_element['cm_range_to['+saci_id+']'].onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
					this.form_element['cm_range_from['+saci_id+']'].value = float(round(this.form_element['cm_range_from['+saci_id+']'], global_qty_decimal_points));
					this.form_element['cm_range_to['+saci_id+']'].value = float(round(this.form_element['cm_range_from['+saci_id+']'], global_qty_decimal_points));
				}
			
				$("span_cm_range_field_"+saci_id).show();
				$("span_cm_range_table_"+saci_id).show();
				
				var item = $$('tr.sac_item_cm_range_row_'+saci_id);
				for(i=0;i<item.length;i++){
					var tmp_item_id = item[i].id.split("_")[6];
					var tmp_range_from = float(this.form_element['sac_item_cm_range_from['+saci_id+']['+tmp_item_id+']'].value);
					var tmp_range_to = float(this.form_element['sac_item_cm_range_to['+saci_id+']['+tmp_item_id+']'].value);

					var tmp_range_label = "";

					if(tmp_range_from > 0 && tmp_range_to > 0){
						tmp_range_label = "Between "+amt_label+tmp_range_from+" - "+amt_label+tmp_range_to;
					}else if(tmp_range_from > 0 && tmp_range_to == 0){
						tmp_range_label = "Start from "+amt_label+tmp_range_from;
					}else if(tmp_range_from == 0 && tmp_range_to > 0){
						tmp_range_label = "At most "+amt_label+tmp_range_to;
					}

					$('span_sac_item_cm_range_'+saci_id+'_'+tmp_item_id).update(tmp_range_label);
				}
			}
		}
	},
	add_commission_method_range: function(saci_id){
		var range_from = float(this.form_element['cm_range_from['+saci_id+']'].value);
		var range_to = float(this.form_element['cm_range_to['+saci_id+']'].value);
		var cm_value = this.form_element['cm_value['+saci_id+']'].value.trim();
		var commission_method = this.form_element['commission_method['+saci_id+']'].value;
		var amt_label = "";
		
		if(commission_method == "Sales Range"){
			amt_label = currency_symbol;
		}
		
		if((range_from == 0 && range_to == 0) || !cm_value) return;
		else if(range_from > 0 && range_to > 0 && range_to < range_from){
			alert(amt_label+round(range_to, 2)+" cannot be less than "+amt_label+round(range_from, 2));
			return;
		}

		if($('cm_range_no_data_'+saci_id) != undefined) $('cm_range_no_data_'+saci_id).remove();

		this.form_element['sac_item_cm_range_count['+saci_id+']'].value = float(this.form_element['sac_item_cm_range_count['+saci_id+']'].value)+1;
		var row_id = this.form_element['sac_item_cm_range_count['+saci_id+']'].value;

		var new_tr = $('temp_sac_item_cm_range_row').cloneNode(true).innerHTML;
		new_tr = new_tr.replace(/__saci__id/g, saci_id);
		new_tr = new_tr.replace(/__row__id/g, row_id);
		new Insertion.Bottom($('sac_item_cm_range_'+saci_id), new_tr);

		this.form_element['sac_item_cm_range_from['+saci_id+']['+row_id+']'].value = range_from;
		this.form_element['sac_item_cm_range_to['+saci_id+']['+row_id+']'].value = range_to;
		this.form_element['sac_item_cm_range_value['+saci_id+']['+row_id+']'].value = cm_value;

		var range_label = "";

		if(range_from > 0 && range_to > 0){
			range_label = "Between "+amt_label+range_from+" - "+amt_label+range_to;
		}else if(range_from > 0 && range_to == 0){
			range_label = "Start from "+amt_label+range_from;
		}else if(range_from == 0 && range_to > 0){
			range_label = "At most "+amt_label+range_to;
		}

		$('span_sac_item_cm_range_'+saci_id+'_'+row_id).update(range_label);
		$('span_sac_item_cm_value_'+saci_id+'_'+row_id).update(cm_value);
		this.form_element['cm_range_from['+saci_id+']'].value = "";
		this.form_element['cm_range_to['+saci_id+']'].value = "";
		this.form_element['cm_value['+saci_id+']'].value = "";
		this.form_element['cm_range_from['+saci_id+']'].focus();
	},
	delete_commission_method_range: function(saci_id, row_id){
		if(!confirm("Are you sure want to delete this commission method?")) return;
		if($('sac_item_cm_range_row_'+saci_id+'_'+row_id) != undefined) $('sac_item_cm_range_row_'+saci_id+'_'+row_id).remove();
		else return;
	},
	calendar_updated: function(cal){
		//alert(cal.params.inputField.value);
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
	toggle_commission_item_status: function(id){
		if(this.form_element['active['+id+']'].value == 1){
			$('img_sac_item_status_'+id).src = "ui/act.png";
			this.form_element['active['+id+']'].value = 0;
			$('span_sac_item_inactive_'+id).show();
		}else{
			$('img_sac_item_status_'+id).src = "ui/deact.png";
			this.form_element['active['+id+']'].value = 1;
			$('span_sac_item_inactive_'+id).hide();
		}
	},
	delete_commission_item: function(id, date_id){
		if(!confirm("Are you sure want to delete?")) return;
		var hide_tr = "sac_item_row_"+id;

		var THIS = this;
		var tmp_sac_items = $('items').getElementsByClassName('is_deleted_'+date_id);
		var tmp_sac_item_count = tmp_sac_items.length;

		$A(tmp_sac_items).each(
			function (r,idx){
				if(r.value == 1){
					tmp_sac_item_count-=1;
				}
			}
		);

		if(tmp_sac_item_count == 1){
			hide_tr = "sac_item_"+date_id;
		}

		Effect.DropOut(hide_tr, {
			duration:0.5,
			afterFinish: function() {
				THIS.form_element['is_deleted['+id+']'].value = 1;
			}
		});
	},
	check_cm_value: function(obj){
		//var cm_value = obj.value;
		var cm_value = obj.value.split("+");
		for(var i=0; i<cm_value.length; i++){
			if(cm_value[i].match(/%/)){ // check cm value by percentage
				var tmp_cm_value = round(cm_value[i].replace("%", ""), 2);
				if(tmp_cm_value > 100){
					tmp_cm_value = "100%";
				}else if(tmp_cm_value <= 0){
					tmp_cm_value = "0%";
				}else{
					tmp_cm_value = float(cm_value[i])+"%";
				}
			}else{ // otherwise, it just a normal amount
				tmp_cm_value = round(cm_value[i], 2);
			}
			
			if(i == 0){
				obj.value = tmp_cm_value;
			}else if(i > 0 && i <= 1){ // when reached 2nd value, add "+"
				obj.value += "+"+tmp_cm_value;
			}else if(i > 1) continue; // skip for all those more than 2nd value
		}
	}
}

SA_COMMISSION_CONDITION_DIALOG = {
	form_element: undefined,
	div_content: undefined,
	div_dialog: undefined,
	cat_autocomplete: undefined,
	selected_cat_id: 0,
	brand_autocomplete: undefined,
	selected_brand_id: '',
	vendor_autocomplete: undefined,
	selected_vendor_id: 0,
	initialize: function(){
		this.form_element = document.f_condition_type;
		if(!this.form_element){
			alert('Commission Dialog window failed to initialize.');
			return false;
		}
		// store the content div
		this.div_content = $('div_sa_cc_dialog_content');
		this.div_dialog = $('div_sa_cc_dialog');
		this.span_header = $('div_sa_cc_dialog_header');
		
		// initial autocomplete for category
		this.reset_category_autocomplete();
		$('inp_search_cat_autocomplete').observe('click', function(){
            SA_COMMISSION_CONDITION_DIALOG.reset_category_autocomplete();
		});
		
		// initial autocomplete for brand
		this.reset_brand_autocomplete();
		$('inp_search_brand_autocomplete').observe('click', function(){
            SA_COMMISSION_CONDITION_DIALOG.reset_brand_autocomplete();
		});

		// initial autocomplete for vendor
		this.reset_vendor_autocomplete();
		$('inp_search_vendor_autocomplete').observe('click', function(){
            SA_COMMISSION_CONDITION_DIALOG.reset_vendor_autocomplete();
		});
		
		// event when user click "add combination"
		$('inp_add_cat_brand_autocomplete').observe('click', function(){
            SA_COMMISSION_CONDITION_DIALOG.add_combination();
		});

		// event when user click "check all" for price type
		$('cpt_toggle').observe('click', function(){
            SA_COMMISSION_CONDITION_DIALOG.price_type_toggle(this);
		});
		
		reset_sku_autocomplete();
	},
	// event to open popup dialog
	open: function(group_id, item_id, use_for){
	    if(!group_id)   return false;
	    
	    // check target container first
        var tbl = $('tbody_promo_group_items-'+group_id);
        if(!tbl){
			alert('Invalid action, target group cannot be found.')
			return false;
		}

		$('tbody_condition_additional_filter').show();
		
		if(use_for) this.use_for = use_for;
		$(this.span_header).update(title);
		
		// reset additional filter status
		this.reset_additional_filter();
		// show dialog
		this.show();
		
		// clear all old search data
		this.reset_category_autocomplete();
		this.reset_brand_autocomplete();
		this.reset_vendor_autocomplete();
	},
	show: function(){
        // show dialog
		curtain(true);
		center_div($(this.div_dialog).show());
	},
	// reset category autocomplete
	reset_category_autocomplete: function(){
	    this.selected_cat_id = 0;
	    $('inp_search_cat_autocomplete').value = '';
	    
	    if(!this.cat_autocomplete){
            var params = $H({
				a: 'ajax_search_category',
				max_level: 10,
				no_findcat_expand: 1
			}).toQueryString();

	        this.cat_autocomplete = new Ajax.Autocompleter("inp_search_cat_autocomplete", "div_search_cat_autocomplete_choices", 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "category",
				indicator: 'span_sac_autocomplete_loading',
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					SA_COMMISSION_CONDITION_DIALOG.selected_cat_id = s[0];
				}
			});
		}
	},
	// reset brand autocomplete
	reset_brand_autocomplete: function(){
        this.selected_brand_id = '';
		$('inp_search_brand_autocomplete').value = '';
		
	    if(!this.brand_autocomplete){
            var params = $H({
				a: 'ajax_search_brand'
			}).toQueryString();

	        this.brand_autocomplete = new Ajax.Autocompleter("inp_search_brand_autocomplete", "div_search_brand_autocomplete_choices", 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "brand",
				indicator: 'span_sac_autocomplete_loading',
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					SA_COMMISSION_CONDITION_DIALOG.selected_brand_id = s[0];
				}
			});
		}
	},
	// reset vendor autocomplete
	reset_vendor_autocomplete: function(){
        this.selected_vendor_id = 0;
		$('inp_search_vendor_autocomplete').value = '';

	    if(!this.vendor_autocomplete){
            var params = $H({
				a: 'ajax_search_vendor'
			}).toQueryString();

	        this.vendor_autocomplete = new Ajax.Autocompleter("inp_search_vendor_autocomplete", "div_search_vendor_autocomplete_choices", 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "vendor",
				indicator: 'span_sac_autocomplete_loading',
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					SA_COMMISSION_CONDITION_DIALOG.selected_vendor_id = s[0];
				}
			});
		}
	},
	add_autocomplete: function(){
		if(!this.form_element.sku_item_id.value) return;
		this.ajax_add_commission_item("si");
	},
	// event when user click "add combination"
	add_combination: function(){
		var cat_id = int(this.selected_cat_id);
		var brand_id = this.selected_brand_id;
		
		if(!cat_id && brand_id==''){
			alert('Please search category or brand first.');
			return false;
		}
		
		// only category
		if(cat_id && brand_id==''){
			if(!confirm('Only category being selected, proceed anyway?'))  return false;
		}
		// only brand
		if(brand_id!='' && !cat_id){
			if(!confirm('Only brand being selected, proceed anyway?'))  return false;
		}

		this.ajax_add_commission_item("bc");
	},
	ajax_add_commission_item: function(type){
		var sac_id = SA_COMMISSION_MODULE.sac_id;
		var date_item_id = SA_COMMISSION_MODULE.date_item_id;

		/*var db_sac_id = 0;
		//if(document.f_a['sac_id['+sac_id+']'].value > 0) db_sac_id = document.f_a['sac_id['+sac_id+']'].value;

		if(!sac_id || sac_id == undefined){
			alert("Unable to add commission item due to invalid ID!");
			return;
		}*/
		var THIS = this;
		var prm = $(this.form_element).serialize();

		var params = {
			'a': 'ajax_add_commission_item',
			id: sac_id,
			category_id: this.selected_cat_id,
			brand_id: this.selected_brand_id,
			vendor_id: this.selected_vendor_id,
			condition_type: type,
			date_item_id: date_item_id
		};

		prm += '&'+$H(params).toQueryString();
		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']==1 && ret['html']){ // success
	                	// append html
						if($('no_data_'+date_item_id) != undefined) $('no_data_'+date_item_id).hide();
	                	new Insertion.Bottom('sac_item_'+date_item_id, ret['html']);
						Effect.Appear('sac_item_row_'+ret['sac_item_id']);
						$("span_cm_range_field_"+ret['sac_item_id']).hide();
						$("span_cm_range_table_"+ret['sac_item_id']).hide();
	                	// reset row num
	                    //SA_COMMISSION_MODULE.sac_reset_row_no(group_id);

	                    // close dialog
	                    curtain_clicked();
		                return;
					}else{  // save failed
						if(ret['err_msg'])	err_msg = ret['err_msg'];
						else err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);	
			}
		});
		this.selected_cat_id = 0;
		this.selected_brand_id = "";
		this.selected_vendor_id = 0;
		this.form_element.sku_item_id.value = "";
	},
	// function to reset additional filter dropdown
	reset_additional_filter: function(){
		this.form_element['disc_target_sku_type'].value = '';
		this.form_element['disc_target_price_type'].value = '';
		this.form_element['disc_target_price_range_from'].value = '';
		this.form_element['disc_target_price_range_to'].value = '';
	},
	price_type_toggle: function(obj){
		var price_type_list = $('div_sa_cc_dialog').getElementsByClassName("price_type_list");
		price_type_count = price_type_list.length;

		if(price_type_count > 0){
			$A(price_type_list).each(
				function (r,idx){
					if(obj.checked == true) r.checked = true;
					else r.checked = false;
				}
			);
		}
	},
	check_sku_type: function(obj){
		if(obj.value != "CONSIGN"){
			$('condition_price_type').style.display = "none";
			var price_type_list = $('div_sa_cc_dialog').getElementsByClassName("price_type_list");
			price_type_count = price_type_list.length;
			
			if(price_type_count > 0){
				$A(price_type_list).each(
					function (r,idx){
						r.checked = false;
					}
				);
			}
		}else{
			$('condition_price_type').style.display = "";
		}
	}
}

// SKU AUTOCOMPLETE DIALOG add
function add_autocomplete(){
	SA_COMMISSION_CONDITION_DIALOG.add_autocomplete();
}
</script>
{/literal}

{if $load_temp_table}
	<div id="temp_sac_row" class="temp_sac_row">
		<table id="sac_item___saci__id" class="sac_items" style="display:none;" width="100%">
			<tr>
				<td colspan="2">
					<b>Date Start: </b>
					<input size="10" type="text" name="selected_date_from[__saci__id]" id="selected_date_from___saci__id">
					<input type="hidden" name="date_to[__saci__id]" value="{$smarty.now|date_format:'%Y-%m-%d'}" id="date_to___saci__id">
					<img align="absmiddle" src="ui/calendar.gif" id="ds___saci__id" style="cursor: pointer;" title="Select Date">
				</td>
				<td align="right" colspan="2">
					<a onclick="SA_COMMISSION_MODULE.toggle_condition_dialog(__saci__id);">Add Commission Item <img src="ui/icons/money_add.png" title="Add Commission Item" width="15" border="0"></a> &nbsp;&nbsp;&nbsp;
					<a onclick="SA_COMMISSION_MODULE.delete_commission(__saci__id);">Delete Commission <img src="ui/del.png" title="Delete Commission" width="15" border="0"></a>
				</td>
			</tr>
			<tr>
				<th bgcolor="{#TB_CORNER#}" width="6%">&nbsp;</th>
				<th bgcolor="{#TB_COLHEADER#}" width="34%">Condition</th>
				<th bgcolor="{#TB_COLHEADER#}" width="34%">Additional Filter</th>
				<th bgcolor="{#TB_COLHEADER#}" width="26%">Commission Method</th>
			</tr>
			<tr id="no_data___saci__id">
				<td colspan="4" align="center" bgcolor="{#TB_CORNER#}">No Data</td>
			</tr>
			{literal}
			<script type="text/javascript">
				Calendar.setup({
					inputField     :    "selected_date_from___saci__id",     // id of the input field
					ifFormat       :    "%Y-%m-%d",      // format of the input field
					button         :    "ds___saci__id",  // trigger for the calendar (button ID)
					align          :    "Bl",           // alignment (defaults to "Bl")
					singleClick    :    true
				});
			</script>
			{/literal}
		</table>
	</div>

	<div id="temp_sac_item_cm_range_row" class="temp_sac_item_cm_range_row" style="display:none;">
		<table width="100%">
			<tr class="sac_item_cm_range_row___saci__id" id="sac_item_cm_range_row___saci__id___row__id">
				<td bgcolor="#CACACA" width="5%">
					<img src="/ui/del.png" width="15" align="absmiddle" onclick="SA_COMMISSION_MODULE.delete_commission_method_range(__saci__id, __row__id);" class="clickable"/>
				</td>
				<td bgcolor="#CACACA" width="65%">
					<span id="span_sac_item_cm_range___saci__id___row__id"></span>
					<input type="hidden" name="sac_item_cm_range_from[__saci__id][__row__id]">
					<input type="hidden" name="sac_item_cm_range_to[__saci__id][__row__id]">
				</td>
				<td bgcolor="#CACACA" width="30%" align="right">
					<span id="span_sac_item_cm_value___saci__id___row__id"></span>
					<input type="hidden" name="sac_item_cm_range_value[__saci__id][__row__id]">
				</td>
			</tr>
		</table>
	</div>
{/if}

<h1>Commission ({if is_new_id($form.id)}New{else}#{$form.id|string_format:"%05d"}{if !$form.active} - Inactive{/if}{/if})</h1>
<!-- commission condition dialog -->
<div id="div_sa_cc_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:650px;height:350px;display:none;border:2px solid #1569C7;background-color:#1569C7;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sa_cc_dialog_header" style="border:2px ridge #1569C7;color:white;background-color:#1569C7;padding:2px;cursor:default;"><span style="float:left;" id="span_sa_cc_dialog_header">Choose Condition</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sa_cc_dialog_content" style="padding:2px;">
		{include file='masterfile_sa_commission.open.condition_dialog.tpl'}
	</div>
</div>

{if $err.mst}
<div id=err><div class=errmsg><ul>
{foreach from=$err.mst item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name="f_a" method="post" onsubmit="return false;">
	<input type="hidden" name="a" value="save_commission">
	<input type="hidden" name="id" value="{$form.id}">
	<div class="stdframe">
	<h4>General Information</h4>
	<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<td><b>Branch</b></td>
			<td>
				{$form.branch_code|default:$BRANCH_CODE}
				<input type="hidden" name="branch_id" id="branch_id" value="{$form.branch_id}">
			</td>
		</tr>
		<tr>
			<td><b>Title</b></td>
			<td><input type="text" id="title" name="title" maxlength="200" size="80" value="{$form.title}" class="required" title="Title" /></td>
		</tr>
		<tr>
			<td><b>Owner</b></td>
			<td>
				{$form.username|default:$sessioninfo.u}
			</td>
		</tr>
	</table>
	</div>
	<br />
	<div>
		<a id="sac_add_link" style="cursor:pointer;"><img src="ui/icons/calculator_add.png" title="Create New Commission" align="absmiddle" border="0"> Create New Commission</a><br /><br />
		<span class="mthly_comm_note">Note: Commission by Sales or Qty range must set from beginning of the month<br />
	</div>
	<br />
	<div id="items" class="stdframe">
		{include file="masterfile_sa_commission.open.item.tpl"}
	</div>
	<p align="center">
		<input class="btn btn-success" type=button id="save_btn" name="save_btn" value="Save">
		<input class="btn btn-error" type=button id="close_btn" name="close_btn" value="Close">
	</p>
</form>

<script>
var sac_items = $('items').getElementsByClassName('sac_items');
sac_item_count = sac_items.length;
SA_COMMISSION_MODULE.initialize();
</script>

{include file=footer.tpl}