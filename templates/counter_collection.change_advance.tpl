{*
01/05/2012 2:04PM Kee Kee
- Add remark column in cash advance form

4/16/2012 10:00:44 AM Andy
- Add "type" at change advance.

1/6/2014 5:13 PM Fithri
- make cash-advance value always negative
- make top-up value always positive
- show positive (absolute) value on screen

1/22/2014 5:30 PM Justin
- Enhanced to show name of cashier and collected by instead of ID.

6/28/2016 1:11 PM Andy
- Rename Top Up to Cash In.

11/10/2017 3:56 PM Justin
- Renamed the Cashier ID column become Cashier.
- Renamed the Collected By column become Approved By.
- Enhanced to add "Reason" drop down list and auto choose as per POS settings.
*}
{include file=header.tpl}
<script>
{literal}
var num_row=0;

function add_row(){
	if(num_row>0){
	    //var result=chk_row();
	    //if(result==true){
			add_new_row();
		//}
	}
	else{
		add_new_row();
		num_row++;
	}
}
function remove_row(){
	num_row--;
}

function chk_row(){
	if(num_row>0){
    	var items_code = document.f_a.elements['new[code][]'];
    	var items_description = document.f_a.elements['new[description][]'];
    	var items_qty = document.f_a.elements['new[qty][]'];
    	var items_cost = document.f_a.elements['new[cost][]'];

		if(items_code.length){
    		for(i=0;i<items_code.length;i++){
        		if(trim(items_code[i].value)=="" || trim(items_description[i].value)=="" || trim(items_qty[i].value)=="" || trim(items_cost[i].value)==""){
            	alert('You are not allowed to add row without complete this row details.');
    	        return false;
        		}
			}
		}
		else{
        	if(document.f_a.elements['new[code][]'].value=="" || document.f_a.elements['new[description][]'].value=="" || document.f_a.elements['new[qty][]'].value=="" || document.f_a.elements['new[cost][]'].value==""){
        		alert('You are not allowed to add row without complete this row details.');
            	return false;
        	}
    	}
    	return true;
	}
	//else{
    //	return false;
	//}
}

function add_new_row(){
	var new_row = $('tbl_new').insertRow(-1);
//    new_row.bgColor="grey";
	new_row.height=25;
	//new_row.innerHTML='<td nowrap><img src="/ui/remove16.png" title="Remove Item" height="12" class=clickable onclick="if(confirm(\'Are you sure?\')) Element.remove(this.parentNode.parentNode);remove_row();"></td><td><input name=user_id[]></td><td><input name=collected_by[]></td><td><input name=amount[]></td><td>-</td><td><input name=timestamp[]></td>';
	new_row.innerHTML = $('tmp_new_row').innerHTML;
	$(new_row).getElementsBySelector("input").each(function(ele){
		ele.disabled = false;
	});
	
	$(new_row).getElementsBySelector("select").each(function(ele){
		ele.disabled = false;
	});
}

function make_absolute(e) {
	var t = e.value;
	var u = t.replace(',','');
	var v = Math.abs(u);
	if (isNaN(v)) e.value = 0;
	else e.value = v;
}

{/literal}
</script>

{if $smarty.request.msg}{assign var=msg value=$smarty.request.msg}{/if}
{if $msg}<p align=center><font color=red>{$msg}</font></p>{/if}

{if $history_type eq 'ADVANCE'}
	{assign var=page_history_title value='Cash Advance'}
{elseif $history_type eq 'TOP_UP'}
	{assign var=page_history_title value='Cash In'}
{/if}
	
<h1>{$PAGE_TITLE} - {$page_history_title}
</h1>
<h3>{$BRANCH_CODE}({$counters[$smarty.request.counter_id].network_name}) - {$smarty.request.date}</h3>

<input type=button onclick="add_row();" value="Add">

<form name="f" method="post">
<input name="counter_id" value="{$smarty.request.counter_id}" type="hidden" />
<input name="cashier_id" value="{$smarty.request.cashier_id}" type="hidden" />
<input name="date" value="{$smarty.request.date}" type="hidden" />
<input name="a" value="save_change_advance" type="hidden" />
<input name="e" value="{$smarty.request.e}" type="hidden" />
<input name="history_type" value="{$history_type}" type="hidden" />

<table id="tbl_new" class="report_table" cellpadding="4" cellspacing="1" border="0">

<tr class="header">
	<th>&nbsp;</th>
	<th>Type</th>
	<th>Cashier</th>
	<th>Collected /<br />Approved By</th>
	<th>Amount</th>
	<th>Original<br />Amount</th>
	<th>Timestamp</th>
	<th>Remark</th>
	<th>Reason</th>
</tr>
<tr id="tmp_new_row" style="display:none;">
	<td>
		<img src="/ui/remove16.png" title="Remove Item" height="12" class="clickable" onclick="if(confirm('Are you sure?')) Element.remove(this.parentNode.parentNode);remove_row();">
	</td>
	<td>{$page_history_title}</td>
	<td>{*<input type="text" name="user_id[]" disabled />*}</td>
	<td>{*<input type="text" name="collected_by[]" disabled />*}</td>
	<td><input type="text" name="amount[]" disabled /></td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><input type="text" name="remark[]" disabled /></td>
	<td>
		<select name="reason[]" disabled>
			{foreach from=$config.pos_cash_advance_reason_list item=reason_value}
				<option value="{$reason_value}" {if $ca_reason_settings.setting_value eq $reason_value}selected{/if}>{$reason_value}</option>
			{/foreach}
		</select>
	</td>
</tr>

{foreach name=i from=$items item=item}
	<tr>
		<td>
			<input name="id[]" value="{$item.id}" type="hidden" />
		</td>
		<td>{$page_history_title}</td>	
		<td>{$item.cashier}</td>
		<td>{$item.approved_by}</td>
		<td>{if $history_type eq 'ADVANCE'}- {/if}<input name="amount[]" value="{$item.amount|abs|number_format:2:'.':''}" onchange="make_absolute(this);" /></td>
		<td>{$item.oamount|ifzero:$item.amount|number_format:2}</td>
		<td>{$item.timestamp}</td>
		<td>
			{$item.remark}
			<input type="hidden" value="{$item.remark}" name="remark[]" />
		</td>
		<td>
			<select name="reason[]">
				{if !$item.reason}
					<option value="" {if !$item.reason}selected{/if}>--</option>
				{/if}
				{foreach from=$config.pos_cash_advance_reason_list item=reason_value}
					<option value="{$reason_value}" {if $item.reason eq $reason_value}selected{/if}>{$reason_value}</option>
				{/foreach}
			</select>
		</td>
	</tr>
{/foreach}
</table>
<p align=center><input name=bsubmit type=submit value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;"></p
</form>



{include file=footer.tpl}
