{*
8/5/2011 12:55:11 PM Andy
- Add Sales Order print preview page.

11/23/2011 2:44:43 PM Justin
- Added new report printing menu.
- Added config check to show new option choice for user to print "Quotation".

4/3/2012 3:38:25 PM Andy
- Add can select approved Sales Order to generate to PO.

3/4/2013 11:51 AM Andy
- Add get receipt list when load the sales order which has been exported to POS.

4/11/2013 11:15 AM Andy
- Add can print sales order checklist if got config "sales_order_print_checklist_template".

4/6/2017 14:38 Qiu Ying
- Add default template to represent ARMS Sales Order Quotation

11/23/2017 1:50 PM Justin
- Enhanced to have [?] for user to click of what can be search from search engine.

3/30/2018 4:13PM HockLee
- Add new link to generate to upload csv.

10/10/2019 9:46 AM William
- Add new print option "Picking List" for sales order.

7/14/2020 4:16 PM William
- Enhanced to change checkbox print document to use radio button.

3/3/2021 16:40 PM Sin Rou
- Added config check to hide "Batch Code" in column table.
*}

{include file='header.tpl'}

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var tab_num = 1;
var page_num = 0;
var order_no_autocomplete;

{literal}
var search_str = '';


function list_sel(selected){
	var order_list = $('order_list');
	if(!order_list) return;

	if(selected==6){
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){
			//alert('Cannot search empty string');
			return;
		}else 	search_str = tmp_search_str;
	}
	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}

	var all_tab = $$('.tab .a_tab');
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('selected');
	}
	$('lst'+tab_num).addClassName('selected');

	$('order_list').update(_loading_);
	new Ajax.Updater('order_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num,{
		parameters:{
			search_str: search_str
		},
		onComplete: function(msg){

		},
		evalScripts: true
	});
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(6);
	}
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function curtain_clicked(){
	$('print_dialog').style.display = 'none';
	$('div_generate_po').hide();
	$('div_generate_po_result').hide();
	$('receipt_details_container').hide();
	
	curtain(false);
}

function do_print(id,bid){
	if(!confirm('Click OK to print'))   return false;
	document.f_print['id'].value = id;
	document.f_print['branch_id'].value = bid;
    document.f_print.a.value='print_order';
	//document.f_print.target = 'ifprint';
	document.f_print.target = '_blank';
	document.f_print.submit();
}

function show_print_dialog(id,bid){
	center_div('print_dialog');
	document.f_print['id'].value = id;
	document.f_print['branch_id'].value = bid;
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
	curtain(true);
}

function print_ok(){
	var dropdown_print_title = $('dropdown_print_title');
	if(dropdown_print_title.value == 'other' && document.f_print['title_other_print'].value == ''){
		alert("Please enter the title.");
		return false;
	}
	//if(!confirm('Click OK to print')) return false;
    document.f_print.a.value='print_order';
	//document.f_print.target = 'ifprint';
	document.f_print.target = '_blank';
	document.f_print.submit();
	curtain_clicked();
}

function show_generate_po_popup(){
	$('tbody_order_no_list').update('');
	center_div($('div_generate_po').show());
	curtain(true);
	$('inp_order_no').value = '';
	$('inp_order_no').focus();
}

function reset_order_no_autocomplete(){
	var param_str = "a=ajax_search_order_no&";
	order_no_autocomplete = new Ajax.Autocompleter("inp_order_no", "div_autocomplete_order_no_choices", phpself, {parameters:param_str, paramName: "value",
	indicator: 'span_loading_order_no',
	afterUpdateElement: function (obj, li) {
	    $('span_loading_order_no').hide();
	}});
}

function add_so_to_order_no_list(){
	var order_no = $('inp_order_no').value.trim();
	if(!order_no)	return;
	
	// check duplicate
	var td_order_no_list = $$('#tbody_order_no_list td.td_order_no');
	for(var i=0; i<td_order_no_list.length; i++){
		if(td_order_no_list[i].innerHTML.trim()==order_no){
			alert('Order No ('+order_no+') already added in the list.');
			return;
		}
	}
	
	var inp_add_order_no = $('inp_add_order_no');
	var span_adding_order_no = $('span_adding_order_no');
	
	inp_add_order_no.disabled = true;
	span_adding_order_no.show();
	
	var params = {
		a: 'ajax_add_order_no_to_generate_po',
		order_no: order_no
	};
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
			var str = e.responseText.trim();
			var ret = {};
		    var err_msg = '';

			inp_add_order_no.disabled = false;
		    span_adding_order_no.hide();
		    
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok'] && ret['html']){ // success
                    new Insertion.Bottom('tbody_order_no_list', ret['html']);
                    $('inp_order_no').value = '';
                    $('inp_order_no').focus();
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
}

function remove_order_no_from_list(branch_id, so_id){
	td_order_no = $('td_order_no-'+branch_id+'-'+so_id);
	if(td_order_no)	$(td_order_no).remove();
}

function generate_po(){
	// check sales order
	
	var inp_so_list = $(document.f_so_to_po).getElementsBySelector('input.inp_so_list');
	if(inp_so_list.length<=0){
		alert('Please search and add at least 1 Sales Order.');
		return;
	}
	
	if(!confirm('Are you sure?'))	return;
	
	var inp_generate_po = $('inp_generate_po');
	var span_generating_po = $('span_generating_po');
	inp_generate_po.disabled = true;
	span_generating_po.show();
	
	new Ajax.Request(phpself, {
		parameters: $(document.f_so_to_po).serialize(),
		onComplete: function(e){
			var str = e.responseText.trim();
			var ret = {};
		    var err_msg = '';

			inp_generate_po.disabled = false;
		    span_generating_po.hide();
		    
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok'] && ret['po_html']){ // success
                	$('div_generate_po').hide();
                	$('div_generate_po_result_content').update(ret['po_html']);
                    center_div($('div_generate_po_result').show());
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
}

function trans_detail(counter_id,cashier_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('receipt_details_container');
	
    $('receipt_details_container').show();
	$('receipt_details').update('Please wait...');
	//return;

	new Ajax.Updater('receipt_details','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			branch_id: branch_id,
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}

function close_receipt_details(){
	default_curtain_clicked();
}

function toggle_search_info(){
	alert("Search by Batch / Order / PO No / Debtor Code or Description");
}

function onchange_print_type(){
	var div_sales_order_dropdown = $('div_sales_order_dropdown');
	if(document.f_print['print_type'].value == 'sales_order')  div_sales_order_dropdown.show();
	else  div_sales_order_dropdown.hide();
}

function onchange_title_print(){
	var tr_title_other = $('tr_title_other');
	var dropdown_print_title = $('dropdown_print_title');
	if(dropdown_print_title.value == 'other')  tr_title_other.show();
	else tr_title_other.hide();
}
{/literal}
</script>

<!--div style="display:none;">
<form name=f_print method=get>
<input type=hidden name="a">
<input type=hidden name="id">
<input type=hidden name="branch_id">
</form></div-->

<!-- Start print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:320px;height:180px;position:absolute; padding:10px; display:none;">
<form name=f_print method=get>
<input type=hidden name=a>
<input type=hidden name=id>
<input type=hidden name=branch_id>
<table width="100%">
	<tr>
		<td rowspan="2"><img src="ui/print64.png" hspace=10 align=left></td>
		<td><h3>Print Options</h3></td>
	</tr>
	<tr>
		<td>
			<input type=radio onchange="onchange_print_type()" name="print_type" value="sales_order" checked> Sales Order<br />
			<div id="div_sales_order_dropdown" style="display:none">
				<table>
					<tr>
						<td>Title Print :</td>
						<td>
							<select id="dropdown_print_title" name="title_print" onchange="onchange_title_print()">
								<option value="sales_order">Sales Order</option>
								<option value="quotation">Quotation</option>
								<option value="proforma_invoice">Proforma Invoice</option>
								<option value="other">Others</option>
							</select>
						</td>
					</tr>
					<tr id="tr_title_other" style="display:none">
						<td>Title :</td>
						<td><input type="text" name="title_other_print"/></td>
					</tr>
				</table>
			</div>
			{if $config.sales_order_print_checklist_template}
				<input onchange="onchange_print_type()" type=radio name="print_type" value="checklist" /> Checklist<br />
			{/if}
			<input onchange="onchange_print_type()" type=radio name="print_type" value="picking_list"> Picking List<br /> 
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">
			<input type=button value="Print" onclick="print_ok()">
			<input type=button value="Cancel" onclick="curtain_clicked();">
		</td>
	</tr>
</table>
</p>
</form>
</div>
<!--End print dialog -->

<!-- generate po popup -->
<div id="div_generate_po" class="curtain_popup" style="position:absolute;z-index:10000;width:500px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_generate_po_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default"><span style="float:left;">Genereate Sales Order to Purchase Order</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_generate_po_content" style="padding:2px;">
		<form name="f_so_to_po" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_generate_po" />
			
			You can select multiple Sales Order to generate at once.<br />
			<b>Search Order No: </b>
			<input type="text" name="order_no" id="inp_order_no" />
			<input type="button" value="Add" id="inp_add_order_no" onClick="add_so_to_order_no_list();" />
			<span id="span_adding_order_no" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
			<br />
			<div id="div_autocomplete_order_no_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
			<div style="height:20px;">
				<span id="span_loading_order_no" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
            
            </div>
			<div id="div_order_no_list" style="border:2px inset black;background-color:#fff;height:200px;">
				<table width="100%" border="1" style="border-collapse:collapse;">
					<tr bgcolor="#cccccc">
						<th>&nbsp;</th>
						<th>Order No</th>
						{if !$config.sales_order_hide_batch_code}
							<th>Batch Code</th>
						{/if}
						<th>Customer PO</th>
						<th>Order Date</th>
					</tr>
					<tbody id="tbody_order_no_list">
					</tbody>
				</table>
			</div>
			
			<ul>
				<li> PO will split by department and vendor.
				<li> PO vendor will base on 
					<select name="vendor_type">
						<option value="last_vendor">Last Vendor</option>
						<option value="master_vendor">Master Vendor</option>
					</select>	 
				</li>
				<li>
					Deliver to 
					{if $BRANCH_CODE eq 'HQ'}
						<select name="po_branch_id">
							{foreach from=$branches key=bid item=r}
								<option value="{$bid}" {if $bid eq $sessioninfo.branch_id}selected {/if}>{$r.code}</option>
							{/foreach}
						</select>
					{else}
						<b>{$BRANCH_CODE}</b>
						<input type="hidden" name="po_branch_id" value="{$sessioninfo.branch_id}" />
					{/if}
				</li>
			</ul>
			<p align="center">
				<input type="button" value="Generate PO" onClick="generate_po();" id="inp_generate_po" />
				<br />
				<span id="span_generating_po" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Processing...</span>
			</p>
		</form>
	</div>
</div>

<div id="div_generate_po_result" style="position:absolute;background-color:#fff;border:2px solid black;height:200px;width:400px;z-index:10001;display:none;">
	<span style="float:right;padding:2px;">
		<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable" />
	</span>
	<div id="div_generate_po_result_content" style="height:180px;overflow:auto;">
	</div>
</div>

<div id="receipt_details_container" style="position:absolute;background-color:#fff;border:2px solid black;padding:5px;height:400px;width:600px;z-index:10002;display:none;">
	<span style="float:right;padding:2px;">
		<img src="/ui/closewin.png" align="absmiddle" onClick="close_receipt_details();" class="clickable" />
	</span>
	<div id="receipt_details"></div>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>


<h1>{$PAGE_TITLE}</h1>

{if $smarty.request.err_msg}
    <p><img src="ui/cancel.png" align="absmiddle"> {$smarty.request.err_msg}</p>
{/if}

{if $smarty.request.t eq 'delete'}
	<p><img src="ui/terminated.png" align="absmiddle" /> Order ID#{$smarty.request.save_id} was deleted</p>
{elseif $smarty.request.t eq 'cancel'}
	<p><img src="ui/cancel.png" align="absmiddle" /> ORder ID#{$smarty.request.save_id} was cancelled</p>
{elseif $smarty.request.t eq 'confirm'}
    <p><img src="ui/icons/accept.png" align="absmiddle" /> Order ID#{$smarty.request.save_id} confirmed. </p>
{elseif $smarty.request.t eq 'reset'}
    <p><img src="ui/notify_sku_reject.png" align="absmiddle"> Order ID#{$smarty.request.save_id} was reset.</p>
{elseif $smarty.request.t eq 'approve'}
	<p><img src="ui/approved.png" align="absmiddle"> Order ID#{$smarty.request.save_id} was Fully Approved.</p>
{/if}

<ul>
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Order</a></li>
	{if $config.enable_reorder_integration}
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="?a=upload_csv">Create New Order by Upload CSV</a></li>
	{/if}
	<li>
		<img src="ui/icons/application_add.png" align="absmiddle" /> <a href="javascript:void(show_generate_po_popup());">Generate Sales Order to Purchase Order</a>
	</li>
</ul>

<div class="row mx-3 mb-3">
	<div class="col">
		<div class=tab style="white-space:nowrap;">
			<a href="javascript:void(list_sel(1))" id=lst1 class="a_tab btn btn-outline-primary btn-rounded">Saved Order</a>
			<a href="javascript:void(list_sel(2))" id=lst2 class="a_tab btn btn-outline-primary btn-rounded">Waiting for Approval</a>
			<a href="javascript:void(list_sel(5))" id=lst5 class="a_tab btn btn-outline-primary btn-rounded">Rejected</a>
			<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab btn btn-outline-primary btn-rounded">Cancelled/Terminated</a>
			<a href="javascript:void(list_sel(4))" id=lst4 class="a_tab btn btn-outline-primary btn-rounded">Approved</a>
			<a href="javascript:void(list_sel(7))" id=lst7 class="a_tab btn btn-outline-primary btn-rounded">Delivered</a>
			<a href="javascript:void(list_sel(8))" id=lst8 class="a_tab btn btn-outline-primary btn-rounded">Exported To POS</a>
		
	</div>
	<br>
	<div class="col">
		<a class="a_tab" id="lst6">Search [<span class="link" onclick="toggle_search_info();">?</span>] 
			<input id="inp_item_search" onKeyPress="search_input_keypress(event);" />
			 <input type="button" class="btn btn-primary" value="Go" onClick="list_sel(6);" />
		</a>
		
	</div>
</div>
<
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>
<div id="order_list" style="border:1px solid #000">
</div>
</div>

{include file='footer.tpl'}
<script>

list_sel();
reset_order_no_autocomplete();
onchange_print_type();

{literal}
new Draggable('div_generate_po',{ handle: 'div_generate_po_header'});
{/literal}
</script>
