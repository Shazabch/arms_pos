{*
5/18/2018 5:24PM HockLee
- Create Packing Input.
*}

{include file='header.tpl'}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var CHECKOUT_PACKING_INPUT = {
	initialize: function(){
		// initial calendar
		this.init_calendar();
	},

	init_calendar: function(){
	    // sales date from
        Calendar.setup({
			inputField     :    "inp_date_range_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_from",
			align          :    "Bl",
			singleClick    :    true
		});
		
		// sales date to
		Calendar.setup({
			inputField     :    "inp_date_range_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_to",
			align          :    "Bl",
			singleClick    :    true
		});
	},

}

function show_do(){
	if(document.f_picking_list_by_batch['batch_code'].value.trim()==''){
		alert('Please enter Batch Code');
		return false;
	}

	if(document.f_picking_list_by_batch['date_from'].value.trim()==''){
		alert('Please select sales date from.');
		return false;
	}
	if(document.f_picking_list_by_batch['date_to'].value.trim()==''){
		alert('Please select sales date to.');
		return false;
	}
	
	document.f_picking_list_by_batch.submit();
}

function validate_carton(id){
	var carton = document.f_packing['carton['+ id + '][]'];	
	carton.value = carton.value.regex(/\s/g, '');	// replace all whitespace
	carton.value = carton.value.regex(/[^0-9]/g, '');	// replace alphabet to whitespace
	carton.value = carton.value.regex(/[&\]/\\#,+()_$`@^~%.'[":*?<>|?!;:=~{}-]/g, '');	// replace special character
}

function validate_weight(id){
	var weight = document.f_packing['weight_kg['+ id + '][]'];	
	weight.value = weight.value.regex(/\s/g, '');	// replace all whitespace
	weight.value = weight.value.regex(/[&\]/\\#,+()_$`@^~%'[":*?<>|?!;:=~{}-]/g, '');	// replace special character
}

function save_packing(){
	if(confirm('Are you sure?')){
		document.f_packing.submit();
	}
}

var batch_code_autocomplete = undefined;

function reset_batch_code_autocomplete(){
	var param_str = "a=ajax_search_batch_code&";
	batch_code_autocomplete = new Ajax.Autocompleter("inp_batch_code", "div_autocomplete_batch_code_choices", phpself, {parameters:param_str, paramName: "value",
	indicator: 'span_loading_batch_code',
	afterUpdateElement: function (obj, li) {
	    s = li.title;
	    $('span_loading_batch_code').hide();
	}});
}

function print_picking_list(batch_code, date_from, date_to){
	window.open(phpself+'?a=print&batch_code='+batch_code+'&date_from='+date_from+'&date_to='+date_to);
}

function go_back() {
    location.href = "/do.php?page=credit_sales";
}

</script>

<style>
.text_right{ 
    text-align: right; 
}

.text_center{ 
    text-align: center; 
}
</style>
{/literal}

<h1>Input Packing Information</h1>

<form name="f_picking_list_by_batch" class="stdframe" method="post">
	<input type="hidden" name="a" value="show_do_for_packing" />

	<table border="0" cellspacing="0" cellpadding="4" width="100%">
		<tr>
			<th width="15%" align="left">Batch Code</th>
			<td>
				<input type="text" name="batch_code" value="{$batch_code}" id="inp_batch_code" /><img src="ui/rq.gif" align="absbottom" title="Required Field">
	            <span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
				<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
			</td>
		</tr>
		<tr>
			<th align="left">DO Date</th>
			<td>
				<b>from </b> 
				<input name="date_from" id="inp_date_range_from" size="10" maxlength="10"  value="{$date_from|default:$smarty.now-604800|date_format:"%Y-%m-%d"}" />
	    		<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_from" style="cursor: pointer;" title="Select Date" />

				<b>to </b> 
				<input name="date_to" id="inp_date_range_to" size="10" maxlength="10"  value="{$date_to|default:$smarty.now|date_format:"%Y-%m-%d"}" />
	   			<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_to" style="cursor: pointer;" title="Select Date" />
			</td>
		</tr>
		<tr>
			<td colspan="3">				
				<p id="p_submit_btn" align="center">
				<input type="button" value="Submit" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="show_do();" />
				<input type="button" value="Back" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="go_back();" />
				</p>
			</td>
		</tr>
	</table>
</form>

{if $no_data_msg}
<h2>{$no_data_msg}</h2>
{/if}

{if $do}
	{assign var=arr_count value=$do|@count}
	{assign var=count value=$arr_count*2}
	{if $checkout eq 1}<h5>* this batch has been checkout.</h5>{/if}
	<h2>Input Packed Carton and Weight</h2>

	<form name="f_packing" method="post">
	<input type="hidden" name="a" value="save_packing" />
	<input type="hidden" name="batch_code" value="{$batch_code}" />
	<input type="hidden" name="date_from" value="{$date_from}" />
	<input type="hidden" name="date_to" value="{$date_to}" />
	<input type="hidden" name="pack_date" value="{$smarty.now|date_format:"%Y-%m-%d"}" />

		<table class="report_table" width="100%">
			<tr class="header">
				<th>No.</th>
				<th>Do No.</th>
				<th>ARMS Code</th>
				<th>MCode<br />ArtNo<br />{$config.link_code_name|default:'Link Code'}</th>
				<th>SKU Description</th>
				<th>UOM</th>
				<th>Total Qty</th>
				<th>Pack Date</th>
				<th>Carton(s)</th>
				<th>Total Weight<br /> of all carton(s)</th>
			</tr>
			{assign var=item_no value=0}
			{foreach from=$do item=do_info key=do_no}		
			{foreach from=$do_info item=do_item}
			{if $a ne $do_item.do_no}
				{assign var=item_no value=$item_no+1}
			{else}
				{assign var=item_no value=$item_no}
			{/if}
			<tr class="text_center">
				<td>{if $a eq $do_item.do_no}{else}{$item_no}{/if}</td>
				<td>{if $a eq $do_item.do_no}{else}{$do_item.do_no}{/if}</td>
				<td>{$do_item.sku_item_code}</td>
				<td>
					{$do_item.mcode|default:'-'}<br />
					{$do_item.artno|default:'-'}<br />
					{$do_item.link_code|default:'-'}
				</td>
				<td>{$do_item.description}</td>
				<td>{$do_item.code}</td>
				<td>
					<table width="100%" class="report_table">
						<tr style="background-color:f0fa85;">
							<th>Ctn</th>
							<th>Pcs</th>
						</tr>
						<tr>
							<td>
								{$do_item.ctn}
							</td>
							<td>
								{$do_item.pcs}
							</td>
						</tr>
					</table>
				</td>
				<td>{$do_item.pack_date}</td>
				<td align="center">
					<input type="text" id="carton_validate_{$do_item.do_items_id}" class="text_right" name="carton[{$do_item.do_items_id}][]" value="{$do_item.carton}" onchange="validate_carton({$do_item.do_items_id});" {if $do_item.checkout eq 1}disabled{/if} />
				</td>
				<td align="center">
					<input type="text" id="weight_validate[]" class="text_right" name="weight_kg[{$do_item.do_items_id}][]" value="{$do_item.weight_kg}" onblur="mf(this, {$config.global_weight_decimal_points});" onchange="validate_weight({$do_item.do_items_id});" {if $do_item.checkout eq 1}disabled{/if} /> kg
				</td>
				<input type="hidden" name="do_id[{$do_item.do_items_id}][]" value="{$do_item.id}">
			</tr>
			{assign var=a value=$do_no}			
			{/foreach}
			{/foreach}
		</table>
		<br>

		<center><input type="button" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="save_packing();" /></center>				
	</form>
{/if}

{if $save_succeed}
<script language="javascript">alert("Save successfully.");</script>
{/if}

{include file='footer.tpl'}

<script>
	CHECKOUT_PACKING_INPUT.initialize();
	reset_batch_code_autocomplete();
</script>