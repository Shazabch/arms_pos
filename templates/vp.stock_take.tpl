{*
1/9/2013 2:17:00 PM Fithri
- rewrite the code, change JS and PHP to object-oriented

1/16/2013 9:39:00 AM Fithri
- import button change to "confirm stock take"
- zerolise checkbox change to "auto fill zero quantity for unfilled items"
- add column to show items category (level 3)
- can sort by category or description
- add print button to print out the item list

1/18/2013 10:50 AM Justin
- Enhanced to hide department dropdown list and become hidden field.
- Bug fixed on date could not be use once do sorting.

1/23/2013 3:48:00 PM Fithri
- stock balance & variance dont show in input, direct show it using span
- add Variance Cost (Cost * Variance Qty) column

2/4/2013 10:48 AM Justin
- Bug fixed on javascript generating long values.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>

.calendar, .calendar table {
	z-index:100000;
}
.positive{
	font-weight: bold;
	color:green;
}
.negative{
	font-weight: bold;
	color:red;
}
</style>
{/literal}


<script>

var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';

{literal}

var STOCKTAKE = {
	show_record : function(new_stock_take) {
	
		document.selection.a.value = 'load_table_data';
	
		if (!document.selection.dat.value && !new_stock_take) {
			alert('Please select date');
			return false;
		}
		
		$('div_table_frame').show();
		
		$('div_table').update(_loading_).show();
		
		if (new_stock_take) {
			document.selection.new_stock_take.value = '1';
			document.selection.dat.value = '';
		}
		else document.selection.new_stock_take.value = '';
		
		st = this;
		new Ajax.Updater("div_table",phpself,{
			parameters: document.selection.serialize(),
			onComplete: function(){
				st.btn_import(!new_stock_take);
				
				if (new_stock_take) document.tbl.new_stock_take.value = '1';
				else document.tbl.new_stock_take.value = '';

				if (new_stock_take) {
					Calendar.setup({
						inputField	:	"stock_take_date",
						ifFormat	:	"%Y-%m-%d",
						button		:	"stock_take_date_btn",
						align		:	"Bl",
						singleClick	:	true
					});
				}
				
				document.selection.sort_by.disabled = false;
				$('importbtn').show();
				$('finalbtn').hide();
				$('undobtn').hide();
			}
		});
	},
	sort_table : function() {
	
		document.selection.a.value = document.tbl.a.value = 'sort_table';
		document.tbl.sort_by.value = document.selection.sort_by.value;
		var allparams = document.selection.serialize()+'&'+document.tbl.serialize();
		st = this;
		new Ajax.Updater("div_table",phpself,{
			parameters: allparams,
			onComplete: function(){
				if (document.selection.new_stock_take.value) {
					Calendar.setup({
						inputField	:	"stock_take_date",
						ifFormat	:	"%Y-%m-%d",
						button		:	"stock_take_date_btn",
						align		:	"Bl",
						singleClick	:	true
					});
				}
				//st.btn_import(false);
			}
		});
	},
	form_submit : function(confirm) {
		if(!document.tbl.stock_take_date.value){
			alert("Please Select Date");
			return;
		}
		
		document.tbl.a.value = 'save_edit';
		document.tbl.sort_by.value = document.selection.sort_by.value;
		
		if (confirm) {
			$$('.stock_take_item').each(
				function (r) {
					var ri = r.id.split("_");
					if ($('qty_'+ri[1]).value == '') r.hide();
				}
			);
			document.tbl.is_import.value = '1';
			$('importbtn').hide();
			$('finalbtn').show();
			$('undobtn').show();
			document.tbl.sort_by.value = document.selection.sort_by.value;
			document.selection.sort_by.disabled = true;
			return;
		}
		else {
			document.tbl.is_import.value = '';
			var validated = false;
			$$('.stock_take_item').each(
				function (r) {
					var ri = r.id.split("_");
					if ($('qty_'+ri[1]).value != '') validated = true;
				}
			);
			if (!validated) {
				alert('Please key in at least one quantity');
				return;
			}
		}
		document.tbl.submit();
	},
	finalize : function() {
		document.tbl.a.value = 'save_edit';
		document.tbl.is_import.value = '1';
		
		if (document.selection.zerolize.checked) document.tbl.zerolize.value = '1';
		else document.tbl.zerolize.value = '';
		
		if (confirm('Are you sure?')) document.tbl.submit();
	},
	undo_confirm : function() {
		document.selection.sort_by.disabled = false;
		$$('.stock_take_item').each(function (r) {r.show()});
		this.btn_import();
		$('importbtn').disabled = false;
		$('printbtn').disabled = false;
	},
	btn_import : function(enable) {
		$('importbtn').show();
		$('printbtn').show();
		$('importbtn').disabled = !enable;
		$('printbtn').disabled = !enable;
		$('finalbtn').hide();
		$('undobtn').hide();
	},
	print_report : function() {
		document.print_form.date.value = document.selection.dat.value;
		document.print_form.sort_by.value = document.selection.sort_by.value;
		document.print_form.submit();
	},
	roundup_value : function (doc_allow_decimal,ele,id){
		if (ele.value.length == 0)	{
			$('var_'+id).value = ele.value;
			$('span_var_'+id).update(ele.value);
			$('span_var_cost_'+id).update(ele.value);
			return;
		}
		if (doc_allow_decimal == 1) ele.value = float(round(ele.value, global_qty_decimal_points));
		else mi(ele);
		$('var_'+id).value = float(round(ele.value - $('sb_qty_'+id).value, global_qty_decimal_points));
		$('span_var_'+id).update($('var_'+id).value);
		var variance_cost = float(round($('var_'+id).value*$('cost_'+id).value, global_cost_decimal_points));
		$('span_var_cost_'+id).update(variance_cost);
	}
};

{/literal}
</script>

<h1>Stock Take</h1>
<form name="selection" action="vp.stock_take.php">

	<input type=hidden name=a value=load_table_data />
	<input type=hidden name=new_stock_take />

	<table>
		<tr>
			<td valign=top><b>Date</b></td>
		</tr>
		<tr>
			<td>
				<div id="div_date" style="min-width:100px;">
					<select name="dat" onchange="STOCKTAKE.show_record(false)" size=10 style="width:100%;">
						{foreach from=$dat item=val}
							<option value="{$val.date}" {if $smarty.request.date eq $val.date}selected {/if}>{$val.date}</option>
						{/foreach}
					</select>
				</div>
			</td>
		</tr>
	</table>

	<input type="button" value="Refresh" onclick="STOCKTAKE.show_record(false);" />
	&nbsp;
	<input type="button" value="New" onclick="STOCKTAKE.show_record(true);" />
	&nbsp;
	<input id="printbtn" type="button" value="Print" onclick="STOCKTAKE.print_report();" />
	<br /><br />
	<input id="importbtn" type="button" value="Confirm" onclick="STOCKTAKE.form_submit(true);" />
	<input id="undobtn" type="button" value="Back" style="display:none;" onclick="STOCKTAKE.undo_confirm();" />
	<input id="finalbtn" type="button" value="Final Confirm" style="display:none;" onclick="STOCKTAKE.finalize();" />
	<label><input name="zerolize" type="checkbox" value="Zerolize" {if $zerolize}checked{/if} />Autofill zero quantity for unfilled item(s)</label>
	<div>
	<br />
	<b>Sort by : </b>
	<select name="sort_by" onchange="STOCKTAKE.sort_table();" {if !$smarty.request.saved}disabled{/if}>
	<option value="category" {if $smarty.request.sort_by eq 'category'}selected{/if}>Category</option>
	<option value="description" {if $smarty.request.sort_by eq 'description'}selected{/if}>SKU Description</option>
	</select>
	</div>
</form>

<br /><br />
<form name="tbl" action="vp.stock_take.php" method='post'>
	<input type=hidden name=a value=save_edit />
	<input type=hidden name=is_import />
	<input type=hidden name=sort_by />
	<input type=hidden name=zerolize />
	<input type=hidden name=new_stock_take />
	<input type=hidden name=date value="{$smarty.request.date}" />
	<input type=hidden name=branch_id value="{$smarty.request.branch_id}" />

	<div id="div_table_frame" class="stdframe" style="{if !$smarty.request.date}display:none;{/if}">
		<div id="div_table">
			{include file='vp.stock_take.table.tpl'}
		</div>
		<input type="button" value="Save" onclick="STOCKTAKE.form_submit(false)" />
	</div>
</form>

<form name="print_form" action="vp.stock_take.php" method='post' target="_blank">
	<input type=hidden name=a value=print_report />
	<input type=hidden name=date value="" />
	<input type=hidden name=sort_by />
</form>

{if $smarty.request.msg}<script>alert("Data successfuly saved")</script>{/if}
{if $smarty.request.existed}<script>alert("Stock take on {$smarty.request.existed} already existed")</script>{/if}
{include file='footer.tpl'}

{if !$smarty.request.saved}
{literal}
<script>STOCKTAKE.btn_import(false);</script>
{/literal}
{/if}
