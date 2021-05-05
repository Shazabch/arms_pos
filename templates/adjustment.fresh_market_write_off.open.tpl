{*
9/3/2010 12:22:35 PM Andy
- Add checking to get parent sku only.

7/27/2011 4:19:23 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs not to round up fixed by 2.

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

12/4/2015 9:21AM DingRen
- add check login for form submit and ajax call

2/27/2017 4:43 PM Zhi Kai
- Change wording of 'General Informations' to 'General Information'.

5/28/2019 3:11 PM Andy
- Enhanced to must select Department.

06/30/2020 10:21 AM Sheila
- Updated button css.
*}

{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<style>
{literal}
input[disabled],input[readonly],select[disabled], textarea[disabled]{
  color:black;
}
{/literal}
</style>
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var global_cost_decimal_points = '{$config.global_cost_decimal_points}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}
function init_calendar(){
	Calendar.setup({
		inputField     :    "inp_adjustment_date",
		ifFormat       :    "%Y-%m-%d",
		button         :    "img_adjustment_date",
		align          :    "Bl",
		singleClick    :    true
	});
}

function add_autocomplete(){
    var sku_item_id = $('sku_item_id').value;
    var sku_item_code = $('sku_item_code').value;
    if(!sku_item_id)    return false;
	var sku_code_list = [sku_item_id];
	clear_autocomplete();
    ajax_add_multiple_item(sku_code_list);
}

function ajax_add_multiple_item(sku_code_list){
    var param_str = Form.serialize(document.f_a) + '&a=ajax_add_item_row';
    var s = $H({'sku_code_list[]': sku_code_list}).toQueryString();
	$('autocomplete_sku_indicator').show();
 	// insert new row
	ajax_request(phpself,{
		method:'post',
		parameters: param_str+'&'+s,
	    evalScripts: true,
		onFailure: function(m) {
		    $('autocomplete_sku_indicator').hide();
			alert(m.responseText);
			add_autocomplete_extra();
		},
		onSuccess: function (m) {
		    if (!/^(\s+)*<t/.test(m.responseText)&&m.responseText.trim()!=''){  // add failed
                alert(m.responseText);
                if($('btn_submit_multiple_add')){
                    $('btn_submit_multiple_add').value = 'Add';
                    $('btn_submit_multiple_add').disabled = false;
				}
			}else{
                new Insertion.Bottom($('tbody_container'),m.responseText);
                if($('div_multiple_add_popup'))	default_curtain_clicked();
			}

		},
		onComplete: function(m){
			add_autocomplete_extra();
		}
	});
}


function submit_multi_add(ele){
	var sku_code_list = [];
	$$('#tbl_multi_add input.chx_sid_list').each(function(chx){
		if(chx.checked) sku_code_list.push(chx.value);
	});
	if(sku_code_list.length<=0) return false;
	ele.value = 'Adding...';
	ele.disabled = true;
	ajax_add_multiple_item(sku_code_list);
}

// function to run after finsih add items
function add_autocomplete_extra(){
    reset_row_no();
    $('autocomplete_sku_indicator').hide();
}

function reset_row_no(){
	var e = $('tbody_container').getElementsByClassName('no');
	var total_row=e.length;
	for(var i=0;i<e.length;i++)	{
 		$(e[i]).update((i+1)+'.');
	}
	$('autocomplete_sku').select();
}

function delete_item(id){
 	if (!confirm('Remove this SKU from sheet?')) return;
 	var branch_id = document.f_a['branch_id'].value;
 	var adj_id = document.f_a['id'].value;
	ajax_request(phpself,{
		method:'post',
		parameters: 'a=ajax_delete_item&branch_id='+branch_id+'&adj_id='+adj_id+'&id='+id,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function (m) {
		    if(m.responseText.trim()=='OK'){
                Element.remove('tr_item,'+id);
				recalc_total();
				reset_row_no();
			}else{
                alert(m.responseText);
			}
    	}
	});
}

function row_recalc(id){
	var row_qty = 0;
	var row_cost = 0;
	var row_selling = 0;

	var cost = float($('cost,'+id).value);
	var selling = float($('selling_price,'+id).value);

	row_qty = float($('qty,'+id).value);
	row_selling = float(round(row_qty*selling,2));
	row_cost = float(round(row_qty*cost, global_cost_decimal_points));
	
	$('row_selling,'+id).update(row_selling.toFixed(2));
    $('row_amount,'+id).update(row_cost.toFixed(2));

	recalc_total();
}

function recalc_total(){
	var total_cost = 0;
	var total_qty = 0;
	var total_selling = 0;

	// qty
	var inp_qty = $$('#tbody_container input.qty');
	for(var i=0; i<inp_qty.length; i++){
		total_qty += float(inp_qty[i].value);
		total_qty = float(round(total_qty, global_qty_decimal_points));
	}
	
	// cost
	var span_amt = $$('#tbody_container span.row_amt');
	for(var i=0; i<span_amt.length; i++){
		total_cost += float(span_amt[i].innerHTML);
		total_cost = float(round(total_cost, global_cost_decimal_points));
	}

	// selling
	var span_selling = $$('#tbody_container span.row_selling');
	for(var i=0; i<span_selling.length; i++){
		total_selling += float(span_selling[i].innerHTML);
		total_selling = float(round(total_selling, 2));
	}

	total_cost = float(round(total_cost, global_cost_decimal_points));
	total_selling = float(round(total_selling, 2));

	$('span_total_qty').update(total_qty);
	$('span_total_selling').update(total_selling.toFixed(2));
	$('span_total_cost').update(total_cost.toFixed(2));
}

function check_form(){
	if(!document.f_a['dept_id'].value){
		alert('Please select Department.');
		document.f_a['dept_id'].focus();
		return false;
	}
	
	return true;
}

function do_save(){
	if(!check_form())	return false;
	
  if (check_login()) {
    document.f_a.a.value='save';
	document.f_a.target = "";
	document.f_a.submit();
  }	
}

function do_confirm(){
	if(!check_form())	return false;
	
  if (check_login()) {
	if (confirm('Finalise and submit for approval?')){
		document.f_a.a.value = "confirm";
		document.f_a.target = "";
		document.f_a.submit();
	}
  }
}

function do_delete(){
  if (check_login()) {
	document.f_a.reason.value = '';
	var p = prompt('Enter reason to Delete :');
	if (p==null || p.trim()=='') return;
	document.f_a.reason.value = p;
	if (confirm('Delete this sheet?')){
		document.f_a.a.value = "delete";
		document.f_a.submit();
	}
  }
}

function do_reset(){
  if (check_login()) {
    document.f_do_reset['reason'].value = '';
	var p = prompt('Enter reason to Reset :');
	if (p==null || p.trim()=='' ) return false;
	document.f_do_reset['reason'].value = p;

	if(!confirm('Are you sure to reset?'))  return false;

	document.f_do_reset.submit();
  }
	return false;
}
{/literal}
</script>

<form name="f_do_reset" method="post" style="display:none;">
	<input type=hidden name="a" value="do_reset">
	<input type=hidden name="branch_id" value="{$form.branch_id}">
	<input type=hidden name="id" value="{$form.id}" >
	<input type=hidden name=reason value="">
</form>

<!-- end of special div -->

<h1>{$PAGE_TITLE} {if $form.id<$time_value}(ID#{$form.id}){else}(New){/if}</h1>

<h3>Status:
{if $form.approved}
	Fully Approved
{elseif $form.status == 1}
	In Approval Cycle
{elseif $form.status == 5}
 	Cancelled
{elseif $form.status == 4}
	Terminated
{elseif $form.status == 3}
	In Approval Cycle (KIV)
{elseif $form.status == 2}
	Rejected
{elseif $form.status == 0}
	Draft
{/if}
</h3>

{include file=approval_history.tpl}

<form name="f_a" method=post ENCTYPE="multipart/form-data">
<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

{if $errm.top}
	<div id="err">
		<div class="errmsg">
			<ul>
				{foreach from=$errm.top item=e}
					<li> {$e}</li>
				{/foreach}
			</ul>
		</div>
	</div>
{/if}

{assign var=branch_id value=$form.branch_id|ifzero:$sessioninfo.branch_id}
<input type="hidden" name="a" value="save" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$branch_id}" />
<input type="hidden" name="reason" value="" />

<table border="0" cellspacing="0" cellpadding="4">
	<tr>
	    <td width="120"><b>Write-Off Date</b></td>
	    <td>
	        <input name="adjustment_date" id="inp_adjustment_date" size="10" value="{$form.adjustment_date|default:$smarty.now|date_format:"%Y-%m-%d"}" />
			{if !$readonly}
				<img align="absmiddle" src="ui/calendar.gif" id="img_adjustment_date" style="cursor: pointer;" title="Select Date" />
				<img src="ui/rq.gif" align="absbottom" title="Required Field">
			{/if}
	    </td>
	</tr>
	<tr>
	    <td><b>Department</b></td>
	    <td>
	        <select name="dept_id">
	            <option value="">-- Please Select --</option>
	            {foreach from=$dept item=r}
	                <option value="{$r.id}" {if $form.dept_id eq $r.id}selected {/if}>{$r.description}</option>
	            {/foreach}
	        </select>
			<img src="ui/rq.gif" align="absbottom" title="Required Field">
	    </td>
	</tr>
	<tr>
	    <td><b>Remark</b></td>
	    <td><textarea onchange="uc(this);" name="remark" cols="68" rows="2">{$form.remark}</textarea></td>
	</tr>
	<tr>
	    <td><b>Branch</b></td>
	    <td>{$branches.$branch_id.code} - {$branches.$branch_id.description}</td>
	</tr>
</table>

<br />
<div id="div_sheets">{include file='adjustment.fresh_market_write_off.open.sheet.tpl'}

{if !$readonly}
	<div style="background:#ddd;border:1px solid #999;">
		{include file='sku_items_autocomplete.tpl' multiple_add=1 fresh_market_filter='yes' is_parent_only=1}
		<span id="autocomplete_sku_indicator" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</div>
	<script>reset_sku_autocomplete();</script>
{/if}
</div>
</div>
</form>

<p id="p_submit_btn" align="center">
	{if !$readonly}
		{if (!$form.status || $form.status==2)}
		<input class="btn btn-success" name="bsubmit" type="button" value="Save & Close" onclick="do_save()" />
		{/if}

		{if $form.id<$time_value}
			<input class="btn btn-error" type="button" value="Delete" onclick="do_delete()" />
		{else}
			<input class="btn btn-error" type="button" value="Close" onclick="document.location='{$smarty.server.PHP_SELF}'" />
		{/if}

		{if (!$form.status || $form.status==2)}
		<input class="btn btn-primary" type="button" value="Confirm" onclick="do_confirm()" />
		{/if}
	{else}
	    {if $form.approved and ($sessioninfo.level>=$config.doc_reset_level)}
	        <input class="btn btn-warning" type="button" value="Reset" onclick="do_reset();" />
	    {/if}
		<input class="btn btn-error" type="button" value="Close" onclick="document.location='{$smarty.server.PHP_SELF}'" />
	{/if}
</p>

{include file='footer.tpl'}

<script>
{if $readonly}
	Form.disable(document.f_a);
{else}
	{literal}
	new Ajax.PeriodicalUpdater('', "dummy.php", {frequency:1500});
	{/literal}
	init_calendar();
{/if}
	recalc_total();
</script>
