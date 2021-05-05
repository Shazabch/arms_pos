{*
1/9/2015 12:26 PM Justin
- Enhanced to have privilege checking.

3/4/2015 4:29 PM Andy
- Change to limit user can only choose the level 3 category.
- Add some legend to let user how it filter the items.

3/16/2015 11:24 AM Justin
- Modified to indicate vendor, brand and SKU Type is optional.
- Added "*" for category as mandatory field.

3/20/2015 2:36 PM Justin
- Enhanced to have Download items into CSV format feature.

3/21/2015 1:51 PM Justin
- Enhanced to show download CSV button while reached maximum 5,000 items.
- Enhanced to always set batch price change as approved once it is saved.
- Enhanced to limit the query for picking up maximum 5,000 items.

3/24/2015 9:57 AM Justin
- Enhanced to have mprice selection.

3/27/2015 2:48 PM Justin
- Enhanced to have normal price choice on mprice.

3/28/2015 9:32 AM Andy
- Move download button to always visible.

3/30/2015 2:14 PM Justin
- Enhanced to show error msg when reached maximum items.

5/17/2018 11:03 AM Andy
- Enhanced to have calculation method add or deduct.

5/18/2018 1:37 PM Andy
- Fixed arms user cant see the add / deduct option.

06/26/2020 Sheila 02:26 PM
- Updated button css.
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}

.td_label_top{
	padding-top:3;
	vertical-align:top;
}

.div_multi_select{
	border:1px solid grey;
	overflow:auto;
	overflow-x:hidden;
	display: inline-block;
	padding: 2px;
}

.calendar{
	z-Index: 100000 !important;
}

.span_sku_count{
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

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var curr_branch_code = '{$BRANCH_CODE}';

{literal}
function curtain_clicked(){
	$('div_cp_dialog').hide();
	curtain(false);
}

var GST_PRICE_WIZARD_MODULE = {
	form_element: undefined,
	initialize : function(){
		form_element = document.f_a;
		var THIS = this;
		// event when user click "search"
		$('search_btn').observe('click', function(){
            THIS.search_validate();
		});
		
		// event when user click "save"
		$('save_btn').observe('click', function(){
			THIS.save(this);
		});
		
		// event when user click "cancel"
		$('cancel_btn').observe('click', function(){
			curtain_clicked();
		});
		
		// event when user click "download"
		$('dl_btn').observe('click', function(){
			THIS.download_csv();
		});

		// event when user click "all_mprice" checkbox
		$('all_mprice').observe('click', function(){
			THIS.toggle_all_mprice(this);
		});
				
		if(curr_branch_code == "HQ"){
			// event to toggle set date by branch
			$('date_by_branch').observe('click', function(){
				THIS.toggle_date(this);
			});
		
			// event to toggle branches
			$('toggle_branches').observe('click', function(){
				THIS.toggle_branches_chx(this);
			});
		}
		
		new Draggable('div_cp_dialog',{ handle: 'div_cp_dialog_header'});

		var curr_date = new Date();
		var curr_year = curr_date.getFullYear();
		var curr_mth = curr_date.getMonth();
		var curr_day = curr_date.getDate();
		allowed_date = new Date(curr_year, curr_mth, curr_day);
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
	},
	search_check: function(type){
		var cat_selected = false;
		var cat_length = form_element['cat_id_list[]'].length;
		var cat_id_list = [];
		for(var i=0; i<cat_length; i++){
			if(form_element['cat_id_list[]'][i].checked && !form_element['cat_id_list[]'][i].disabled){
				cat_selected = true;
				cat_id_list.push(form_element['cat_id_list[]'][i].value);
			}
		}
		
		if(!cat_selected){
			alert("Please select a category.");
			return false;
		}
		
		var mprice_list = $('udiv').getElementsByClassName("mprice_list");
		mprice_list_count = mprice_list.length;
		mprice_is_checked = false;
		if(mprice_list_count > 0){
			$A(mprice_list).each(
				function (tr,idx){
					if(tr.checked == true){
						mprice_is_checked = true;
						throw $break;
					}
				}
			);
		}
		
		if(!mprice_is_checked){
			alert("Please select at least one or more MPrice");
			return false;
		}
		
		if(type == 'download'){
			var total_sku_count = 0;
			for(var i=0,len=cat_id_list.length; i<len; i++){
				var tmp_cat_id = cat_id_list[i];
				total_sku_count += int(form_element['sku_count['+tmp_cat_id+']'].value);
			}
			if(total_sku_count > 5000){
				if(!confirm('You have selected '+total_sku_count+' sku to be export. Are you sure to continue?\n\nTips: You can select smaller category in order to avoid the system memory limit.')){
					return false;
				}
			}
		}
		
		return true;
	},		
	search_validate : function(){
		if(!this.search_check())	return false;

		this.search();
	},
	
	search : function(){
		form_element['a'].value = "update";
		var prm = $(form_element).serialize();

		var params = {
		    a: 'ajax_search_items'
		};
		prm += '&'+$H(params).toQueryString();
		
		$("div_items").update("Loading... Please wait");
		$("div_csv_download").hide();

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] == 1){ // success
						if(ret['reached_maximum_items'] == 1){
							$('div_csv_download').show();
							$('div_items').update();
						}else{
							$('div_csv_download').hide();
							$("div_items").update(ret['html']);
						}
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				
				// prompt the error
			    if(err_msg){
					alert("You have encountered below errors:\n"+err_msg);
					$("div_items").update("");
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},

	save : function(){
		/*this.form_element = document.f_a;
		var prm = $(this.form_element).serialize();

		var params = {
		    a: 'update'
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
	                if(ret['ok']){ // success
						alert("Save successfully.");
						document.f_a['id'].value = ret['id'];
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				
				// prompt the error
			    if(err_msg) alert("You have encountered below errors:\n"+err_msg);
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});*/
		
		document.f_a.submit();
	},
	
	toggle_settings_window : function(type){
		var form_ele = document.f_b;
		if(type == "future_price"){
			var future_price_rows = $('div_cp_content').getElementsByClassName("future_price");
			div_future_row_count = future_price_rows.length;
			$('tr_bpc_warning').show();
			
			if(div_future_row_count > 0){
				$A(future_price_rows).each(
					function (tr,idx){
						tr.style.display = "";
					}
				);
			}
			
			var date_rows = $('div_cp_content').getElementsByClassName("times");
			date_row_count = date_rows.length;
			
			if(date_row_count > 0){
				$A(date_rows).each(
					function (tr,idx){
						tr.style.display = "";
						if(form_ele['date_by_branch'].checked == true) tr.show();
						else tr.hide();
					}
				);
			}
		}else{
			var future_price_rows = $('div_cp_content').getElementsByClassName("future_price");
			div_future_row_count = future_price_rows.length;
			$('tr_bpc_warning').hide();
			
			if(div_future_row_count > 0){
				$A(future_price_rows).each(
					function (tr,idx){
						tr.style.display = "none";
					}
				);
			}
			
			var date_rows = $('div_cp_content').getElementsByClassName("times");
			date_row_count = date_rows.length;
			
			if(date_row_count > 0){
				$A(date_rows).each(
					function (tr,idx){
						tr.hide();
					}
				);
			}
		}
		$("div_cp_dialog").show();
		center_div("div_cp_dialog");
		form_ele['type'].value = type;
		curtain(true);
	},
	
	calculate_gst : function(id, type, obj){
		var form_ele = document.f_b;
		var inclusive_tax = form_ele["inclusive_tax["+id+"]"].value;
		var gst_rate = float(form_ele["gst_rate["+id+"]"].value);
		if(inclusive_tax == "inherit" || gst_rate <= 0) return;
		
		if(type == "normal"){
			// calculate selling price after/before GST
			if(obj != undefined && obj.name == "gst_price["+id+"]["+type+"]"){ // found user changing GST selling price
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

				form_ele["price["+id+"]["+type+"]"].value = round(selling_price, 2);
			}else{
				var selling_price = float(form_ele["price["+id+"]["+type+"]"].value);
				
				if (inclusive_tax=='yes') {
					var gst_selling_price=(selling_price*100)/(100+gst_rate);
					var gst_amt=float(gst_selling_price) * gst_rate / 100;
				}
				else{
					var gst_amt=float(selling_price) * gst_rate / 100;
					var gst_selling_price=float(selling_price+gst_amt);
				}
				
				form_ele["gst_price["+id+"]["+type+"]"].value=round(gst_selling_price,2);
			}
			
			form_ele["gst_amount["+id+"]["+type+"]"].value = round(gst_amt, 2);
			
			if(confirm("Do you want to copy this price over to all different Mprice for this row?")){
				var mprice_rows = $('div_items').getElementsByClassName("mprice_"+id);
				div_mprice_row_count = mprice_rows.length;
				
				if(div_mprice_row_count > 0){
					$A(mprice_rows).each(
						function (inp,idx){
							inp.value = form_ele["price["+id+"]["+type+"]"].value;
						}
					);
				}
			}
		}else{
			// calculate selling price after/before GST
			if(obj != undefined && obj.name == "gst_mprice["+id+"]["+type+"]"){ // found user changing GST selling price
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

				form_ele["mprice["+id+"]["+type+"]"].value = round(selling_price, 2);
			}else{
				var selling_price = float(form_ele["mprice["+id+"]["+type+"]"].value);
				
				if (inclusive_tax=='yes') {
					var gst_selling_price=(selling_price*100)/(100+gst_rate);
					var gst_amt=float(gst_selling_price) * gst_rate / 100;
				}
				else{
					var gst_amt=float(selling_price) * gst_rate / 100;
					var gst_selling_price=float(selling_price+gst_amt);
				}
				
				form_ele["gst_mprice["+id+"]["+type+"]"].value=round(gst_selling_price,2);
			}
			
			form_ele["gst_amount["+id+"]["+type+"]"].value = round(gst_amt, 2);
		}
	},
	
	toggle_branches_chx: function (obj){
		var branch_rows = $('div_cp_content').getElementsByClassName("effective_branch");
		branch_row_count = branch_rows.length;
		
		if(branch_row_count > 0){
			$A(branch_rows).each(
				function (tr,idx){
					if(obj.checked == true) tr.checked = true;
					else tr.checked = false;
				}
			);
		}
	},
	
	toggle_date: function (obj){
		var form_ele = document.f_b;
		if(obj.checked == true){
			form_ele['hour'].disabled = true;
			form_ele['minute'].disabled = true;
			form_ele['date'].disabled = true;
			$('ds1').hide();
		}else{
			form_ele['hour'].disabled = false;
			form_ele['minute'].disabled = false;
			form_ele['date'].disabled = false;
			$('ds1').show();
		}
		
		var date_rows = $('div_cp_content').getElementsByClassName("times");
		date_row_count = date_rows.length;
		
		if(date_row_count > 0){
			$A(date_rows).each(
				function (tr,idx){
					if(obj.checked == true) tr.show();
					else tr.hide();
				}
			);
		}
	},	
	save_validate: function (){
		var form_ele = document.f_b;
		
		if(curr_branch_code == "HQ"){
			var branch_rows = $('div_cp_content').getElementsByClassName("effective_branch");
			branch_row_count = branch_rows.length;
			var is_branch_checked = false;
			var empty_date_by_branch = false;
			
			if(branch_row_count > 0){
				$A(branch_rows).each(
					function (tr,idx){
						if(tr.checked == true){
							is_branch_checked = true;
							if(!form_ele['branch_date['+tr.value+']'].value){
								empty_date_by_branch = true;
							}
						}
					}
				);
			}
			
			if(!is_branch_checked){
				alert("Please select a branch.");
				return false;
			}
			
			if(form_ele['type'].value == "future_price"){
				if(form_ele['date_by_branch'].checked == true){		
					if(empty_date_by_branch){
						alert("Please select date for all branches.");
						return false;
					}
				}else{
					if(!form_ele['date'].value){
						alert("Please select a Date.");
						return false;
					}
				}
			}
		}
		
		return true;
	},
	
	save: function(obj){
		if(this.save_validate() == false) return false;
		document.f_b.submit();
	},
	
	download_csv: function(){
		if(!this.search_check('download'))	return false;
		
		//this.form_element = document.f_a;
		/*if(form_element['category_id'].value == 0){
			alert("Please select a category.");
			return false;
		}*/
		
		form_element['a'].value = "export_csv";
		
		document.f_a.submit();
	},
	
	toggle_all_mprice: function(obj){
		if(obj == undefined) return;
		
		var mprice_list = $('udiv').getElementsByClassName("mprice_list");
		mprice_list_count = mprice_list.length;
		if(mprice_list_count > 0){
			$A(mprice_list).each(
				function (tr,idx){
					if(obj.checked == true){
						tr.checked = true;
					}else{
						tr.checked = false;
					}
				}
			);
		}
	},
	// function when user expand / collapse category tree
	toggle_cat_child: function(cat_id, show){
		var li_list = $$("#ul_cat li.child_cat-of-"+cat_id);
		var check_result = -1;
		if(show != undefined){
			check_result = show == true ? 1 : 0;
		}
		for(var i=0,len=li_list.length; i<len; i++){
			if(check_result == -1){
				check_result = $(li_list[i]).style.display == 'none' ? 1: 0;
			}
			if(check_result == 1){
				$(li_list[i]).show();
			}else{
				$(li_list[i]).hide();
				
				// hide all the child too
				var tmp_cat_id = $(li_list[i]).id.split('-')[1];
				this.toggle_cat_child(tmp_cat_id, false);
			}
		}
	},
	// function when user tick / untick category checkbox
	cat_selected_changed: function(cat_id){
		var c = $('inp_cat_id_list-'+cat_id).checked;
		
		var li_list = $$("#ul_cat li.child_cat-of-"+cat_id);
		for(var i=0,len=li_list.length; i<len; i++){
			var tmp_cat_id = $(li_list[i]).id.split('-')[1];
			
			$('inp_cat_id_list-'+tmp_cat_id).checked = c;
			$('inp_cat_id_list-'+tmp_cat_id).disabled = c;
			
			this.cat_selected_changed(tmp_cat_id);
		}
	}
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

{if $err}
<div id="err"><div class="errmsg"><ul>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

{if $smarty.request.save}
<img src="ui/approved.png" title="Saved GST information" border="0"> 
<b>
	{if $smarty.request.type eq "future_price"}
		Batch Price Change have been saved and approved
		{if $smarty.request.fp_id > 0}
			as #{$smarty.request.fp_id}, <a href="masterfile_sku_items.future_price.php?a=open&id={$smarty.request.fp_id}&branch_id={$smarty.request.bid}" target="_blank">click here</a> to review.
		{else}
			not saved due to no new price change found
		{/if}
	{else}
		Price Change saved.
	{/if}</b>
<br /><br />
{/if}

<form method="post" name="f_a" onSubmit="return GST_PRICE_WIZARD_MODULE.search_validate();">
<div id="udiv" class="stdframe">
	<input type="hidden" name="a" value="update">
	<input type="hidden" name="active" value="{$form.active}">
	<table id="gst_settings" width="100%">
		<tr>
			<td valign="top" style="padding-top:5;"><b>Category</b></td>
			<td colspan="3">
				{*
				<select name="category_id">
					<option value="">Please Select</option>
					{foreach from=$dept_cat_list key=dept_id item=dept}
						<optgroup label="{$dept.line_name} > {$dept.dept_name}">
							{foreach from=$dept.cat_list key=cat_id item=cat}
								<option value="{$cat_id}" {if $form.category_id eq $cat_id}selected {/if}>{$cat.name}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
				<font color="red" size="+1">*</font>
				*}
				
				<div style="border:1px solid black;background-color:#fff;height:200px;width:100%;overflow-y:auto;">
					<ul style="list-style:none;" id="ul_cat">
						
						{foreach from=$cat_data.tree.0 item=line_id}
							{assign var=line value=$cat_data.list.$line_id}
							<li id="li_cat-{$line_id}"> 
								<input type="checkbox" name="cat_id_list[]" value="{$line_id}" onChange="GST_PRICE_WIZARD_MODULE.cat_selected_changed('{$line_id}');" id="inp_cat_id_list-{$line_id}" />
								<input type="hidden" name="cat_level_info[{$line_id}]" value="{$line.level}" />
								<input type="hidden" name="sku_count[{$line_id}]" value="{$line.sku_count}" />
								{if $line.code}[{$line.code}]{/if}
								<span class="clickable link" onClick="GST_PRICE_WIZARD_MODULE.toggle_cat_child('{$line_id}');">
									{$line.description}
								</span>
								<span class="span_sku_count">({$line.sku_count|default:0} sku)</span>
							</li>
							{if $cat_data.tree.$line_id}
								{foreach from=$cat_data.tree.$line_id item=dept_id}
									{assign var=dept value=$cat_data.list.$dept_id}
									<li style="display:none;" id="li_cat-{$dept_id}" class="child_cat-of-{$line_id}"> 
										{section loop=$dept.level name=s start=1}
											<img src="ui/pixel.gif" width="20"  />
										{/section}
										<input type="checkbox" name="cat_id_list[]" value="{$dept_id}" onChange="GST_PRICE_WIZARD_MODULE.cat_selected_changed('{$dept_id}');" id="inp_cat_id_list-{$dept_id}" />
										<input type="hidden" name="cat_level_info[{$dept_id}]" value="{$dept.level}" />
										<input type="hidden" name="sku_count[{$dept_id}]" value="{$dept.sku_count}" />
										{if $dept.code}[{$dept.code}]{/if}
										<span {if $cat_data.tree.$dept_id}class="clickable link" onClick="GST_PRICE_WIZARD_MODULE.toggle_cat_child('{$dept_id}');"{/if}>
											{$dept.description}
										</span>
										<span class="span_sku_count">({$dept.sku_count|default:0} sku)</span>
									</li>
									{if $cat_data.tree.$dept_id}
										{foreach from=$cat_data.tree.$dept_id item=cat_id}
											{assign var=cat value=$cat_data.list.$cat_id}
											<li style="display:none;" id="li_cat-{$cat_id}" class="child_cat-of-{$dept_id}"> 
												{section loop=$cat.level name=s start=1}
													<img src="ui/pixel.gif" width="20"  />
												{/section}
												<input type="checkbox" name="cat_id_list[]" value="{$cat_id}" onChange="GST_PRICE_WIZARD_MODULE.cat_selected_changed('{$cat_id}');" id="inp_cat_id_list-{$cat_id}" />
												<input type="hidden" name="cat_level_info[{$cat_id}]" value="{$cat.level}" />
												<input type="hidden" name="sku_count[{$cat_id}]" value="{$cat.sku_count}" />
												{if $cat.code}[{$cat.code}]{/if}
												{$cat.description}
												<span class="span_sku_count">({$cat.sku_count|default:0} sku)</span>
											</li>
										{/foreach}
									{/if}
								{/foreach}
							{/if}
						{/foreach}
					<ul>
				</div>
			</td>
		</tr>
		<tr>
			<td><b>Vendor</b></td>
			<td colspan="3">
				<select name="vendor_id">
					<option value="" {if !$form.vendor_id}selected{/if}>- All -</option>
					{foreach from=$vendor_list key=row item=r}
						<option value="{$r.id}" {if $form.vendor_id eq $r.id}selected{/if}>{$r.code} - {$r.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Brand</b></td>
			<td>
				<select name="brand_id">
					<option value="" {if !$form.brand_id}selected{/if}>- All -</option>
					<option value=0 {if $form.brand_id eq '0'}selected{/if}>UN-BRANDED</option>
					{foreach from=$brand_list key=row item=r}
						<option value="{$r.id}" {if $form.brand_id eq $r.id}selected{/if}>{$r.description}</option>
					{/foreach}
					{if $brand_groups}
						<optgroup label="Brand Group">
							{foreach from=$brand_groups key=bgk item=bgv}
							<option value="{$bgk}" {if $smarty.request.brand_id eq $bgk}selected{/if}>{$bgv}</option>
							{/foreach}
						</optgroup>
					{/if}
				</select>
			</td>
			<td><b>SKU Type</b></td>
			<td>
				<select name="sku_type">
					<option value="" {if !$form.sku_type}selected{/if}>- All -</option>
					{foreach from=$st_list key=row item=r}
						<option value="{$r.code}" {if $form.sku_type eq $r.code}selected{/if}>{$r.description}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		
		
		<tr style="{if !$is_arms_user}display:none;{/if}">
			<td><b>Calculation Method</b></td>
			<td>
				<select name="calculate_method">
					<option value="add">Add</option>
					<option value="deduct" {if $smarty.request.calculate_method eq 'deduct'}selected {/if}>Deduct</option>
				</select>
			</td>
		</tr>
		
		{if $config.sku_multiple_selling_price}
			<tr>
				<td><b>MPrice</b></td>
				<td colspan="3">
					<input type="checkbox" id="all_mprice" value="1" checked> All
					<input type="checkbox" name="mprice[normal]" value="normal" class="mprice_list" checked> NORMAL
					{foreach from=$config.sku_multiple_selling_price item=s}
						<input type="checkbox" name="mprice[{$s}]" value="{$s}" class="mprice_list" checked> {$s|strtoupper}
					{/foreach}
				</td>
			</tr>
		{/if}
	</table>
	<!-- bottom -->
	<div align="left" style="padding-top:10;">
		<input class="btn btn-primary" type="button" value="Search" id="search_btn"> 
		<input class="btn btn-primary" type="button" value="Download" id="dl_btn">
	</div>
	
	<p>
		<ul>
			<li>Only sku with inclusive tax = 'yes' will be show.</li>
			<li>If the line/department/category/sku gst rate is zero, the sku will not be show.</li>
			{if $MAXIMUM_ITEMS}
				<li>Please use "Download" (CSV Format) if the department contains more than {$MAXIMUM_ITEMS|number_format:0} items and upload it from <b><a href="masterfile_sku_items.future_price.php?a=open_csv" target="_blank">Batch Price Change</a></b> module.</li>
			{/if}
		</ul>
	</p>
</div>

<br />

<div id="div_csv_download" style="display:none; font-weight:bold; color:red;">
	Reached maximum {$MAXIMUM_ITEMS|number_format:0} items, please download and import from Batch Price Change manually.
</div>
</form>

<form method="post" name="f_b" onSubmit="return GST_PRICE_WIZARD_MODULE.save_validate();">
<input type="hidden" name="a" value="save" />
<input type="hidden" name="type" value="">
<!-- generate batch price change dialog -->
<div id="div_cp_dialog" class="curtain_popup" style="position:absolute;z-index:10000;display:none;border:2px solid #1569C7;background-color:#1569C7;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_cp_dialog_header" style="border:2px ridge #1569C7;color:white;background-color:#1569C7;padding:2px;cursor:default;"><span style="float:left;" id="span_cp_dialog_header">Menu</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_cp_content" style="padding:2px;">
		<table>
			{if $BRANCH_CODE eq 'HQ'}
				<tr class="future_price">
					<td><b>Set Date by Branch</b></td>
					<td>
						<input type="checkbox" name="date_by_branch" id="date_by_branch" value="1" {if $form.date_by_branch}checked{/if} {if $form.approval_screen}onclick="toggle_date(this);"{/if} />
					</td>
				</tr>
			{/if}
			<tr class="future_price">
				<td><b>Date <span><img src="ui/rq.gif" align="absbottom" width="8" title="Required Field"></span></b></td>
				<td>
					<input size="10" type="text" name="date" id="date" value="{$form.date|ifzero:''}" class="date" readonly>
					{if !$readonly || $form.approval_screen}
						<img align="absmiddle" src="ui/calendar.gif" id="ds1" style="cursor: pointer; {if $form.date_by_branch}display:none{/if}" title="Select Date">&nbsp;
					{/if}
					H: 
					<select name="hour" {if $form.date_by_branch}disabled{/if}>
						{section name=hr loop=24 start=0}
							{assign var=hour value=$smarty.section.hr.iteration-1}
							<option value="{$hour}" {if $form.hour eq $hour}selected{/if}>{$hour}</option>
						{/section}
					</select>
					M: 
					<select name="minute" {if $form.date_by_branch}disabled{/if}>
						<option value="0" {if !$form.minute}selected{/if}>0</option>
						<option value="30" {if $form.minute eq "30"}selected{/if}>30</option>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top"><b>Branch <span><img src="ui/rq.gif" align="absbottom" width="8" title="Required Field"></span></b></td>
				<td>
					{if $BRANCH_CODE eq 'HQ'}
					<!-- Branch -->
						<div class="div_multi_select" id="div_multi_select">
							<ul style="list-style:none;">
								<table width="100%" border="0" cellspacing="0">
									<tr>
										<td><input type="checkbox" id="toggle_branches" /></td>
										<td colspan="3"><b>All</b></td>
									</tr>
									{foreach from=$branch_list key=bid item=r}
										{if !$form.approval_screen || ($form.approval_screen && $form.effective_branches.$bid)}
											<tr>
												<td>
													<input type="checkbox" name="effective_branches[{$bid}]" value="{$bid}" {if $form.effective_branches.$bid}checked{/if} class="effective_branch" />
												</td>
												<td>{$r.code}</td>
												<td class="times" {if !$form.date_by_branch}style="display:none;"{/if}>
													<input size="10" type="text" name="branch_date[{$bid}]" id="branch_date_{$bid}" value="{$form.effective_branches.$bid.date|ifzero:''}" class="date" readonly>
													{if !$readonly || ($form.approval_screen && $form.effective_branches.$bid)}
														<img align="absmiddle" src="ui/calendar.gif" id="ds1_{$bid}" style="cursor: pointer;" title="Select Date">&nbsp;
													{/if}
												</td>
												<td class="times" {if !$form.date_by_branch}style="display:none;"{/if} nowrap>
													H: 
													<select name="branch_hour[{$bid}]">
														{section name=hr loop=24 start=0}
															{assign var=hour value=$smarty.section.hr.iteration-1}
															<option value="{$hour}" {if $form.effective_branches.$bid.hour eq $hour}selected{/if}>{$hour}</option>
														{/section}
													</select>
												</td>
												<td class="times" {if !$form.date_by_branch}style="display:none;"{/if} nowrap>
													M: 
													<select name="branch_minute[{$bid}]">
														<option value="0" {if !$form.effective_branches.$bid.minute}selected{/if}>0</option>
														<option value="30" {if $form.effective_branches.$bid.minute eq "30"}selected{/if}>30</option>
													</select>
												</td>
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
						<input type="hidden" name="effective_branches[{$sessioninfo.branch_id}]" value="{$sessioninfo.branch_id}" />
					{/if}
				</td>
			</tr>
			<tr align="center" id="tr_bpc_warning" style="display:none;">
				<td colspan="2" style="color:red;"><b>* This will become approved Batch Price Change once it is saved</b></td>
			</tr>
			<tr>
				<td align="center" colspan="2" style="padding-top:10;">
					<input type="button" id="save_btn" value="Save" />
					<input type="button" id="cancel_btn" value="Cancel" />
				</td>
			</tr>
		</table>
	</div>
</div>
<div id="div_items"></div>
</form>

<script>
GST_PRICE_WIZARD_MODULE.initialize();
</script>

{include file=footer.tpl}
