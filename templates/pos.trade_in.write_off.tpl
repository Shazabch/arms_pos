{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}
.col_writeoff{
	background-color: #ffe0f0;
}
.col_verified{
	background-color: #ccffcc;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var selected_date = '{$smarty.request.date}';

{literal}
var TRADE_IN_WRITEOFF_MODULE = {
	selection_form: undefined,
	initialize: function(){
		this.selection_form = document.f_a;
	
		// init calendar	
		Calendar.setup({
			inputField     :    "inp_date",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date",
			align          :    "Bl",
			singleClick    :    true
		});
	},
	submit_form: function(){
		this.selection_form.submit();
	},
	get_id_info_by_ele: function(ele){
		var parent_ele = ele;

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('pi_item_row')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;

		var id_info = {
			counter_id: parent_ele.id.split('-')[2],
			pos_id: parent_ele.id.split('-')[3],
			pos_item_id: parent_ele.id.split('-')[4]
		}
		
		return id_info;
	},
	set_writeoff: function(ele){
		if(!confirm('Are you sure?'))	return false;
		
		var id_info = this.get_id_info_by_ele(ele);
		var counter_id = id_info['counter_id'];
		var pos_id = id_info['pos_id'];
		var pos_item_id = id_info['pos_item_id'];
		
		this.update_write_off(counter_id, pos_id, pos_item_id, 1);
	},
	undo_writeoff: function(ele){
		if(!confirm('Are you sure?'))	return false;
		
		var id_info = this.get_id_info_by_ele(ele);
		var counter_id = id_info['counter_id'];
		var pos_id = id_info['pos_id'];
		var pos_item_id = id_info['pos_item_id'];
		
		this.update_write_off(counter_id, pos_id, pos_item_id, 0);
	},
	update_write_off: function(counter_id, pos_id, pos_item_id, writeoff){
		if(!counter_id || !pos_id || !pos_item_id)	return false;
		
		var pi_item_row = $('tr-pi_item_row-'+counter_id+'-'+pos_id+'-'+pos_item_id);
		if(!pi_item_row)	return false;
		
		var span_act = ($(pi_item_row).getElementsBySelector("span.span_act"))[0];
		var span_loading = ($(pi_item_row).getElementsBySelector("span.span_loading"))[0];
		$(span_act).hide();
		$(span_loading).show();
		
		var params = {
			a: 'ajax_set_writeoff',
			counter_id: counter_id,
			pos_id: pos_id,
			pos_item_id: pos_item_id,
			date: selected_date,
			writeoff: writeoff
		}
		
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
	                	
	                    new Insertion.After(pi_item_row, ret['html']);
	                    $(pi_item_row).remove();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(err_msg.trim()=='')	err_msg = 'Unknown Error Occur';
			    // prompt the error
			    alert(err_msg);
			}
		});
	}
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="err">
		{foreach from=$err item=e}
		<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" method="post" onSubmit="return false;" class="stdframe">
	<input type="hidden" name="show_data" value="1" />

	<b>Date</b>
	<input name="date" id="inp_date" size="10" maxlength="10"  value="{$smarty.request.date|date_format:"%Y-%m-%d"}" />
	<img align="absmiddle" src="ui/calendar.gif" id="img_date" style="cursor: pointer;" title="Select Date" />
	&nbsp;&nbsp;&nbsp;&nbsp;
		
	<input type="button" value="Refresh" onClick="TRADE_IN_WRITEOFF_MODULE.submit_form();" />
</form>

<br />
{if $smarty.request.show_data && !$err}
	{if !$data}
		
		-- No Data --
	{else}
		<h2>{$report_title}</h2>
		
		<table class="report_table" width="100%">
			<tr class="header">
				{if $allow_edit}
					<th rowspan="2">&nbsp;</th>
				{/if}
				<th rowspan="2" width="50">Status</th>
				<th rowspan="2">Receipt No.</th>
				<th colspan="8">Trade In Info</th>
				<th colspan="6">Verified Info</th>
			</tr>
			<tr class="header">
				<!-- trade in -->
				<th>Barcode</th>
				<th>Description</th>
				<th>Serial No.</th>
				<th>Qty</th>
				<th>Total Price</th>
				<th>Approved by</th>
				<th>Write-off by</th>
				<th>Write-off timestamp</th>
				
				<!-- verify -->
				<th>ARMS Code</th>
				<th>MCode</th>
				<th>{$config.link_code_name}</th>
				<th>Description</th>
				<th>Verified by</th>
				<th>Timestamp</th>
			</tr>
			{foreach from=$data.by_counter key=counter_id item=pi_list}
				{assign var=counter_info value=$data.counter_info.$counter_id}
				<tr>
					<td colspan="17" bgcolor="#ccffff"><b>{$counter_info.network_name}</b></td>
				</tr>
				{foreach from=$pi_list item=pi}
					{include file='pos.trade_in.write_off.row.tpl'}
				{/foreach}
			{/foreach}
		</table>
	
	{/if}
{/if}
<script>
TRADE_IN_WRITEOFF_MODULE.initialize();
</script>
{include file='footer.tpl'}