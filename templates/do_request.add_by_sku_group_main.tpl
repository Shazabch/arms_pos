{*
12/24/2012 11:45 AM Andy
- DO Request can add by sku group.

12/26/2012 10:24 AM Andy
- Fix if refresh sku group error, the loading icon should hide.

1/10/2012 3:09 PM Andy
- Add can choose to sort which field when refresh sku group item.

3/5/2013 12:48 PM Justin
- Enhanced to have validation for expected delivery date that disallow user to choose previous date base on config set.
*}

{include file="header.tpl"}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<h1>{$PAGE_TITLE} (Add by SKU Group)</h1>

<script type="text/javascript">

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

if(do_request_expected_delivery_date_times && do_request_expected_delivery_date_days){
	var curr_date = new Date();
	var curr_year = curr_date.getFullYear();
	var curr_mth = curr_date.getMonth()+1;
	var curr_day = curr_date.getDate();
	var edd_time_splt = do_request_expected_delivery_date_times.split(":");
	var expired_expected_delivery_date = new Date(curr_year, curr_mth-1, curr_day, edd_time_splt[0], edd_time_splt[1], 0);

	//expired_expected_delivery_date.setTime(expired_expected_delivery_date.getTime() + 3600000 * do_request_expected_delivery_date_times);
	if(curr_date.getTime() > expired_expected_delivery_date.getTime()){
		allowed_min_expect_do_date = new Date(curr_year, curr_mth-1, int(curr_day)+int(do_request_expected_delivery_date_days), 0, 0, 0);
	}else{
		allowed_min_expect_do_date = new Date(curr_year, curr_mth-1, curr_day+1, 0, 0, 0);
	}
}

var DO_REQUEST_BY_SKU_GROUP = {
	f_a: undefined,
	f_b: undefined,
	initialize: function(){
		this.f_a = document.f_a;
		this.f_b = document.f_b;
		
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
	},
	// function when user click Refresh
	refresh_sku_group_item_list: function(){
		if(this.f_a['sku_group_ids'].value ==''){
			alert('Please select SKU Group.');
			return false;
		}
		
		var THIS = this;
		
		/*var params = {
			a: 'ajax_refresh_sku_group_item_list',
			sku_group_ids: this.f_a['sku_group_ids'].value
		};*/
		var params = $(this.f_a).serialize();
		
		$('div_sku_group_item_list').update(_loading_);
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    $('div_sku_group_item_list').update('');
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('div_sku_group_item_list').update(ret['html']);
						
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);

			}
		});
	},
	// function to check form
	check_form: function(f){
		if(f == 'f_a'){	// check f_a
		
			return true;
		}else if(f == 'f_b'){	// check f_b
			if(!this.f_b['request_branch_id'].value){
				alert('Please select request from branch.');
				return false;
			}
			
			// get all rows
			var tr_sgi_item_row_list = $$('#div_sku_group_item_list tr.tr_sgi_item_row');
		
			if(tr_sgi_item_row_list.length<=0){
				alert('No item to add');
				return false;
			}
			
			var got_request_qty = false;
			// loop all the row
			for(var i=0; i<tr_sgi_item_row_list.length; i++){
				var sid = tr_sgi_item_row_list[i].id.split('-')[1];

				// check qty
				var qty = float(this.f_b['request_qty_list['+sid+']'].value);

				if(qty<0){
					alert("Qty must more than zero");
					this.f_b['request_qty_list['+sid+']'].focus();
					return false;
				}else if(qty>0){
					got_request_qty = true;
				}
			}
			
			if(!got_request_qty){	// no item got key in qty
				alert('No item to add');
				return false;
			}
			return true;
		}
	},
	// function when user click DO Request
	add_do_request_clicked: function(){
		if(!this.check_form('f_b'))	return false;
		
		if(!confirm('Are you sure?'))	return false;
		
		var btn_add_do_request = $('btn_add_do_request');
		btn_add_do_request.default_value = btn_add_do_request.value;
		btn_add_do_request.value = 'Processing . . .';
		btn_add_do_request.disabled = true;
		
		var params = $(this.f_b).serialize();
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    
			    btn_add_do_request.value = btn_add_do_request.default_value;
				btn_add_do_request.disabled = false;
			
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
	                	if(ret['html']){
	                		$('div_result').update(ret['html']).show();
	                	}
	                	
	                	alert('Item Successfully added to DO Request, please refresh DO Request to see the changes.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	expect_do_date_changed: function (){
		var v = this.f_b['expect_do_date'].value.trim();
		if(!v)	return false;
		
		var tmp = v.split('-');
		var y = int(tmp[0]);
		var m = int(tmp[1]);
		var d = int(tmp[2]);
		
		if(y>2000 && m >=1 && m <=12 && d>=1 && d<=31){
			var d = new Date(y, m-1, d);
			if(!(d.getTime() < allowed_max_expect_do_date.getTime())){
				alert('Expected Delivery Date cannot over '+allowed_max_expect_do_date.getFullYear()+'-'+(allowed_max_expect_do_date.getMonth()+1)+'-'+allowed_max_expect_do_date.getDate());
				document.f_b['expect_do_date'].value = '';
				return false;
			}
		}
				
		if(allowed_min_expect_do_date && !(d.getTime() >= allowed_min_expect_do_date.getTime())){
			alert('Expected Delivery Date cannot earlier than '+allowed_min_expect_do_date.getFullYear()+'-'+(allowed_min_expect_do_date.getMonth()+1)+'-'+allowed_min_expect_do_date.getDate());
			document.f_a['expect_do_date'].value = '';
			return false;
		}
	}
};
{/literal}
</script>

<form name="f_a" class="stdframe" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_refresh_sku_group_item_list" />
	
	<b>SKU Group</b>
	<select name="sku_group_ids">
		<option value="">-- Please Select --</option>
		{foreach from=$sku_group_list item=r}
			<option value="{$r.branch_id},{$r.sku_group_id}">{$r.code} - {$r.description} ({$r.item_count})</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;
	
	<b>Sort by</b>
	<select name="sort_by">
		<option value="si.description">Description</option>
		<option value="si.sku_item_code">ARMS Code</option>
		<option value="si.mcode">MCode</option>
		<option value="si.artno">Art No</option>
	</select>
	<select name="sort_order">
		<option value="asc">Ascending</option>
		<option value="desc">descending</option>
	</select>
	&nbsp;&nbsp;&nbsp;
	
	<input type="button" value="Refresh" onClick="DO_REQUEST_BY_SKU_GROUP.refresh_sku_group_item_list();" />
</form>


<br />

<form name="f_b" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_add_do_request_item_by_sku_group" />
	
	<div class="stdframe">
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
			<input name="expect_do_date" id="inp_expect_do_date" size="12" maxlength="10" value="{$default_expect_min_do_date|default:$default_expect_do_date}" onChange="DO_REQUEST_BY_SKU_GROUP.expect_do_date_changed();" />
			<img align="absmiddle" src="ui/calendar.gif" id="img_expect_do_date" style="cursor: pointer;" title="Select Date" />
		{/if}
	</div>
	<div id="div_sku_group_item_list">
	
	</div>
</form>
<script type="text/javascript">DO_REQUEST_BY_SKU_GROUP.initialize();</script>

{include file="footer.tpl"}
