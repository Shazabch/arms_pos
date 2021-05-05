{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}
</style>
{/literal}
<script>
var curr_id = '';
var phpself = '{$smarty.server.PHP_SELF}';
var duration_item_count = 0;
{literal}
function curtain_clicked(){
	if($('div_return_policy_table').style.display == ""){
		RETURN_POLICY_MODULE.table_fade();
	}
}

var RETURN_POLICY_MODULE = {
	curr_id: undefined,
	curr_bid: undefined,
	form_element: undefined,
	prv_bid: undefined,
	duration_item_count: 0,
	initialize : function(){
		var THIS = this;
		// event when user click "add"
		$('add_btn').observe('click', function(){
            THIS.add();
		});

		// even when user click "cancel" and "close"
		$('cancel_btn').observe('click', function(){
            THIS.table_fade();
		});
		$('close_btn').observe('click', function(){
            THIS.table_fade();
		});

		// even when user click "edit"
		$('restore_btn').observe('click', function(){
			THIS.edit(0, 0, 1);
		});

		// event when user click "update"
		$('update_btn').observe('click', function(){
            THIS.update();
		});

		// event when user click "Add"
		$('add_duration_item_btn').observe('click', function(){
            THIS.add_duration_item('duration', false);
		});

		// event when user click "Add"
		$('add_charges_item_btn').observe('click', function(){
            THIS.add_duration_item('charges', false);
		});
		
		// event when user click "Add" policy return
		$('add_return_policy').observe('click', function(){
            THIS.table_appear('add');
		});

		new Draggable('div_return_policy_table');
		center_div('div_return_policy_table');
	},
	table_appear : function(type){
		this.form_element = document.f_b;
		var THIS = this;
		if(type == "add"){
			$('bmsg').update("Complete below form and click Save");
			$('abtn').show();
			$('ebtn').hide();
			document.f_b.reset();
			document.f_b.id.value = 0;
			duration_item_count = 0;
			
			THIS.truncate_item_list();

			$('charges_condition').hide();
			$('charges_input').hide();
			$('charges_table').hide();
			
			this.form_element.duration.value = 0;
			this.form_element.duration_type.value = 1;
			this.form_element.duration_rate.value = 0;
			THIS.add_duration_item('duration', true);
			this.form_element.charges.value = 0;
			this.form_element.charges_type.value = 1;
			this.form_element.charges_rate.value = 0;
			THIS.add_duration_item('charges', true);
		}else{
			$('bmsg').update("Edit and click Update");
			$('abtn').hide();
			$('ebtn').show();
		}
		$('err_msg').update();
		hidediv('err_msg');

		Effect.SlideDown('div_return_policy_table', {
			duration: 0.5
		});
		curtain(true);
	},
	table_fade : function(){
		curtain(false);
		Effect.SlideUp('div_return_policy_table', {
			duration: 0.5,
			afterFinish: function() {
				$('bmsg').update();
			}
		});
	},
	add : function(){
		if(this.form_element == undefined) this.form_element = document.f_b;
		var prm = $(this.form_element).serialize();
		
		var params = {
		    a: 'add'
		};
		prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("New Return Policy ["+document.f_b.title.value.trim()+"] has been added.");
					document.location=phpself;
				}else{
					$('err_msg').update(msg.responseText.trim());
					Effect.Appear('err_msg', {
						duration: 0.5
					});
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	update : function(){
		this.form_element = document.f_b;
		var prm = $(this.form_element).serialize();

		var params = {
		    a: 'update'
		};
		prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("Return Privacy ["+document.f_b.title.value.trim()+"] has been updated.");
					document.location=phpself;
				}else{
					$('err_msg').update(msg.responseText.trim());
					Effect.Appear('err_msg', {
						duration: 0.5
					});
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},

	edit : function(id, bid, is_restore){
		if(is_restore && !confirm("Are you sure want to restore?")) return;
		else if(is_restore && (id == 0 || bid == 0)){
			id = this.curr_id;
			bid = this.curr_bid;
		}
		var THIS = this;
		THIS.truncate_item_list();
		
		document.f_b.reset();
		document.f_b.id.value = id;
		document.f_b.bid.value = bid;
		this.curr_id = id;
		this.curr_bid = bid;
		
		var has_charges = false;
		new Ajax.Request(phpself, {
			parameters:{
				a: 'edit',
				rp_id: id,
				branch_id: bid
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['rp_info']){ // success
					if(document.f_b){
						document.f_b.title.value = ret['rp_info']['title'];

						var duration_condition_list = $('tb').getElementsByClassName("duration_condition_list");
						duration_condition_list_count = duration_condition_list.length;

						if(duration_condition_list_count > 0){
							$A(duration_condition_list).each(
								function (r,idx){
									if(r.value == ret['rp_info']['duration_condition']){
										r.checked = true;
										throw $break;
									}
								}
							);
						}
						
						for(var key in ret['rp_info']['durations']){
							if(ret['rp_info']['durations'][key]['durations'] == undefined) continue;
							document.f_b.duration.value = ret['rp_info']['durations'][key]['durations'];
							document.f_b.duration_type.value = ret['rp_info']['durations'][key]['type'];
							document.f_b.duration_rate.value = ret['rp_info']['durations'][key]['rate'];
							THIS.add_duration_item('duration', true);
						}

						var charges_condition_list = $('tb').getElementsByClassName("charges_condition_list");
						charges_condition_list_count = charges_condition_list.length;

						if(charges_condition_list_count > 0){
							$A(charges_condition_list).each(
								function (r,idx){
									if(r.value == ret['rp_info']['charges_condition']){
										r.checked = true;
										throw $break;
									}
								}
							);
						}
						
						document.f_b.expiry_durations.value = ret['rp_info']['expiry_durations'];
						document.f_b.expiry_type.value = ret['rp_info']['expiry_type'];

						for(var key in ret['rp_info']['charges']){
							if(ret['rp_info']['charges'][key]['durations'] == undefined) continue;
							document.f_b.charges.value = ret['rp_info']['charges'][key]['durations'];
							document.f_b.charges_type.value = ret['rp_info']['charges'][key]['type'];
							document.f_b.charges_rate.value = ret['rp_info']['charges'][key]['rate'];
							THIS.add_duration_item('charges', true);
							has_charges = true;
						}
						
						if(has_charges){
							document.f_b.charges_choice.checked = true;
							$('charges_condition').show();
							$('charges_input').show();
							$('charges_table').show();
						}else{
							document.f_b.charges.value = 0;
							document.f_b.charges_type.value = 1;
							document.f_b.charges_rate.value = 0;
							THIS.add_duration_item('charges', true);
							$('charges_condition').hide();
							$('charges_input').hide();
							$('charges_table').hide();
						}
						document.f_b.receipt_remark.value = ret['rp_info']['receipt_remark'];
						document.f_b.max_charges.value = ret['rp_info']['max_charges'];
						if(ret['rp_info']['active'] == 1) document.f_b.active.checked = true;
						else document.f_b.active.checked = false;
						
						if(is_restore == 0) THIS.table_appear('edit');
						return;
					}else err_msg = "Failed to load edit form!";
				}else{  // load return policy info failed
					if(ret['failed_msg'])	err_msg = ret['failed_msg'];
					else err_msg = str;
				}

				alert(err_msg);
			}
		});

		document.f_b.a.value = 'update';
		document.f_b.title.focus();
	},
	activation : function(id, bid, status){
		if(status == 0 && !confirm("Are you sure want to deactivate this Return Policy?")) return;

		var params = {
		    a: 'activation',
			rp_id: id,
			branch_id: bid,
			value: status
		};
		//prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					document.location=phpself;
				}else{
					$('bmsg').update(msg.responseText.trim());
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	add_duration_item: function(extra, allow_zero_day){
		if(this.form_element == undefined) this.form_element = document.f_b;
		var duration = this.form_element[extra].value;
		var duration_type = this.form_element[extra+'_type'].value;
		var rate = this.form_element[extra+'_rate'].value;
		var amt_label = "";
		var duration_desc = "";
		this.form_element[extra].value = "";
		this.form_element[extra+'_type'].value = 1;
		this.form_element[extra+'_rate'].value = "";

		if((!duration || duration==0 || !rate || rate==0) && allow_zero_day == false) return;

		var items = $(extra+'_items').getElementsByClassName(extra+"_item_row");
		if(items.length > 0){
			if(this.form_element[extra+'_condition'][1].checked == true){
				alert("Cannot more than 1 item for condition [Every].");
				return;
			}
			this.form_element[extra+'_condition'][1].disabled = true;
		}else this.form_element[extra+'_condition'][1].disabled = false;

		if($(extra+'_item_no_data') != undefined) $(extra+'_item_no_data').hide();
		duration_item_count++;
		var row_id = duration_item_count;

		var new_tr = $('temp_duration_item_row').cloneNode(true).innerHTML;

		new_tr = new_tr.replace(/extra__/g, extra);
		new_tr = new_tr.replace(/__row__id/g, row_id);
		new Insertion.Bottom($(extra+'_items'), new_tr);
		// disable user for delete first item
		this.form_element[extra+'_item_durations['+row_id+']'].value = duration;
		this.form_element[extra+'_item_type['+row_id+']'].value = duration_type;
		this.form_element[extra+'_item_rate['+row_id+']'].value = rate;
		if(duration_type == 1){
			if(allow_zero_day && duration == 0){
				$(extra+'_img_del_'+row_id).hide();
				duration = "Beginning";
				duration_desc = "Day";
			}else duration_desc = "Day(s)";
		}else if(duration_type == 2) duration_desc = "Week(s)";
		else duration_desc = "Month(s)";
		$('span_'+extra+'_item_durations_'+row_id).update(duration);
		$('span_'+extra+'_item_type_'+row_id).update(duration_desc);
	},
	delete_duration_item: function(row_id, extra){
		if(!confirm("Are you sure want to delete this duration?")) return;
		if($(extra+'_item_'+row_id) != undefined){
			$(extra+'_item_'+row_id).remove();
			var duration_items = $('tb').getElementsByClassName(extra+"_item_row");
			if(duration_items.length <= 0 && $(extra+'_item_no_data') != undefined) $(extra+'_item_no_data').show();
			
		}

		var items = $(extra+'_items').getElementsByClassName(extra+"_item_row");
		if(items.length > 1) this.form_element[extra+'_condition'][1].disabled = true;
		else this.form_element[extra+'_condition'][1].disabled = false;
	},
	truncate_item_list: function(){
		var duration_items = $('tb').getElementsByClassName("duration_item_row");
		var duration_items_row = duration_items.length;
	
		// found have PO items
		if(duration_items_row > 0){
			$A(duration_items).each(
				function (r,idx){
					r.remove();
				}
			);
		}

		var charges_items = $('tb').getElementsByClassName("charges_item_row");
		var charges_items_row = charges_items.length;
	
		// found have PO items
		if(charges_items_row > 0){
			$A(charges_items).each(
				function (r,idx){
					r.remove();
				}
			);
		}
		
		if($('duration_item_no_data') != undefined) $('duration_item_no_data').show();
		if($('charges_item_no_data') != undefined) $('charges_item_no_data').show();
		duration_item_count = 0;
	},
	check_rate: function(obj){
		//var cm_value = obj.value;
		if(obj.value.match(/%/)){ // check cm value by percentage
			var tmp_cm_value = round(obj.value.replace("%", ""), 2);
			if(tmp_cm_value > 100){
				tmp_cm_value = "100%";
			}else if(tmp_cm_value <= 0){
				tmp_cm_value = "0%";
			}else{
				tmp_cm_value = float(obj.value)+"%";
			}
		}else{ // otherwise, it just a normal amount
			tmp_cm_value = round(obj.value, 2);
		}
		
		obj.value = tmp_cm_value;
	},
	toggle_expiry_charges_table: function(obj){
		if(obj.checked == true){
			$('charges_condition').show();
			$('charges_input').show();
			$('charges_table').show();
		}else{
			$('charges_condition').hide();
			$('charges_input').hide();
			$('charges_table').hide();
		}
	},
	check_condition: function(extra){
		if(this.form_element[extra+'_condition'][0].checked == true){ // checked 'more than'
			var THIS = this;
			this.form_element[extra].value = 0;
			this.form_element[extra+'_type'].value = 1;
			this.form_element[extra+'_rate'].value = 0;
			THIS.add_duration_item(extra, true);
		}else{
			var items = $(extra+'_items').getElementsByClassName(extra+"_item_row");
			var total_items = items.length;
			
			if(total_items > 0){
				$A(items).each(
					function (r,idx){
						$(r.id).remove();
						throw $break;
					}
				);
			}
			
			
			if((total_items-1) == 0){
				if($(extra+'_item_no_data') != undefined) $(extra+'_item_no_data').show();
			}
		}
	}
}

</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>
<div>
	<a id="add_return_policy" style="cursor:pointer;"><img src="ui/icons/user_add.png" title="Create Return Policy" align="absmiddle" border="0"> Create New Policy Return</a> <span id="span_loading"></span><br /><br />
</div>

<div id="temp_duration_item_row" class="temp_duration_item_row" style="display:none;">
	<table width="100%">
		<tr class="extra___item_row" id="extra___item___row__id">
			<td bgcolor="#eeeeee" align="center">
				<img src="/ui/del.png" id="extra___img_del___row__id" align="absmiddle" onclick="RETURN_POLICY_MODULE.delete_duration_item(__row__id, 'extra__');" class="clickable"/>
			</td>
			<td bgcolor="#eeeeee" align="right">
				<span id="span_extra___item_durations___row__id"></span>
				<input type="hidden" name="extra___item_durations[__row__id]">
			</td>
			<td bgcolor="#eeeeee" align="center">
				<span id="span_extra___item_type___row__id"></span>
				<input type="hidden" name="extra___item_type[__row__id]">
			</td>
			<td bgcolor="#eeeeee" align="right">
				<input name="extra___item_rate[__row__id]" size="10" maxlength="10" class="r" onchange="RETURN_POLICY_MODULE.check_rate(this);">
			</td>
		</tr>
	</table>
</div>

{include file="masterfile_return_policy.list.tpl"}

<br>

<!-- printing area -->
<form name="fprint" target="ifprint">
	<input type="hidden" name="a">
	<input type="hidden" name="selected_bid" />
</form>
<iframe name="ifprint" style="width:1px;height:1px;visibility:hidden;"></iframe>
<!-- end of printing area -->

<div class="ndiv" id="div_return_policy_table" style="position:absolute;width:450px;height:300px;display:none;z-index:10000;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small" style="position:absolute; right:10; text-align:right;"><a onclick="RETURN_POLICY_MODULE.table_fade();" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br><u>C</u>lose (Alt+C)</div>

<form method="post" name="f_b">
	<div id="bmsg" style="padding:10 0 10 0px;"></div>
	<div id="err_msg" style="color:#CE0000; display:none; font-weight:bold;"></div>
	<input type="hidden" name="a" value="add">
	<input type="hidden" name="id" value="">
	<input type="hidden" name="bid" value="">
	<table id="tb" width="100%">
		<tr>
			<td><b>Title</b></td>
			<td><input onBlur="uc(this)" name="title" size="40" maxlength="40"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td><b>Duration Condition</b></td>
			<td>
				{foreach from=$condition_list key=id item=dc_name}
					<input type="radio" name="duration_condition" value="{$id}" {if $id eq 1}checked{/if} class="duration_condition_list" onchange="RETURN_POLICY_MODULE.check_condition('duration');"/>{$dc_name}
				{/foreach}
			</td>
		</tr>
		<tr>
			<td><b>Duration</b></td>
			<td>
				<input name="duration" size="3" maxlength="3" class="r" onchange="mi(this);">
				<select name="duration_type">
					{foreach from=$date_type_list key=dt item=date_type}
						<option value="{$dt}">{$date_type}</option>
					{/foreach}
				</select> = 
				<input name="duration_rate" size="7" maxlength="7" class="r" onchange="RETURN_POLICY_MODULE.check_rate(this);">
				<input type="button" value="Add" id="add_duration_item_btn">
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<table width="100%" style="border:1px solid;">
					<tr>
						<th bgcolor="#dddddd" width="15%">&nbsp;</th>
						<th bgcolor="#cccccc" width="15%">Durations</th>
						<th bgcolor="#cccccc" width="40%">Type</th>
						<th bgcolor="#cccccc" width="30%">Deduct Rate</th>
					</tr>
					<tbody class="duration_items" id="duration_items">
						<tr id="duration_item_no_data">
							<td colspan="4" align="center">No record</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td><b>Expiry Durations</b></td>
			<td>
				<input name="expiry_durations" size="3" maxlength="3" class="r" onchange="mi(this);">
				<select name="expiry_type">
					{foreach from=$date_type_list key=dt item=date_type}
						<option value="{$dt}">{$date_type}</option>
					{/foreach}
				</select>
				<input type="checkbox" name="charges_choice" value="1" onclick="RETURN_POLICY_MODULE.toggle_expiry_charges_table(this);" /> Charges
			</td>
		</tr>
		<tr id="charges_condition" style="display:none;">
			<td><b>Charges Condition</b></td>
			<td>
				{foreach from=$condition_list key=id item=dc_name}
					<input type="radio" name="charges_condition" value="{$id}" {if $id eq 1}checked{/if} class="charges_condition_list" onchange="RETURN_POLICY_MODULE.check_condition('charges');"/>{$dc_name}
				{/foreach}
			</td>
		</tr>
		<tr id="charges_input" style="display:none;">
			<td>&nbsp;</td>
			<td>
				<input name="charges" size="3" maxlength="3" class="r" onchange="mi(this);">
				<select name="charges_type">
					{foreach from=$date_type_list key=dt item=date_type}
						<option value="{$dt}">{$date_type}</option>
					{/foreach}
				</select> = 
				<input name="charges_rate" size="7" maxlength="7" class="r" onchange="RETURN_POLICY_MODULE.check_rate(this);">
				<input type="button" value="Add" id="add_charges_item_btn">
			</td>
		</tr>
		<tr id="charges_table" style="display:none;">
			<td>&nbsp;</td>
			<td>
				<table width="100%" style="border:1px solid;">
					<tr>
						<th bgcolor="#dddddd" width="15%">&nbsp;</th>
						<th bgcolor="#cccccc" width="15%">Durations</th>
						<th bgcolor="#cccccc" width="40%">Type</th>
						<th bgcolor="#cccccc" width="30%">Charges Rate</th>
					</tr>
					<tbody class="charges_items" id="charges_items">
						<tr id="charges_item_no_data">
							<td colspan="4" align="center">No record</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td><b>Maximum Charges</b></td>
			<td><input name="max_charges" size="15" maxlength="15" class="r" onchange="mf(this);"></td>
		</tr>
		<tr>
			<td><b>Receipt Remark</b></td>
			<td><input onBlur="uc(this)" name="receipt_remark" size="40" maxlength="40"></td>
		</tr>
		<tr>
			<td><b>Active</b></td>
			<td><input type="checkbox" name="active" value="1" checked /></td>
		</tr>
	</table>
	<br />
	<!-- bottom -->
	<div align="center" id="abtn" style="display:none;">
		<input type="button" value="Save" id="add_btn"> 
		<input type="button" value="Cancel" id="cancel_btn">
	</div>
	<div align="center" id="ebtn" style="display:none;">
		<input type="button" value="Update" id="update_btn"> 
		<input type="button" value="Restore" id="restore_btn"> 
		<input type="button" value="Close" id="close_btn">
	</div>
</form>
</div></div></div>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
RETURN_POLICY_MODULE.initialize();
</script>

{include file=footer.tpl}
