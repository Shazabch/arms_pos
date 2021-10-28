{*
12/8/2014 2:07 PM Andy
- Add a remark to let user know what sku are searchable.
- Amend the remark.

12/16/2019 1:11 PM William
- Enhanced to show "PO Reorder Qty by Branch" table when sku items PO Reorder Qty by child.

1/23/2020 4:00 PM William
- Remove error message when po reorder save successfully.
*}

{include file="header.tpl"}
{literal}
<style>
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
var sid = '{$smarty.request.sid}';
var si_code = '{$smarty.request.si_code}';
{literal}

var sku_autocomplete = undefined;
var PO_REORDER_QTY_BY_BRANCH_MODULE = {
	form_element: undefined,
	initialize : function(){
		var THIS = this;
		THIS.reset_sku_autocomplete();
		
		// found it is redirected from SKU Listing
		if(sid > 0){
			document.f_a.sku_item_id.value = sid;
			document.f_a.sku_item_code.value = si_code;
			THIS.find_items();
		}
	},

	// update autocompleter parameters when vendor_id or department_id changed
	reset_sku_autocomplete: function(){
		//var param_str = "a=ajax_search_sku&dept_id={/literal}{$form.department_id}{literal}&type="+getRadioValue(document.f_a.search_type);
		var param_str = "a=ajax_search_sku&type="+getRadioValue(document.f_a.search_type)+"&check_po_reorder_by_child=1";
		var THIS = this;
		if (sku_autocomplete != undefined)
		{
			sku_autocomplete.options.defaultParams = param_str;
		}
		else
		{
			sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
			afterUpdateElement: function (obj, li) {
				s = li.title.split(",");
				document.f_a.sku_item_id.value = s[0];
				document.f_a.sku_item_code.value = s[1];
				
			}});
		}
		THIS.clear_autocomplete();
	},

	clear_autocomplete: function(){
		document.f_a.sku_item_id.value = '';
		document.f_a.sku_item_code.value = '';
		$('autocomplete_sku').value = '';
		$('autocomplete_sku').focus();
	},

	find_items: function(obj){
		var params = "";

		if(!document.f_a.sku_item_id.value){
			alert('Please select SKU item.');
			return false;
		}

		if(obj != undefined) obj.disabled = true;

		new Ajax.Updater('div_sku_items', phpself, {
			method:'post',
			parameters: Form.serialize(document.f_a)+params,
			evalScripts: true,
			onFailure: function(m){
				alert(m.responseText);
			},
			onSuccess: function(m){
				if(obj != undefined) obj.disabled = false;
			}

		});
	},

	save: function(){
		if(!confirm("Are you sure want to save?")) return false;

		new Ajax.Request(phpself,{
			method:'post',
			parameters: Form.serialize(document.f_a)+"&a=save",
			evalScripts: true,
			onFailure: function(m){
				alert(m.responseText);
			},
			onSuccess: function(m){
				eval("var json = "+m.responseText);
				for(var tr_key in json){
					if(json[tr_key]['err'] != undefined){
						$("div_err_msg").update("<ul>"+json[tr_key]['err']+"</ul>");
						return;
					}else{
						$("div_err_msg").update('');
						alert("Save sucessfully.");
						return;
					}
				}
			}
		});
	},

	disableEnterKey: function(e){
		 var key;

		 if(window.event) key = window.event.keyCode;	//IE
		 else key = e.which;	//firefox

		 if(key == 13) return false;
		 else return true;
	}
}

{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe">

			<form name="f_a" method="post">
			<input type="hidden" name="a" value="search">
			<table>
			<tr>
				<td colspan="2">
					<div class="alert alert-primary rounded">
						<ul>
							<li> This module will only show the item which enable PO Reorder Qty By Child. (Set this in SKU Masterfile)</li>
						</ul>
					</div>
				</td>
			</tr>
			<tr>
				<th class="form-label">Search SKU</th>
				<td>
					<input name="sku_item_id" size=3 type="hidden" />
					<input name="sku_item_code" size=13 type="hidden" />
					<input class="form-control" id="autocomplete_sku" name="sku" size=50 onclick="this.select()" onKeyPress="return PO_REORDER_QTY_BY_BRANCH_MODULE.disableEnterKey(event);" style="font-size:14px;width:500px;">
					<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
				</td>
				<td><input type="button" class="btn btn-primary" value="Search" onclick="PO_REORDER_QTY_BY_BRANCH_MODULE.find_items(this);"></td>
			</tr><tr>
				<td>&nbsp;</td>
				<td>
					<input onchange="PO_REORDER_QTY_BY_BRANCH_MODULE.reset_sku_autocomplete()" type=radio name="search_type" value="1" checked> MCode &amp; {$config.link_code_name}
					<input onchange="PO_REORDER_QTY_BY_BRANCH_MODULE.reset_sku_autocomplete()" type=radio name="search_type" value="2" {if $smarty.request.search_type eq 2 || (!$smarty.request.search_type and $config.consignment_modules)}checked {/if}> Article No
					<input onchange="PO_REORDER_QTY_BY_BRANCH_MODULE.reset_sku_autocomplete()" type=radio name="search_type" value="3"> ARMS Code
					<input onchange="PO_REORDER_QTY_BY_BRANCH_MODULE.reset_sku_autocomplete()" type=radio name="search_type" value="4"> Description
				</td>
			</tr>
			
			</table>
			
			<div id="div_err_msg" class="errmsg"></div>
			
			<input name="item_del_list" type="hidden">
			<div id="div_sku_items">
			{if $smarty.request.show_type}
				{include file="masterfile_sku_items.po_reorder_qty_by_branch.items.tpl"}
			{/if}
			</div>
			</form>
			</div>
	</div>
</div>
{include file="footer.tpl"}
{literal}
<script>
PO_REORDER_QTY_BY_BRANCH_MODULE.initialize();
</script>
{/literal}
