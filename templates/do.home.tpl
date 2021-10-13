{*
REVISION HISTORY
================
11/2/2007 9:29:39 AM  gary
- add do priting option for do and invoice.

1/4/2008 3:25:48 PM gary
- add price indicate.

5/8/2008 2:38:00 PM gary
- pass variable=from to do_checkout if get the list from do_checkout (diff purpose)

6/19/2008 1:41:12 PM yinsee
- add checkout parameter to do_print()

9/11/2008 5:59:03 PM yinsee
- add DO Price as price indicator 

4/22/2009 10:37 AM Andy
- add new link to create transfer DO

7/10/2009 3:08:06 PM Andy
- add let user to key in invoice no when print

8/6/2009 1:08:37 PM Andy
- add new type DO 'credit sales'

8/12/2009 12:33:55 PM Andy
- DO Module Modified

10/7/2009 10:25 AM Andy
- Add printing feature for draft and proforma DO

11/9/2009 10:25 AM edward
- add function do_chown

11/19/2009 11:46:57 AM Andy
- check create DO from PO before submit

12/24/2009 4:10:42 PM Andy
- Make DO searching to include search for invoice no

1/18/2010 11:43:26 AM Andy
- add paid checkbox

4/20/2010 2:40:19 PM Andy
- Add search & print multiple DO

6/1/2010 11:18:09 AM Alex
- add upper_lower_limit()

7/2/2010 4:11:39 PM Alex
- fix search bugs

11/12/2010 6:06:51 PM Alex
- add branch searching for consignment modules only

9/19/2011 3:49:04 PM Andy
- Add new format to create DO from data collector.
- Hide "Create DO from PO" for cash sales and credit sales DO.

11/29/2011 04:31:00 PM Andy
- Add when printing if found config "do_printing_allow_hide_date" will show option to allow user to tick "Don't Show DO Date".

1/13/2012 5:52:43 PM Justin
- Added to show new print option "Print DO (Size & Color)" when found config "do_sz_clr_print_template".

2/15/2012 4:17:52 PM Andy
- Add DO will default select config price indicator for "Create from Data Collector input".

12/14/2012 2:17:00 PM Fithri
- remove config checking on scan barcode

3/4/2013 5:39 PM Justin
- Enhanced to have print receipt option while config "do_generate_receipt_no" is turned on.

5/22/2013 3:51 PM Justin
- Modified the default import format should show "|" instead of "," separator.

7/2/2013 4:20 PM Justin
- Enhanced to show message to tell user that system will auto generate GRN if the config "do_skip_generate_grn" is not turned on.

10/2/2013 5:02 PM Justin
- Enhanced the price indicators to use latest enhanced sourced for import data from data collector and import from PO.

10/9/2013 3:37 PM Justin
- Bug fixed on javascript error while not loading DO Transfer page.

10/18/2013 3:17 PM Andy
- Fix DO Create From PO should default tick on PO Cost.

11/11/2013 11:02 AM Fithri
- add missing indicator for compulsory field

11/14/2013 11:38 AM Fithri
- add missing indicator for compulsory field

12/24/2013 4:16 PM Andy
- Enhance to accept mcode from data collector. (default import format)

12/27/2013 4:46 PM Fithri
- DO Import from Data Collector, user can choose field delimiter

1/29/2014 4:15 PM Justin
- Enhanced to add new import format for data collector.

3/25/2014 2:13 PM Justin
- Modified the wording from "Color" to "Colour".

5/22/2014 11:55 AM Justin
- Enhanced import from PO function to auto select delivery branch base on the PO while config turn on.

1/13/2015 4:24 PM Andy
- Change the invoice markup to become readonly.

6/16/2015 5:00 PM Eric
- Hide the invoice markup box when is gst DO

4/10/2017 3:51 PM Justin
- Enhanced to have Picking List report.

7/27/2017 2:01 PM Justin
- Enhanced to show export button when it is Transfer DO and under Save or Waiting for Approval.
- Enhanced to auto export DO as Excel while found the Transfer DO have been confirmed and config is turned on.

2017-09-07 14:07 PM Qiu Ying
- Enhanced to have default DO Size & Color Print Template

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/14/2018 10:00 AM HockLee
- Batch Processing feature: Add Create from Sales Order by Batch

5/30/2018 10:00 AM KUAN YEH
-  Added proforma selection,add  proforma invoice printing option

6/1/2018 2:00 PM HockLee
- Create Print Picking List by Batch link
- Create Input Packing Information link

6/11/2018 3:20 PM HockLee
- Added Print DO Packing List

9/18/2018 5:30 PM Andy
- Enhanced to hide invoice markup by default.
- Add Print Size & Color Invoice Format.
- Enhanced DO Printing to use shared templates.
- Remove DO print receipt feature.

11/13/2018 2:40 PM Andy
- Add Create Multiple DO from CSV. (Transfer DO Only)

8/5/2019 10:46 AM Justin
- Added new module "DO Multiple Confirm".

6/23/2020 04:23 PM Sheila
- Updated button css

7/15/2020 4:02 PM William
 - Enhanced "Credit Sales DO" checkout list can mark as paid and key in (Payment Date, payment type and Remark).
 
8/5/2020 8:50 AM William
- Added checking to do paid payment type selection.

8/10/2020 2:13 PM William
- Remove "-- Please Select --" option.

12/15/2020 11:44 PM William
- Enhanced to pass do_type to do printing tpl.
*}

{include file=header.tpl}
{literal}
<style>
#div_multiple_print{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
	padding: 0 !important;
}
#div_multiple_print_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#div_multiple_print_content{
    padding:2px;
}
</style>

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
{/literal}

{if isset($config.upper_date_limit) && $config.upper_date_limit >= 0}	var upper_date_limit = int('{$config.upper_date_limit}'); {/if}
{if isset($config.lower_date_limit) && $config.lower_date_limit >= 0}	var lower_date_limit = int('{$config.lower_date_limit}'); {/if}
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var do_generate_receipt_no = '{$config.do_generate_receipt_no|default:0}';
var dc_prev_price_indicate = "";
var po_prev_price_indicate = "";

{literal}
var do_inv_no = {};
var do_branch_id_list = {};
var g_do_id;
var g_bid;
function do_upload(){
	if($('file').value){
		document.f_a.submit();
	}
	else{
		alert('Please upload your file to create DO');
		return;
	}
}

function do_create_from_po(){
	po_no=$('po_no').value;
	if(po_no){
	    // use ajax checking
	    
	    $('inp_do_create_from_po').disable();
	    $('inp_do_create_from_po').value = 'Checking PO...';
	    
	    new Ajax.Request('do.php',{
			parameters:{
				a: 'ajax_check_po_no',
				po_no: po_no
			},
			onComplete: function(e){
				var msg = e.responseText;
				if(msg=='OK'){
                    document.f_b.submit();
				}else{
				    curtain(true);
					center_div($('div_po_no_checking_result').update(msg).show());
					$('inp_do_create_from_po').enable().value='Create';
				}
			}
		});
			
	}
	else{
		alert('Please keyin the PO No. to create DO');
		return;
	}
}

function continue_create_do_from_po(){
    document.f_b.submit();
}

function do_create_from_sales_order(){
	if(document.f_sales_order['order_no'].value.trim()==''){
		alert('Please enter Order No');
		return false;
	}
	
	document.f_sales_order.submit();
}

function do_create_from_sales_order_by_batch(){
	if(document.f_sales_order_by_batch['batch_code'].value.trim()==''){
		alert('Please enter Batch Code');
		return false;
	}
	
	if(confirm('Are you sure?')){
		document.f_sales_order_by_batch.submit();
	}
}

function get_selected_price_indicator(type){
	if(type == "import_data") var price_indicate_list = document.f_a['price_indicate'];
	else var price_indicate_list = document.f_b['price_indicate'];
	var selected_price_indicate;
	for(var i=0; i<price_indicate_list.length; i++){
		if(price_indicate_list[i].checked){
			selected_price_indicate = price_indicate_list[i];
			break;
		}
	}

	return selected_price_indicate;
}

function check_cannot_use_cost_indicator(type, ele){
	if(type == "import_data"){ // checking from Data Collector 
		if(dc_prev_price_indicate){
			if(dc_prev_price_indicate != ele){
				alert('You must have cost privilege in order to change price from others to cost');
			}
		}
	}else{ // checking from import from PO
		if(po_prev_price_indicate){
			if(po_prev_price_indicate != ele){
				alert('You must have cost privilege in order to change price from others to cost');
			}
		}
	}
}

function price_indicator_changed(type, obj){
	if(type == "import_data"){
		dc_prev_price_indicate = obj;
	}else{
		po_prev_price_indicate = obj;
	}
}
</script>
{/literal}

{if $smarty.request.page eq 'credit_sales'}
	{assign var=do_type value='credit_sales'}
{elseif $smarty.request.page eq 'open'}
	{assign var=do_type value='open'}
{else}
	{assign var=do_type value='transfer'}
{/if}


<div id="div_po_no_checking_result" class="curtain_popup" style="border:1px solid black;width:500px;height:300px;background:white;position:absolute;display:none;">
</div>

{if $smarty.request.msg}
<script>alert('{$smarty.request.msg|escape:javascript}');</script>
{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{if $do_type eq 'credit_sales'}Credit Sales{elseif $do_type eq 'open'}Cash Sales{else}Transfer{/if}&nbsp;{$PAGE_TITLE}
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		{if $do_type eq 'transfer' && !$config.do_skip_generate_grn}
	<b><font color="red">Important: </font><br />System will auto generate GRN once Transfer DO has been checkout.</b>
{/if}

<div id=show_last>
{if $smarty.request.t eq 'save'}
<img src=/ui/approved.png align=absmiddle> DO saved as ID#{$smarty.request.save_id}<br>
{elseif $smarty.request.t eq 'cancel'}
<img src=/ui/cancel.png align=absmiddle> DO ID#{$smarty.request.save_id} was cancelled<br>
{elseif $smarty.request.t eq 'delete'}
<img src=/ui/cancel.png align=absmiddle> DO ID#{$smarty.request.save_id} was deleted<br>
{elseif $smarty.request.t eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> DO ID#{$smarty.request.save_id} confirmed. 
{elseif $smarty.request.t eq 'approve'}
<img src=/ui/approved.png align=absmiddle> DO ID#{$smarty.request.save_id} was Fully Approved. 
{elseif $smarty.request.t eq 'reset'}
<img src=/ui/notify_sku_reject.png align=absmiddle> DO ID#{$smarty.request.save_id} was reset.
{/if}
</div>

<div class="row">
<div class="col">
	<ul style="list-style-type: none;" class="mt-2 list-group">

		{if $smarty.request.page eq 'credit_sales'}
		<li class="list-group-item list-group-item-action"><img src=ui/new.png align=absmiddle> <a href="?a=open&do_type=credit_sales">Create New Credit Sales DO</a></li>
		{elseif $smarty.request.page eq 'open'}
		<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> <a href=do.php?a=open&do_type=open>Create New Cash Sales DO</a></li>
		{else}
		<li class="list-group-item list-group-item-action"><img src=ui/new.png align=absmiddle> <a href="?a=open&do_type=transfer">Create New Transfer DO</a></li>
		<li class="list-group-item list-group-item-action"><img src=ui/new.png align=absmiddle> <a href="?a=open_upload_csv&do_type=transfer">Create Multiple DO from CSV</a></li>
		{/if}
		
		<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> 
		<a href="javascript:void(togglediv('import_data'))">Create from Data Collector input</a>
		
		<div id=import_data class=stdframe style="{if !$smarty.request.sku}display:none;{/if}margin:5px 0;">
	<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method=post ENCTYPE="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="2048000">
		<input type=hidden name=a value="create_from_upload_file">
		<input type=hidden name=create_type value="2">
		{if $errm.top}
			<div id=err><div class=errmsg><ul>
			{foreach from=$errm.top item=e}
			<div class="alert alert-danger">
				<li> {$e}</li>
			</div>
			{/foreach}
			</ul></div></div>
		{/if}
		
				<table border=0 cellspacing=0 cellpadding=4>
					<tr>
						<td valign=top width=120><b>DO Date</b></td>
						<td>
						<input name="do_date" id="added1" size=10 onchange="upper_lower_limit(this);"  maxlength=10 value="{$form.do_date|default:$smarty.now|date_format:"%Y-%m-%d"}">
						<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
						</td>
					</tr>
					
					{if $do_type eq 'transfer'}
					<tr>
						<td width=120><b>Delivery Branch</b></td>
						<td>
						<select name="do_branch_id">
							{foreach item="curr_Branch" from=$branch}
							<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
							{/foreach}
							</select>
						</td>
					</tr>
					{/if}
					
					<tr>
						<td valign=top width=120><b>Price From</b></td>
						<td>
							{if $config.sku_multiple_selling_price}
								{foreach from=$config.sku_multiple_selling_price key=i item=e}
									{if $i eq '1'}
										<span class="nowrap"><input type="radio" name="price_indicate" value="1" {if $form.price_indicate eq '1' || ($config.do_default_price_from eq 'cost' && !$form.price_indicate)}checked {/if} onClick="{if !$sessioninfo.privilege.SHOW_COST}check_cannot_use_cost_indicator('import_data', this); return false;{/if}" /><label for='dc_pi_1'>Cost</label>&nbsp;&nbsp;</span>
									{elseif $i eq '2'}
										<span class="nowrap"><input type="radio" name="price_indicate" id="dc_pi_2" value="2" {if $form.price_indicate eq '2' || ($config.do_default_price_from eq 'selling' && !$form.price_indicate && $sessioninfo.privilege.SHOW_COST) || (!$config.do_default_price_from && !$form.price_indicate)}checked {/if} onclick="price_indicator_changed('import_data', this);"><label for='dc_pi_2'>Selling (Normal)</label> &nbsp;&nbsp;</span>
									{elseif $i eq '3'}
										<span class="nowrap"><input type="radio" name="price_indicate" id="dc_pi_3" value="3" {if $form.price_indicate eq '3' || ($config.do_default_price_from eq 'last_do' and !$form.price_indicate)}checked {/if} onclick="price_indicator_changed('import_data', this);"><label for='dc_pi_3'>Last DO</label> &nbsp;&nbsp;</span>
									{elseif $i eq '4'}
										<span class="nowrap"><input type="radio" name="price_indicate" id="dc_pi_4" value="4" {if $form.price_indicate eq $i}checked {/if} {if !$sessioninfo.privilege.SHOW_COST}disabled {/if} onclick="price_indicator_changed('import_data', this);"><label for='dc_pi_4'>PO Cost</label> &nbsp;&nbsp;</span>
									{else}
										<span class="nowrap"><input type="radio" name="price_indicate" id="dc_sp_{$i}" value="{$i}" {if $form.price_indicate eq $i}checked {/if}><label for='dc_sp_{$i}' onclick="price_indicator_changed('import_data', this);">{$e}</label>&nbsp;</span>
									{/if}
								{/foreach}
							{/if}
						</td>
					</tr>
					<tr>
						<th align="left">Import Format</th>
						<td>
							<input type="radio" name="import_format" value="1" checked /> Default (ARMS CODE or MCODE | {$config.link_code_name|default:'Old Code'} | Qty)<br />
							<input type="radio" name="import_format" value="2" /> GRN Barcode (barcode)<br />
							<input type="radio" name="import_format" value="3" /> Standard (ARMS CODE or MCODE or {$config.link_code_name|default:'Old Code'} | Qty)
						</td>
					</tr>
					<tr>
						<th align="left">Delimiter</th>
						<td>
							<select name="delimiter">
								<option value="|">| (Pipe)</option>
								<option value=",">, (Comma)</option>
								<option value=";">; (Semicolon)</option>
							</select>
						</td>
					</tr>
					<tr>
						<th align=left valign=top width=80>Import File</th>
						<td align=left colspan=3><input name=files id=file type=file class="files" size=50> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span></td>
					</tr>
					
					</table>
			
			<input type="hidden" name="do_type" value="{$do_type|default:'transfer'}" />
		</form>
		<p align=center>
		<input type=button class="btn btn-primary" value="Upload" onclick="do_upload()">
		</p>
		</div>
	</div>
</div>
	</li>
		
		{if $do_type eq 'transfer'}
		<li class="list-group-item list-group-item-action"> <img src=ui/new.png align=absmiddle> 
		<a href="javascript:;" onclick="togglediv('create_from_po')">Create from PO</a>
		<div id=create_from_po class=stdframe style="{if !$smarty.request.sku}display:none;{/if}margin:5px 0;background:#fff;">
		<form name=f_b method=post>
		<input type=hidden name=a value="create_from_po">
		<input type=hidden name=create_type value="3">
		<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
		<table border=0 cellspacing=0 cellpadding=4>
		<tr>
		<th align=left width=120>DO Date</th>
		<td>
		<input name="do_date" id="added2" size=12 onchange="upper_lower_limit(this);"  maxlength=10 value="{$form.do_date|default:$smarty.now|date_format:"%Y-%m-%d"}">
		<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
		</td>
		</tr>
		
		{if !$config.do_create_and_use_branch_from_po}
			<tr>
			<td width=120><b>Delivery Branch</b></td>
			<td>
			<select name="po_do_branch_id">
				{foreach item="curr_Branch" from=$branch}
				<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
				{/foreach}
				</select>
			</td>
			</tr>
		{/if}
		
		<tr>
		<td valign=top width=120><b>Price From</b></td>
		<td>
			{if $config.sku_multiple_selling_price}
				{if $config.sku_multiple_selling_price}
					{foreach from=$config.sku_multiple_selling_price key=i item=e}
						{if $i eq '1'}
							<span class="nowrap"><input type="radio" name="price_indicate" id="po_pi_1" value="1" onClick="{if !$sessioninfo.privilege.SHOW_COST}check_cannot_use_cost_indicator('import_po', this); return false;{/if}" /><label for='po_pi_1'>Cost</label>&nbsp;&nbsp;</span>
						{elseif $i eq '2'}
							<span class="nowrap"><input type="radio" name="price_indicate" id="po_pi_2" value="2" onclick="price_indicator_changed('import_po', this);"><label for='po_pi_2'>Selling (Normal)</label> &nbsp;&nbsp;</span>
						{elseif $i eq '3'}
							<span class="nowrap"><input type="radio" name="price_indicate" id="po_pi_3" value="3" onclick="price_indicator_changed('import_po', this);"><label for='po_pi_3'>Last DO</label> &nbsp;&nbsp;</span>
						{elseif $i eq '4'}
							<span class="nowrap"><input type="radio" name="price_indicate" id="po_pi_4" value="4" checked onclick="price_indicator_changed('import_po', this);"><label for='po_pi_4'>PO Cost</label> &nbsp;&nbsp;</span>	
						{else}
							<span class="nowrap"><input type="radio" name="price_indicate" id="po_sp_{$i}" value="{$i}" onclick="price_indicator_changed('import_po', this);"><label for='po_sp_{$i}'>{$e}</label>&nbsp;</span>
						{/if}
					{/foreach}
				{/if}
			{/if}
		</td>
		</tr>
		
		<tr>
		<th align=left width=120>PO No.</th>
		<td colspan=3>
		<input id=po_no name=po_no maxlength=15 size=15 onchange="uc(this);"> <span><img src="ui/rq.gif" align="absbottom" title="Required Field"></span>
		</td>
		</tr>
		</table>
		
		<input type="hidden" name="do_type" value="{$do_type|default:'transfer'}" />
		</form>
		
		<p align=center>
		<input type=button value="Create" style="background-color:#f90; color:#fff;" onclick="do_create_from_po()" id="inp_do_create_from_po" />
		</p>
		
		</div></li>
		{/if}
		
		{if $config.allow_sales_order and $do_type eq 'credit_sales'}
			<li class="list-group-item list-group-item-action"> <img src="ui/new.png" align="absmiddle"><a href="javascript:void(togglediv('create_from_so'));"> Create from Sales Order</a>
				<div id="create_from_so" class="stdframe" style="display:none;">
					<form name="f_sales_order">
						<input type="hidden" name="do_type" value="credit_sales" />
						<input type="hidden" name="a" value="do_create_from_sales_order" />
						<b>Order No</b> <input type="text" name="order_no" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
						<input type="button" value="Create" style="background-color:#f90; color:#fff;" onclick="do_create_from_sales_order();" />
					</form>
				</div>
			</li>
		{/if}
		
		{if $config.enable_reorder_integration and $do_type eq 'credit_sales'}
			<li class="list-group-item list-group-item-action"> <img src="ui/new.png" align="absmiddle"><a href="javascript:void(togglediv('create_from_so_by_batch'));"> Create from Sales Order by Batch</a>
				<div id="create_from_so_by_batch" class="stdframe" style="display:none;">
					<form name="f_sales_order_by_batch">
						<input type="hidden" name="do_type" value="credit_sales" />
						<input type="hidden" name="a" value="do_create_from_sales_order_by_batch" />
						<b>Batch Code</b> <input type="text" name="batch_code" id="inp_batch_code" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
						<span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span><br>
						<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
						<input type="button" value="Create" style="background-color:#f90; color:#fff;" onclick="do_create_from_sales_order_by_batch();" />
					</form>
				</div>
			</li>
			<li class="list-group-item list-group-item-action">
				<img src=ui/new.png align=absmiddle> <a href="/do.batch_picking.php?a=picking_list">Print Picking List by Batch</a>
			</li class="list-group-item list-group-item-action">
			<li class="list-group-item list-group-item-action">
				<img src=ui/new.png align=absmiddle> <a href="/do.batch_picking.php?a=packing_input">Input Packing Information</a>
			</li>
		{/if}
		
		{if $config.do_can_multiple_print}
		<li class="list-group-item list-group-item-action"> <img src="ui/icons/printer.png" align=absmiddle>
		<a href="javascript:void(show_multiple_print());">Print Multiple DO</a></li>
		{/if}
		
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/do.multi_confirm_checkout.php")}
			<li class="list-group-item list-group-item-action">
				<img src="ui/table_multiple.png" align="absmiddle"> <a href="/do.multi_confirm_checkout.php?do_type={$do_type}" target="_blank">DO Multiple Confirm</a>
			</li>
		{/if}
		</ul>
</div>
</div>

	</div>
</div>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var page = '{$do_type|default:"transfer"}';

{literal}

function do_chown(id,branch_id)
{
	var p = prompt('Enter the username for new DO owner:');
	if (p.trim()=='' || p==null) return;

	new Ajax.Request('/do.php?a=chown&id='+id+'&branch_id='+branch_id+'&new_owner='+p, { evalScripts: true, onComplete: function(m) { alert(m.responseText); list_sel(1) }});
}


function list_sel(n,s){
	var i;
	for(i=0;i<=6;i++){
		if (i==n)
		    $('lst'+i).addClassName('selected');
		else
		    $('lst'+i).removeClassName('selected');
	}
	if(n==6){
		url_file='do_checkout.php';
		n=2;
		from='do';
	}
	else{
		url_file='do.php';
		from='';
	} 
	
	$('do_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==0) pg +='&search='+ $('search').value ;
	if (n==7) pg +='&search='+ $('search_bid').value ;
	
	new Ajax.Updater('do_list', url_file, {
		parameters: encodeURI('a=ajax_load_do_list&t='+n+pg+'&from='+from+'&do_type='+page),
		evalScripts: true
		});
}

function init_calendar(){
	Calendar.setup({
		inputField     :    "added1",     // id of the input field
		ifFormat       :    "%Y-%m-%d",      // format of the input field
		button         :    "t_added1",  // trigger for the calendar (button ID)
		align          :    "Bl",           // alignment (defaults to "Bl")
		singleClick    :    true
		//,
		//onUpdate       :    load_data
	}); 
	
	if(page=='transfer'){
		Calendar.setup({
			inputField     :    "added2",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "t_added2",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
			//,
			//onUpdate       :    load_data
		});     
	}
}

function do_print(id,bid,is_checkout,markup,is_under_gst){
	DO_PRINT.do_print(id,bid,is_checkout,markup,is_under_gst);
}

function curtain_clicked(){
	$('print_dialog').style.display = 'none';
	$('div_paid_status').style.display = 'none';
	curtain(false);
}

function update_paid_status(){
	if (confirm('Are you sure?')){
		$('btn_upd_paid').disabled = true;
		new Ajax.Request(phpself+'?a=ajax_update_paid',{
			parameters: $(document.f_paid).serialize(),
			onComplete: function(msg){
				if(msg.responseText=='OK'){
					var id = document.f_paid['id'].value;
					var bid = document.f_paid['bid'].value;
					if(document.f_paid['paid'].checked == true){
						$('img_paid_status_'+id+'_'+bid).src= "ui/approved.png";
					}else{
						$('img_paid_status_'+id+'_'+bid).src= "ui/icons/cancel.png";
					}
					$('div_paid_status').hide();
					curtain(false);
					alert("Update Success");
				}
				else alert(msg.responseText);
				$('span_list_loading').hide();
				$('btn_upd_paid').disabled = false;
			}
		});
	}
}

function show_paid(id, branch_id){
	new Ajax.Request(phpself,{
		parameters:{ 
			a: 'ajax_show_paid_status',
			id: id, 
			bid: branch_id
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					curtain(true);
					$('div_paid_status').update(ret['html']);
					center_div($('div_paid_status').show());
				}else{
					if(ret['error']){
						alert(ret['error']);
					}else{
						alert(str);
					}						
				}
			}catch(ex){
				alert(str);
			}
		}
	});
}

function show_multiple_print(){
    curtain(true);
	center_div($('div_multiple_print').show());
}

function search_do_for_multiple_print(){
    // check parameters
	if(document.f_multiple_print['no_from'].value.trim()==''){
		alert('Please key in Sheet No. from');
		document.f_multiple_print['no_from'].focus();
		return false;
	}
	if(document.f_multiple_print['no_to'].value.trim()==''){
		alert('Please key in Sheet No to');
		document.f_multiple_print['no_to'].focus();
		return false;
	}
	$('btn_search_multiple_print').disabled = true;
	$('btn_start_multiple_print').disabled = true;

	$('div_multiple_print_list').update(_loading_);
	
	
	new Ajax.Updater('div_multiple_print_list', phpself+'?a=ajax_search_do_for_multiple_print',{
		parameters: $(document.f_multiple_print).serialize(),
		evalScripts: true,
		onComplete: function(e){
			$('btn_search_multiple_print').disabled = false;
		}
	});
}

function toggle_do_list_for_print(ele){
	var c = ele.checked;
	var all_inp = $(document.f_multiple_print_list).getElementsBySelector("input.inp_do_list");
	for(var i=0; i<all_inp.length; i++){
        all_inp[i].checked = c;
	}
}

/*function print_receipt(){
	if(g_do_id != undefined && g_bid != undefined){
		document.f_print.id.value=g_do_id;
		document.f_print.branch_id.value=g_bid;
	}
	$('main_print_menu').hide();
	$('receipt_no_print_menu').show();
	document.f_print['print_receipt'].checked = true;
	document.f_print['print_do'].checked = false;
	if(document.f_print['print_sz_clr'] != undefined) document.f_print['print_sz_clr'].checked = false;
	if(document.f_print['no_show_date'] != undefined) document.f_print['no_show_date'].checked = false;
	if(document.f_print['acc_copy'] != undefined) document.f_print['acc_copy'].checked = false;
	if(document.f_print['store_copy'] != undefined) document.f_print['store_copy'].checked = false;
	
	show_print_dialog();
}*/

function do_export(id, bid){
	if(id == 0 || bid == 0){
		alert("process for DO Export is terminated due to invalid ID or Branch ID.");
		return;
	}

	document.f_export.id.value = id;
	document.f_export.branch_id.value = bid;
	document.f_export.submit();
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


</script>
{/literal}

<!-- multiple print popups -->
{include file='do.multiple_print.tpl' do_type=$do_type|default:'transfer'}

<!-- print dialog -->
{include file='do.print_dialog.tpl' do_type=$do_type|default:'transfer'}
<!--end print dialog-->

<!-- update paid popup -->
<div id="div_paid_status" style="background:#fff;border:3px solid #000;width:350px;position:absolute; padding:10px; display:none;z-index:10000;">
{include file="do.paid_update.tpl"}
</div>
<!-- end update paid popup -->

<!-- DO export -->
<form name="f_export">
	<input type="hidden" name="a" value="do_export" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="branch_id" value="" />
</form>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

<div class="mx-3">
	<form onsubmit="list_sel(0,0);return false;">
		<div class="tab" style="white-space:nowrap;">
		<div class="row">
			<div class="form-group">
				<div class="col-md-6">
					<a href="javascript:list_sel(1)" class="btn btn-outline-primary btn-rounded" id=lst1 >Saved DO</a>
				<a href="javascript:list_sel(2)" class="btn btn-outline-primary btn-rounded" id=lst2>Waiting for Approval</a>
				<a href="javascript:list_sel(5)" class="btn btn-outline-primary btn-rounded" id=lst5>Rejected</a>
				<a href="javascript:list_sel(3)" class="btn btn-outline-primary btn-rounded" id=lst3>Cancelled/Terminated</a>
				<a href="javascript:list_sel(4)" class="btn btn-outline-primary btn-rounded" id=lst4>Approved</a>
				<a href="javascript:list_sel(6)" class="btn btn-outline-primary btn-rounded" id=lst6>Checkout</a>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-6">
					<div class="form-inline">
						<a name=find_po id=lst0>Find DO / Invoice 
							<input id="search" class="form-control" name=pono> 
							<input type="submit" class="btn btn-primary" value="Go">
						</a>
					</div>
				</div>
			</div>
			{if $BRANCH_CODE eq 'HQ' && $config.consignment_modules}
			<div class="form-group">
				<div class="col-md-6">
					<a id=lst7>
						<b class="form-label">Branch</b>
						<select class="form-control" name="branch_id" id="search_bid">
							{foreach from=$branches item=b}
								<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/foreach}
						</select>
						<input type=button onclick="list_sel(7);" value="Go">
					</a>
				</div>
			</div>
			{/if}
			<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
		</div>
		</div>
		</form>
</div>
<div id="do_list" >
</div>
{include file=footer.tpl}

<script>
var do_id = '{$smarty.request.save_id}';
var bid = '{$sessioninfo.branch_id}';
var action = '{$smarty.request.t}';
var do_transfer_auto_export = '{$config.do_transfer_auto_export}';
{literal}
new Draggable('div_multiple_print',{ handle: 'div_multiple_print_header'});
if(page == "transfer"){
	dc_prev_price_indicate = get_selected_price_indicator("import_data");
	po_prev_price_indicate = get_selected_price_indicator("import_po");
	if(do_transfer_auto_export > 0 && (action == "confirm" || action == "approve")) do_export(do_id, bid);
}

init_calendar();
list_sel(1);
{/literal}

{if $config.enable_reorder_integration and $do_type eq 'credit_sales'}
reset_batch_code_autocomplete();
DO_PRINT.initialise();
{/if}
</script>
