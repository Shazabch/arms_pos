{*
3/22/2019 11:06 AM Andy
- Added "Advanced Add" feature.
*}
{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{include file='shared_sku_photo.script.tpl'}

<style>
{literal}
#tbl_item_list tr.tr_item:nth-child(even){
	background-color: #ffc;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var do_request_no_expected_delivery_date = int('{$config.do_request_no_expected_delivery_date}');
var do_request_default_expected_delivery_date_extend_day = int('{$config.do_request_default_expected_delivery_date_extend_day}');
var do_request_maximum_expect_delivery_date_day = int('{$config.do_request_maximum_expect_delivery_date_day}');
var do_request_expected_delivery_date_times = '{$do_request_expected_delivery_date_times}';
var do_request_expected_delivery_date_days = '{$do_request_expected_delivery_date_days}';

{literal}

var allowed_max_expect_do_date;
var allowed_min_expect_do_date;

if(do_request_maximum_expect_delivery_date_day>0){
	allowed_max_expect_do_date = new Date();
	allowed_max_expect_do_date.setTime(allowed_max_expect_do_date.getTime() + 3600000*24*do_request_maximum_expect_delivery_date_day);
}

var ADVANCED_ADD = {
	f: undefined,
	f_search: undefined,
	initialise: function(){
		this.f_search = document.f_search;
		this.f = document.f_a;
		
		if(!do_request_no_expected_delivery_date){
			Calendar.setup({
				inputField     :    "inp_expect_do_date",
				ifFormat       :    "%Y-%m-%d",
				button         :    "img_expect_do_date",
				align          :    "Bl",
				singleClick    :    true,
				dateStatusFunc :    function (date) { // disable those date <= today
									if(allowed_max_expect_do_date || allowed_min_expect_do_date){
										var val = false;
										if((date.getTime() > allowed_max_expect_do_date.getTime())){
											val = true;
										}

										if(allowed_min_expect_do_date && date.getTime() < allowed_min_expect_do_date.getTime()){
											val = true;
										}
										return val;
									}
									return false;	// always allow
	                            }
			});
		}
		
		// default select all category
		$('all_category').checked = true;
		$('all_category').onchange();
		
		Calendar.setup({
			inputField     :    "inp_sku_added_date_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_sku_added_date_from",
			align          :    "Bl",
			singleClick    :    true,
		});
		
		Calendar.setup({
			inputField     :    "inp_sku_added_date_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_sku_added_date_to",
			align          :    "Bl",
			singleClick    :    true,
		});
		
		new Draggable('div_add_item_dialog',{ handle: 'div_add_item_dialog_header'});
	},
	// function when user change expected delivery date
	expect_do_date_changed: function (){
		var v = this.f['expect_do_date'].value.trim();
		if(!v)	return false;
		
		var tmp = v.split('-');
		var y = int(tmp[0]);
		var m = int(tmp[1]);
		var d = int(tmp[2]);
		
		if(y>2000 && m >=1 && m <=12 && d>=1 && d<=31){
			var d = new Date(y, m-1, d);
			if(!(d.getTime() < allowed_max_expect_do_date.getTime())){
				alert('Expected Delivery Date cannot over '+allowed_max_expect_do_date.getFullYear()+'-'+(allowed_max_expect_do_date.getMonth()+1)+'-'+allowed_max_expect_do_date.getDate());
				this.f['expect_do_date'].value = '';
				return false;
			}
		}
				
		if(allowed_min_expect_do_date && !(d.getTime() >= allowed_min_expect_do_date.getTime())){
			alert('Expected Delivery Date cannot earlier than '+allowed_min_expect_do_date.getFullYear()+'-'+(allowed_min_expect_do_date.getMonth()+1)+'-'+allowed_min_expect_do_date.getDate());
			this.f['expect_do_date'].value = '';
			return false;
		}
	},
	// function when user change sku added date filter
	sku_added_filter_changed: function(){
		var v = this.f_search['sku_added_filter'].value;
		
		if(v == ''){
			$('span_sku_added_date').hide();
		}
		
		
		if(v == 'other'){
			$('inp_sku_added_date_from').readOnly = false;
			$('inp_sku_added_date_to').readOnly = false;
			$('img_sku_added_date_from').show();
			$('img_sku_added_date_to').show();
		}else{
			var num = v.split('-')[0];
			var type = v.split('-')[1];
			
			var day_time = 3600000*24;
			var deduct_time = day_time;
			if(type == 'w')	deduct_time *= 7;	// week
			else if(type == 'm')	deduct_time *= 30;	// month
			
			deduct_time *= num;
			
			var date_to = new Date();	// Now - Date to
			var date_from = new Date;
			date_from.setTime(date_to.getTime() - deduct_time + day_time);
			
			$('inp_sku_added_date_to').value = toYMD(date_to);//.getFullYear()+'-'+(date_to.getMonth()+1)+'-'+date_to.getDate();
			$('inp_sku_added_date_from').value = toYMD(date_from);//.getFullYear()+'-'+(date_from.getMonth()+1)+'-'+date_from.getDate();
			
			$('inp_sku_added_date_from').readOnly = true;
			$('inp_sku_added_date_to').readOnly = true;
			$('img_sku_added_date_from').hide();
			$('img_sku_added_date_to').hide();
		}
		
		$('span_sku_added_date').show();
	},
	// function when users click on show item
	show_item_clicked: function(){
		$('btn_show_item').disabled = true;
		$('div_item_list').update(_loading_);
		$('div_add_item').hide();
		
		var params = $(this.f_search).serialize();
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				$('div_item_list').update('');
			    // enable back the button
			    $('btn_show_item').disabled = false;
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                    $('div_item_list').update(ret['html']);
						$('span_item_selected_count').update('0');
						THIS.check_show_add_item();
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
	// function when user change sort by
	change_sort_by: function (ele){
		if(ele.value=='')   $('span_sort_order').hide();
		else    $('span_sort_order').show();
	},
	// function to detect need to show add item div or not
	check_show_add_item: function(){
		var chx_item_list = $$('#tbl_item_list input.chx_item_qty');
		if(chx_item_list.length > 0){
			$('div_add_item').show();
		}else{
			$('div_add_item').hide();
		}
		
	},
	// function when users change item selected
	item_qty_changed: function(sid){
		miz(this.f['item_qty['+sid+']']);
		var qty = float(this.f['item_qty['+sid+']'].value);
		if(qty<=0){
			this.f['item_qty['+sid+']'].value = '';
			qty = 0;
		}
		
		var selected_count = this.get_selected_item_count();
		
		if(qty>0){
			selected_count += 1;
		}else{
			selected_count -= 1;
		}
		
		$('span_item_selected_count').update(selected_count);
	},
	// function to get count of how many item selected
	get_selected_item_count: function(recalc){
		var selected_count = int($('span_item_selected_count').innerHTML);
		return selected_count;
	},
	// function to validate before add item
	validate_add_item: function(){
		if(!this.f['request_branch_id'].value){
			alert('Please select Request from Branch');
			return false;
		}
		
		if(this.get_selected_item_count()<=0){
			alert('Please select at least one item');
			return false;
		}
		
		return true;
	},
	// function when users click on add items
	add_items_clicked: function(){
		// validate
		if(!this.validate_add_item())	return false;
		
		if(!confirm('Are you sure to add items?'))	return false;
		
		var params = $(this.f).serialize();
		var THIS = this;
		
		curtain(true, 'curtain2');
		
		// mark loading
		$('div_add_item_dialog_content').update(_loading_);
		center_div($('div_add_item_dialog').show());
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: params,
			onComplete: function(msg){
				$('div_add_item_dialog_content').update('');
			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				THIS.close_add_item_dialog();
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['item_added_count']){ // success
						// refresh the list
						THIS.show_item_clicked();
	                    alert(ret['item_added_count']+' items added.');
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
	// function to close add item dialog
	close_add_item_dialog: function(){
		default_curtain_clicked();
		curtain(false, 'curtain2');
	}
}
{/literal}
</script>

<div id="div_add_item_dialog" class="curtain_popup" style="position:absolute;z-index:10005;min-width:200px;max-width:750px;min-height:200px;max-height:550px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_add_item_dialog_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Processing...</span>
		<span style="float:right;">
			{*<img src="/ui/closewin.png" align="absmiddle" onClick="ADVANCED_ADD.close_add_item_dialog();" class="clickable"/>*}
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_add_item_dialog_content" style="padding:2px;">
	</div>
</div>

<h1>{$PAGE_TITLE} - Advanced Search</h1>

<a href="do_request.php">&lt;&lt; Go Back to DO Request</a>

<form name="f_search" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_advance_search_items" />
	
	<div class="stdframe">
		<span>
			<b>Vendor</b>
			<select name="vendor_id">
				<option value="">-- All --</option>
				{foreach from=$vendor_list key=vendor_id item=r}
					<option value="{$vendor_id}">{$r.description}</option>
				{/foreach}
			</select>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<span>
			<b>Brand</b>
			<select name="brand_id">
				<option value="">-- All --</option>
				<option value="-1">UN-BRANDED</option>
				{foreach from=$brand_list key=brand_id item=r}
					<option value="{$brand_id}">{$r.description}</option>
				{/foreach}
			</select>
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		
		<p>
			{include file='category_autocomplete.tpl' all="1"}
		</p>
		
		<p>
			<span>
				<b>SKU Added in</b>
				<select name="sku_added_filter" onChange="ADVANCED_ADD.sku_added_filter_changed();">
					<option value="">-- All Time --</option>
					<option value="3-d">3 Day</option>
					<option value="1-w">1 Week</option>
					<option value="2-w">2 Week</option>
					<option value="1-m">1 Month</option>
					<option value="other">Others</option>
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
				
				<span id="span_sku_added_date" style="display:none;">
					<input name="sku_added_date_from" id="inp_sku_added_date_from" size="12" maxlength="10" value="" readonly />
					<img align="absmiddle" src="ui/calendar.gif" id="img_sku_added_date_from" style="cursor: pointer;" title="Select Date From" />
					To
					<input name="sku_added_date_to" id="inp_sku_added_date_to" size="12" maxlength="10" value="" readonly />
					<img align="absmiddle" src="ui/calendar.gif" id="img_sku_added_date_to" style="cursor: pointer;" title="Select Date To" />
				</span>
			</span>&nbsp;&nbsp;&nbsp;&nbsp;
		</p>
		
		<p>
			<span>
			<b>Sort by</b>
			<select name="sort_by" onChange="ADVANCED_ADD.change_sort_by(this);">
				<option value="">--</option>
				<option value="si.sku_item_code" {if $smarty.request.sort_by eq 'si.sku_item_code'}selected {/if}>ARMS Code</option>
				<option value="si.mcode" {if $smarty.request.sort_by eq 'si.mcode'}selected {/if}>MCode</option>
				<option value="si.artno" {if $smarty.request.sort_by eq 'si.artno'}selected {/if}>Art No</option>
				<option value="si.link_code" {if $smarty.request.sort_by eq 'si.link_code'}selected {/if}>{$config.link_code_name}</option>
				<option value="si.description" {if $smarty.request.sort_by eq 'si.description'}selected {/if}>Description</option>
				<option value="si.added" {if $smarty.request.sort_by eq 'si.added'}selected {/if}>SKU Added Time</option>
			</select>
			<span id="span_sort_order" style="display:none;">
			<select name="sort_order">
				<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
				<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
			</select>
			</span>
		</span>
		</p>
		
		<input type="button" value="Show Item" id="btn_show_item" onClick="ADVANCED_ADD.show_item_clicked();" />
		
		<br />
		* Maximum show 500 items;
	</div>
</form>

<br />

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_advance_search_add_items" />
	
	<div class="stdframe" id="div_add_item" style="display:none;">
		<b>Request from Branch</b>
			<select name="request_branch_id">
				<option value="">-- Please Select --</option>
				{foreach from=$branch item=b}
					{if $b.code ne $BRANCH_CODE && ($sessioninfo.branch_type ne "franchise" || $sessioninfo.branch_type eq "franchise" && $b.id eq 1)}
					<option value="{$b.id}" {if $b.code eq 'HQ'}selected {/if}>{$b.code} - {$b.description}</option>
					{/if}
				{/foreach}
			</select>
			
		&nbsp;&nbsp;&nbsp;
		<b>Remarks</b> <input size=32 maxlength="30" name="comment">&nbsp;&nbsp;&nbsp;
		
		{if !$config.do_request_no_expected_delivery_date}
			<b>Expected Delivery Date</b>
			<input name="expect_do_date" id="inp_expect_do_date" size="12" maxlength="10" value="{$default_expect_min_do_date|default:$default_expect_do_date}" onChange="ADVANCED_ADD.expect_do_date_changed();" />
			<img align="absmiddle" src="ui/calendar.gif" id="img_expect_do_date" style="cursor: pointer;" title="Select Date" />
		{/if}
		
		<p>
			<input type="button" value="Add Items into DO Request" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="ADVANCED_ADD.add_items_clicked()" />
		</p>
		<span id="span_item_selected_count">0</span> item(s) will be add.
	</div>
	
	<br />
	
	<div id="div_item_list">
	
	</div>
</form>

<script>
ADVANCED_ADD.initialise();
</script>
{include file='footer.tpl'}