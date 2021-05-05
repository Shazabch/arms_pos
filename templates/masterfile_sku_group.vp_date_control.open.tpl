{*
12/14/2012 3:41 PM Justin
- Enhanced to have 2 new buttons that can process global set expiry and add new date range.

1/18/2013 6:39 PM Justin
- Enhanced to allow user manual key in expiry date for set expire.

1/24/2013 5:11 PM Justin
- Enhanced to have check/uncheck all feature.

3/30/2015 4:44 PM Andy
- Enhanced to have checking on user submit/server received data to prevent data loss.

6/11/2015 4:56 PM Andy
- Enhanced to allow tick only those row without a date.
*}

{include file="header.tpl"}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var DC_MODULE = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// init calendar
		this.init_calendar();
	},
	// function when user click add date control row
	add_new_si_active_date_row_clicked: function(sid){
		if(!sid)	return false;
		
		var new_tr = $('tr_si_active_date_row-__TMP_SID__-__TMP_ROW_NO__').cloneNode(true);
		
		var new_row_no = this.get_si_active_date_max_row_no(sid)+1;
		
		new_tr.id = "tr_si_active_date_row-"+sid+'-'+new_row_no;	// change row id
		
		// get row html
		new_html = new_tr.innerHTML;
		
		// replace row num
		new_html = new_html.replace(/__TMP_ROW_NO__/g, new_row_no);
		new_html = new_html.replace(/__TMP_SID__/g, sid);
		$(new_tr).update(new_html);
		
		$('tbody_sku_active_date_list-'+sid).appendChild(new_tr);
		
		// init calendar
		this.init_calendar(sid, new_row_no);
		return new_row_no;
	},
	// function when user click delete active date row
	delete_tr_sid_active_date_row_clicked: function(sid, row_no){
		if(!sid)	return;
		
		if(!confirm('Are you sure?'))	return false;
		
		$('tr_si_active_date_row-'+sid+'-'+row_no).remove();
	},
	// function to get si date max row_no
	get_si_active_date_max_row_no: function(sid){
		var max_row_no = 0;
		var tr_si_active_date_row_list = $$('#tbody_sku_active_date_list-'+sid+' tr.tr_si_active_date_row');

		for(var i = 0; i<tr_si_active_date_row_list.length; i++){
			var tmp_row_info = this.get_si_active_row_info_by_ele(tr_si_active_date_row_list[i]);
			var tmp_row_no = tmp_row_info['row_no'];
			
			if(tmp_row_no > max_row_no)	max_row_no = tmp_row_no;
		}

		return max_row_no;
	},
	// function to get si active row_no
	get_si_active_row_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the tr contain tr_co_item
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_si_active_date_row')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return {};

		var ret = {};
		ret['sid'] = int(parent_ele.id.split('-')[1]);
		ret['row_no'] = int(parent_ele.id.split('-')[2]);
		
		return ret;
	},
	init_calendar: function(sid, row_no){
		var todo_list = [];
		
		if(sid && row_no){
			todo_list.push({'sid': sid, 'row_no': row_no});
		}else{
			// get all sku
			var tr_si_active_date_row_list = $$('#tbody_si_list tr.tr_si_active_date_row');

			for(var i = 0; i<tr_si_active_date_row_list.length; i++){
				var tmp_row_info = this.get_si_active_row_info_by_ele(tr_si_active_date_row_list[i]);
				
				var tmp_sid = tmp_row_info['sid'];
				var tmp_row_no = tmp_row_info['row_no'];
				
				todo_list.push({'sid': tmp_sid, 'row_no': tmp_row_no});
			}
		}
		
		for(var i=0; i<todo_list.length; i++){
			var row_info = todo_list[i];
			var tmp_sid = row_info['sid'];
			var tmp_row_no = row_info['row_no'];
			
			Calendar.setup({
			    inputField     :    "inp_si_active_date_from-"+tmp_sid+'-'+tmp_row_no,     // id of the input field
			    ifFormat       :    "%Y-%m-%d",      // format of the input field
			    button         :    "img_si_active_date_from-"+tmp_sid+'-'+tmp_row_no,  // trigger for the calendar (button ID)
			    align          :    "Bl",           // alignment (defaults to "Bl")
			    singleClick    :    true
			});
			
			Calendar.setup({
			    inputField     :    "inp_si_active_date_to-"+tmp_sid+'-'+tmp_row_no,     // id of the input field
			    ifFormat       :    "%Y-%m-%d",      // format of the input field
			    button         :    "img_si_active_date_to-"+tmp_sid+'-'+tmp_row_no,  // trigger for the calendar (button ID)
			    align          :    "Bl",           // alignment (defaults to "Bl")
			    singleClick    :    true
			});
		}
	},
	// function to validate form
	check_form: function(){
		if(!check_required_field(this.f))	return false;
		
		return true;
	},
	// function when user click save
	update_clicked: function(){
		var THIS = this;
		
		// set date item code
		var date_item_count = $$('#tbody_si_list tr.tr_si_active_date_row').length;
		this.f["date_item_count"].value = date_item_count;
		
		// validate form
		if(!this.check_form())	return;
		
		// update button status
		$$('#p_action_button input').invoke('disable');
		$('btn_vp_date_control').value = 'Saving . . .';
		
		this.is_updating = true;
		
		var params = $(this.f).serialize();
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    THIS.is_updating = false;
			    $$('#p_action_button input').invoke('enable');
			    $('btn_vp_date_control').value = 'Save';
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Update Successfully');
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
	set_expire: function(){
		var THIS = this;
		
		// validate form
		if(!this.check_form())	return;
		
		// check whether item got tick or not
		if(this.validate_item_is_check() == false){
			alert("Please select one or more item to set expire.");
			return false;
		}
		
		var expiry_date = prompt("Please key in the Expiry Date (YYYY-MM-DD):") || '';
		expiry_date = expiry_date.trim();
		if(!expiry_date){
			return false;
		}

		var err_msg = "";
		var tr_si_check_row_list = $$('#tbody_si_list .item_check_list');
		var splt_expiry_date = expiry_date.split("-");
		var tmp_expiry_date = new Date(splt_expiry_date[0], splt_expiry_date[1]-1, splt_expiry_date[2]);
		tmp_expiry_date.setTime(tmp_expiry_date.getTime());
		var expire_date = tmp_expiry_date.getFullYear()+'-'+(tmp_expiry_date.getMonth()+1)+'-'+tmp_expiry_date.getDate();
		
		var set_count = 0;
		var item_cannot_set = '';
		var last_to_date = '';
		var top_row_no = '';
		
		if(tr_si_check_row_list.length > 0){
			for(var i = 0; i<tr_si_check_row_list.length; i++){
				if(tr_si_check_row_list[i].checked == true){
					var sid = int(tr_si_check_row_list[i].id.split('-')[1]);
					var tr_si_active_date_row_list = $$('#tbody_sku_active_date_list-'+sid+' tr.tr_si_active_date_row');

					for(var j = 0; j<tr_si_active_date_row_list.length; j++){
						var is_found = false;
						var row_info = this.get_si_active_row_info_by_ele(tr_si_active_date_row_list[j]);
						var row_no = row_info['row_no'];
						var from_date = this.f['from_date['+sid+']['+row_no+']'].value;
						var to_date = this.f['to_date['+sid+']['+row_no+']'].value;
						var exp_from_date = from_date.split("-");
						var exp_to_date = to_date.split("-");
						var tmp_from_date = new Date(exp_from_date[0], exp_from_date[1]-1, exp_from_date[2]);
						var tmp_to_date = new Date(exp_to_date[0], exp_to_date[1]-1, exp_to_date[2]);
						tmp_from_date.setTime(tmp_from_date.getTime());
						tmp_to_date.setTime(tmp_to_date.getTime());
						
						if(tmp_expiry_date > tmp_from_date && tmp_expiry_date < tmp_to_date){
							this.f['to_date['+sid+']['+row_no+']'].value = expire_date;
							is_found = true;
							set_count++;
							break;
						}else{
							//alert("waaa");
							if(last_to_date == '' || tmp_to_date > last_to_date){
								last_to_date = tmp_to_date;
								top_row_no = row_no;
							}
						}
					}

					if(!is_found && top_row_no > 0){
						this.f['to_date['+sid+']['+top_row_no+']'].value = expire_date;
						last_to_date = "";
						top_row_no = "";
						//var sku_item_code = $(tr_si_check_row_list[i]).readAttribute('sku_item_code');
						//item_cannot_set += "* "+sku_item_code+"\n";
						// do nothing
					}
				}
			}
			//err_msg = set_count+' item(s) has been set expire at '+expire_date+"\n\n";
			
			//if(item_cannot_set) err_msg += "Cannot set expire for the following SKU Item Code:\n"+item_cannot_set;
			
			//if(err_msg)	alert(err_msg);
		}
	},
	date_range_menu: function(){
		// check whether item got tick or not
		if(this.validate_item_is_check() == false){
			alert("Please select one ore more item to set date range.");
			return false;
		}
		$('date_range_menu').show();
		curtain(true);
	},
	set_date_range: function(){
		var THIS = this;

		//var err_msg = "";
		var date_range_from = $('date_range_from').value;
		var date_range_to = $('date_range_to').value;
		var exp_date_range_from = date_range_from.split("-");
		var exp_date_range_to = date_range_to.split("-");
		var tmp_date_range_from = new Date(exp_date_range_from[0], exp_date_range_from[1]-1, exp_date_range_from[2]);
		var tmp_date_range_to = new Date(exp_date_range_to[0], exp_date_range_to[1]-1, exp_date_range_to[2]);
		tmp_date_range_from.setTime(tmp_date_range_from.getTime());
		tmp_date_range_to.setTime(tmp_date_range_to.getTime());
		if(!date_range_from || !date_range_to || tmp_date_range_from > tmp_date_range_to){
			alert("The Date range From/To is empty or invalid.");
			return;
		}
		
		// validate form
		if(!this.check_form())	return;

		var tr_si_check_row_list = $$('#tbody_si_list .item_check_list');
		
		if(tr_si_check_row_list.length > 0){
			for(var i = 0; i<tr_si_check_row_list.length; i++){
				if(tr_si_check_row_list[i].checked == true){
					//var is_overlap = false;
					var sid = int(tr_si_check_row_list[i].id.split('-')[1]);
					
					/*var tr_si_active_date_row_list = $$('#tbody_sku_active_date_list-'+sid+' tr.tr_si_active_date_row');
					for(var j = 0; j<tr_si_active_date_row_list.length; j++){
						var is_found = false;
						var row_info = this.get_si_active_row_info_by_ele(tr_si_active_date_row_list[j]);
						var tmp_row_no = row_info['row_no'];
						var to_date = this.f['to_date['+sid+']['+tmp_row_no+']'].value;
						var exp_to_date = to_date.split("-");
						var tmp_to_date = new Date(exp_to_date[0], exp_to_date[1]-1, exp_to_date[2]);
						tmp_to_date.setTime(tmp_to_date.getTime());
						
						if(tmp_date_range_to < tmp_to_date || ){
							var sku_item_code = $(tr_si_check_row_list[i]).readAttribute('sku_item_code');
							err_msg += "* "+sku_item_code+"\n";
							is_overlap = true;
							break;
						}
					}*/
					
					var row_no = this.add_new_si_active_date_row_clicked(sid);
					this.f['from_date['+sid+']['+row_no+']'].value = date_range_from;
					this.f['to_date['+sid+']['+row_no+']'].value = date_range_to;
				}
				
				//todo_list.push({'sid': tmp_sid, 'row_no': tmp_row_no});
			}

			//if(err_msg) alert("Cannot set date range for the following SKU Item Code:\n\n"+err_msg);
		}
		
		curtain_clicked();
	},
	
	validate_item_is_check: function(){
	
		var item_checked = 0;
		var tr_si_check_row_list = $$('#tbody_si_list .item_check_list');
		
		if(tr_si_check_row_list.length > 0){
			for(var i = 0; i<tr_si_check_row_list.length; i++){
				if(tr_si_check_row_list[i].checked == true){
					item_checked = 1;
				}
			}
		}
		
		if(item_checked == 0) return false;
		else return true;
	},
	// function when user click tick/un tick all
	check_all_clicked: function(checked){
		var tr_si_check_row_list = $$('#tbody_si_list .item_check_list');
		
		if(tr_si_check_row_list.length > 0){
			for(var i = 0; i<tr_si_check_row_list.length; i++){
				tr_si_check_row_list[i].checked = checked;
			}
		}
	},
	// function when user click on tick all without date
	check_all_without_date_clicked: function(){
		var tr_item_row_list = $$('#tbody_si_list tr.tr_item_row');
		
		if(tr_item_row_list.length > 0){
			for(var i = 0, len=tr_item_row_list.length; i<len; i++){
				var sid = tr_item_row_list[i].id.split('-')[1];
				
				var tr_si_active_date_row_list = $(tr_item_row_list[i]).getElementsBySelector("tr.tr_si_active_date_row");
				$('item_check-'+sid).checked = tr_si_active_date_row_list.length>0 ? false : true;
			}
		}
	}
}

function curtain_clicked(){
	$('date_range_menu').hide();
	$('date_range_from').value = '';
	$('date_range_to').value = '';
	curtain(false);
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<div id="date_range_menu" style="display:none; padding:10px; background-color: #fff; border:4px solid #999; position:fixed; top:200px; left:200px; z-index:20000;">
	<div class="small" style="position:absolute; right:10px;">
		<a href="javascript:void(curtain_clicked())"><img src="ui/closewin.png" border="0" align="absmiddle"></a>
	</div>
	<div class="stdframe">
		<p>
			<h4>Set Date Range Menu:</h4><br>
			From: <input type="text" size="10" id="date_range_from" value="" title="Date Range From" />
			<img align="absmiddle" src="ui/calendar.gif" id="drf_added" style="cursor: pointer;" title="Select Date Range From" />
			To: <input type="text" size="10" id="date_range_to" value="" title="Date Range To" />
			<img align="absmiddle" src="ui/calendar.gif" id="drt_added" style="cursor: pointer;" title="Select Date Range To" />
		</p> 
		<p align="center" id="choices">
			<input type="button" style="font:bold 14px Arial; background-color:#090; color:#fff;" value="Add" onclick="DC_MODULE.set_date_range();">
		</p>
	</div>
</div>

<table style="display:none;">
	{include file="masterfile_sku_group.vp_date_control.open.active_date_row.tpl" sid="__TMP_SID__" row_no="__TMP_ROW_NO__"}
</table>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_update_vp_date_control" />
	<input type="hidden" name="sku_group_bid" value="{$form.branch_id}" />
	<input type="hidden" name="sku_group_id" value="{$form.sku_group_id}" />
	<input type="hidden" name="date_item_count" value="0" />
	
	<div class="stdframe" style="background-color:#fff;">
		<table>
			<tr>
				<td width="100"><b>Code</b></td>
				<td>{$form.code}</td>
			</tr>
			<tr>
				<td><b>Description</b></td>
				<td>{$form.description}</td>
			</tr>
		</table>
	</div>
	
	<br />
	<input type="button" value="Set Expire" onclick="DC_MODULE.set_expire();" />&nbsp;
	<input type="button" value="Add New Date Range" onclick="DC_MODULE.date_range_menu();" />
	<br />
	<br />
	
	<div>
		<a href="javascript:void(DC_MODULE.check_all_clicked(true));">Tick All</a> | 
		<a href="javascript:void(DC_MODULE.check_all_clicked(false));">Un-Tick All</a> | 
		<a href="javascript:void(DC_MODULE.check_all_without_date_clicked());">Tick All without date</a>
		<table class="report_table" width="100%">
			<thead>
				<tr class="header">
					<th width="50" align="center">{*<input type="checkbox" name="item_check_all" id="item_check_all" value="1" onclick="DC_MODULE.check_all_clicked(this);" />*} #</th>
					<th width="150">ARMS Code</th>
					<th width="150">MCode</th>
					<th width="150">Art No</th>
					<th>Description</th>
					<th>Active Date <br />(From/To)</th>
				</tr>
			</thead>
			<tbody id="tbody_si_list">
				{foreach from=$form.item_list key=sid item=item name=f}
					<tr id="tr_item_row-{$sid}" class="tr_item_row">
						<td>
							<input type="checkbox" name="item_check[{$sid}]" id="item_check-{$sid}" value="1" {if $item.item_check}checked{/if} class="item_check_list" sku_item_code="{$item.sku_item_code}" />
							{$smarty.foreach.f.iteration}.
						</td>
						<td align="center">{$item.sku_item_code}</td>
						<td align="center">{$item.mcode|default:'-'}</td>
						<td align="center">{$item.artno|default:'-'}</td>
						<td>{$item.description}</td>
						
						{* Date Control *}
						<td>
							<table class="report_table">
								<tbody id="tbody_sku_active_date_list-{$sid}">
									{foreach from=$item.date_control item=si_date_row name=fdc}
										{assign var=row_no value=$smarty.foreach.fdc.iteration}
										
										{include file="masterfile_sku_group.vp_date_control.open.active_date_row.tpl"}
									{/foreach}
								</tbody>
							</table>
							<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="DC_MODULE.add_new_si_active_date_row_clicked('{$sid}');">+</button>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<p align="center" id="p_action_button">
		<input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onClick="DC_MODULE.update_clicked();" id="btn_vp_date_control" />
	</p>
</form>
<script type="text/javascript">DC_MODULE.initialize();</script>

{literal}
<script type="text/javascript">
	new Draggable('date_range_menu');

    Calendar.setup({
        inputField     :    "date_range_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "drf_added",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true,
		zIndex         :	100000
    });

    Calendar.setup({
        inputField     :    "date_range_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "drt_added",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}
{include file="footer.tpl"}
