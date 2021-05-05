{*
3/30/2018 4:13PM HockLee
- New template
- Create Sales Order by upload csv

8/21/2018 6:10 PM Andy
- Fixed generate sales order error.
*}

{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var disallowed_mprice = '{$disallowed_mprice}';
var sales_order_require_batch_code = '{$config.sales_order_require_batch_code}';

// gst
var enable_gst = int('{$config.enable_gst}');
var global_gst_start_date = '{$config.global_gst_start_date}';
var is_under_gst = int('{$form.is_under_gst}');
var branch_gst_register_no = '{$sessioninfo.gst_register_no}';
var branch_gst_start_date = '{$sessioninfo.gst_start_date}';
var gst_is_active = int('{$sessioninfo.gst_is_active}');
var skip_gst_validate = int('{$sessioninfo.skip_gst_validate}');

{literal}
var Sales_Order = {
	f_a: undefined,
	initialize: function() {
		this.f_a = document.f_a;
	},
}

function init_calendar(){
	if($('added1')){
		Calendar.setup({
			inputField     :    "added1",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "t_added1",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
			//,
			//onUpdate       :    load_data
		}); 
	}	
}

function refresh_tables(){
	var filename = this.f_a['import_csv'].value;		
	// only accept csv file
	if(filename.indexOf('.csv')<0){
		alert('Please select a valid csv file');
		return false;
	}

	document.f_a.a.value = "show_result";
	//document.f_a.target = "";
	document.f_a.submit();
}

function check_debtor(id){
	console.log(id);
	var item_count = 0;
	var debtor = document.getElementById('debtor_' + id);
	var debtor_id = debtor.id;
	debtor_id = debtor_id.split('_');
	var sku = document.f_a.elements['select_sku[]'];

	for(j = 0; j < sku.length; j++){
		var sku_id = sku[j].id;

		if(sku_id == debtor_id[1]){
			if(debtor.checked == false){
				sku[j].checked = false;
			}

			if(sku[j].checked == true){
				item_count = 1;
			}
		}
	}

	if(debtor.checked == true && item_count == 0){
		alert('Please select an item for debtor.');
		return false;
	}

	return true;
}

function check_item(id){
	var item_count = 0;
	var debtor = document.getElementById('debtor_' + id);
	var debtor_id = debtor.id;
	debtor_id = debtor_id.split('_');
	var sku = document.f_a.elements['select_sku[]'];

	for(j = 0; j < sku.length; j++){
		var sku_id = sku[j].id;

		if(sku_id == debtor_id[1]){
			if(debtor.checked == false){
				debtor.checked = true;
			}

			if(sku[j].checked == true){
				item_count = 1;
			}
		}		
	}

	if(debtor.checked == true && item_count == 0){
		alert('Please select an item for debtor.');
		return false;
	}

	return true;
}

function check_save(){
	//var debtor = document.f_a.elements['so_id_n_debtor_n_integration[]'];
	var debtor = $$('input.so_id_n_debtor_n_integration');
	var debtor_length = debtor.length;	
	var sku = document.f_a.elements['select_sku[]'];
	var total_debtor = 0;
	var total_item = 0;

	if(debtor_length == undefined){
		debtor_length = 1;
	}

	for(var i = 0; i < debtor_length; i++){
		var debtor_check = 0;
		var item_count = 0;
		var debtor_id = debtor[i].id;
		debtor_id = debtor_id.split('_');

		if(debtor[i].checked == true){
			debtor_check = 1;
			total_debtor += 1;
		}

		for(j = 0; j < sku.length; j++){
		var sku_id = sku[j].id;

			if(sku_id == debtor_id[1]){
				if(sku[j].checked == true){
					item_count = 1;
					total_item += 1;
				}
			}
		}

		if(debtor_check == 1 && item_count == 0){
			alert('Please select an item for debtor.');
			return false;
		}

		if(debtor_check == 0 && item_count == 1){
			alert('Please select an item for debtor.');
			return false;
		}
	}

	if(total_debtor == 0 && total_item == 0){
		alert('You do not select any debtor and item. Please select debtor and item.');
		return false;
	}

	if(document.f_a['batch_code'].value.trim() == ''){
		alert('Please enter batch code.');
		return false;
	}

	return true;
}

function do_save(){
	if (check_login()) {
        document.f_a.a.value = 'multi_save';
		document.f_a.target = "";
		if(check_save()){
			$('btn_save').disabled = true;
			document.f_a.submit();
		}
    }
}

var batch_code_autocomplete = undefined;

function reset_batch_code_autocomplete(){
	if($('inp_batch_code')){
		var param_str = "a=ajax_search_batch_code&";
		batch_code_autocomplete = new Ajax.Autocompleter("inp_batch_code", "div_autocomplete_batch_code_choices", phpself, {parameters:param_str, paramName: "value",
		indicator: 'span_loading_batch_code',
		afterUpdateElement: function (obj, li) {
			s = li.title;
			$('span_loading_batch_code').hide();
		}});
	}
	
}

// function when do date changed
function on_date_changed(){
	// get the object
	var inp = document.f_a['order_date'];
	// check max/min limit
	upper_lower_limit(inp);
	// check gst
	//if(enable_gst)	check_gst_date_changed();
}

function go_back() {
    location.href = phpself;
}

{/literal}
</script>

<h1>Sales Order (New)</h1>

<h3>Upload by CSV</h3>

<form name="f_a" enctype="multipart/form-data" class="stdframe" onsubmit="return Sales_Order.check_file(this);" method="post">
	<input type="hidden" name="a" value="multi_save" />
	<input type="hidden" name="method" value="1" />
	<input type="hidden" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" />
	<input type="hidden" name="id" value="{$form.id}" />
	<input type="hidden" name="order_no" value="{$form.order_no}" />
	<input type="hidden" name="total_ctn" value="{$form.total_ctn}" />
	<input type="hidden" name="total_pcs" value="{$form.total_pcs}" />
	<input type="hidden" name="total_amount" value="{$form.total_amount}" />
	<input type="hidden" name="total_qty" value="{$form.total_qty}" />
	<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}" />
	<input type="hidden" name="reason" />
	<input type="hidden" name="total_gross_amt" value="{$form.total_gross_amt}" />
	<input type="hidden" name="sheet_discount_amount" value="{$form.sheet_discount_amount}" />
	<input type="hidden" name="sheet_gst_discount" value="{$form.sheet_gst_discount}" />
	<input type="hidden" name="total_gst_amt" value="{$form.total_gst_amt}" />
	<input type="hidden" name="create_by_debtor_id" value="{$form.create_by_debtor_id}" />
	<input type="hidden" name="is_under_gst" value="{$form.is_under_gst}"/>
	<input type="hidden" name="sheet_discount" value="{$form.sheet_discount}"/>
	<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<td colspan="2" style="color:#0000ff;">
				Note:<br />
				* Please ensure the file extension <b>".csv"</b>.<br />				
				* Please ensure the import file contains header.<br />
				* You can download the sample below and refer to the format.<br />
				* You can download the Stock Reorder csv from > Office > PO (Purchase Order) > Stock Reorder Report.<br />
				* Support single or multiple upload.<br /><br />
			</td>
		</tr>
		<tr>
			<th width="150" align="left"><b>Upload Stock Reorder <br />(<a href="?a=download_sample_so&method=1">Download Sample</a>)</b></th>
			<td>
				<input type="file" name="import_csv" />&nbsp;&nbsp;&nbsp;
				<input type="button" value="Show Result" onclick="void(refresh_tables())" id="refresh_btn" />
				{if $form.file_name}{$form.file_name} is uploaded.{/if}
			</td>
		</tr>
	</table>
	<br>
	{if $item_lists && $method == '1'}
	<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<th width="150" align="left">Order Date </th>
			<td><input name="order_date" id="added1" size=10 onchange="on_date_changed();"  maxlength=10  value="{$form.order_date|default:$smarty.now|date_format:"%Y-%m-%d"}" />
				<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date" />
				<span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
			</td>
		</tr>
		<tr>
			<th align="left">Batch Code</th>
			<td><input name="batch_code" size=14 value="{$form.batch_code}" id="inp_batch_code" />
	            {if $config.sales_order_require_batch_code}
					<img src="ui/rq.gif" align="absmiddle" />
				{/if}
			    <span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
				<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				<span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
				<div>eg: SR19032018, BA21072018</div>
			</td>
		</tr>
	</table>

	{if $sample eq 'sample'}
	<div><h3>Sample</h3></div>
	{/if}

	{if $duplicate == 1}<script type="text/javascript">alert("System has detected duplicate Art Number. Please make a appropriate option.")</script>{/if}

	{foreach from=$debtors item=debtor}
	<table border="0" cellspacing="0" cellpadding="4">
		<th align="left" class="large">Debtor: {if $debtor.debtor_disabled eq 1}<span style="color: #ce0000;">{$debtor.debtor_remark}</span>{else}{$debtor.description} ({$debtor.integration_code}){/if}
		</th>
		{if $debtor.debtor_disabled eq 0}
		<td>
			<input type="checkbox" name="so_id_n_debtor_n_integration[]" id="debtor_{$debtor.integration_code}" value="{$debtor.so_id},{$debtor.debtor_id},{$debtor.integration_code}" {if $debtor.data eq 0}disabled{else}checked{/if} onclick="check_debtor('{$debtor.integration_code}')" class="so_id_n_debtor_n_integration" /> Generate Sales Order
		</td>
		{/if}
	</table>
	<div id="div_sheets">{include file="sales_order.display_csv.tpl"}</div>				
	{/foreach}

	{/if}

</form>

{if $item_lists && $method == '1' && $sample ne 'sample'}
<p id="p_submit_btn" align="center">
	<input name="bsubmit" type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" id="btn_save" />
	<input name="bsubmit" type="button" value="Cancel" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="go_back()" />
</p>
{/if}

{include file='footer.tpl'}

<script>
{if $readonly}
	Form.disable(document.f_a);
{else}
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	
	reset_batch_code_autocomplete();
	init_calendar();
	{/literal}
{/if}

</script>