{*
4/15/2011 12:42:31 PM Alex
- change duration to 12 month

4/20/2011 12:12:05 PM Alex 
- add allow interbranch

4/20/2011 5:56:54 PM Alex
- change date valid_to while edit date valid_from

8/22/2011 2:56:50 PM Alex
- change voucher can check multiple branch

10/21/2011 11:55:43 AM Alex
- fix default tick own branch

11/8/2011 5:39:52 PM Alex
- -1 day for each month duration
- calculate total pcs voucher

11/10/2011 12:33:33 PM Alex
- check date end cannot less than today date

3/20/2012 12:05:01 PM Alex
- add batch list to be choose for activation 

5/14/2012 4:04:43 PM Justin
- Added new options "disallow_disc_promo" and "disallow_other_voucher" for user to maintain.

11/26/2012 10:13:00 AM Fithri
- pre-select the choice of Valid Date to ""Duration".
- pre-check all interbranches instead just the logged on branch.
- Added new options "disallow_disc_promo" and "disallow_other_voucher" for user to maintain.

1/11/2013 5:17 PM Justin
- Enhanced to auto display voucher codes that validate from Membership Redemption.

1/18/2013 12:05 PM Justin
- Enhanced to auto select active remark as "Redemption" whenever system found it is activate from Membership Redemption.

2/28/2013 5:41 PM Justin
- Enhanced to show branch codes while allowed to activate voucher batch/codes across branch.

6/26/2019 5:38 PM Andy
- Enhanced to can link voucher to member.

06/29/2020 10:53 AM Sheila
- Updated button css.
*}

{include file=header.tpl}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<style>
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var bid = '{$sessioninfo.branch_id}';

var duration_valid = '{if $config.voucher_active_month_duration}{$config.voucher_active_month_duration}{else}6{/if}';
{literal}

function init_calendar(){
    Calendar.setup({
        inputField     :    "inp_valid_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_valid_from",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true

        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "inp_valid_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "img_valid_to",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true

        //onUpdate       :    load_data
    });
}

function check_form(){
	var num = 0;
	var code_arr=new Array();

	if ($('activate_by_id') && $('activate_by_id').value == "batch_no"){
		//using batch
		var total_tick = 0;
		$$('.batch_no_class').each(function(ele,index){
			if (ele.checked)	total_tick++;
		});
		
		if (total_tick <= 0){
		    alert("No batch selected to be activated.");
		    return false;
		}else if (!confirm(total_tick + " batch will be activated. Are you sure?"))
		    return false;		    
	}else{
		//using codes list
		var val=$("id_codes").value.trim();
	    var val_arr=val.split("\n");
			
		for (var i=0;i<val_arr.length;i++){
	
		    var v=val_arr[i].trim();
			if (!v) continue;
	
			for (var j=0;j<code_arr.length;j++){
				if (code_arr[j] == v){
				    var arr_exist = true;
				}
			}
			
			if (!arr_exist){
				code_arr.push(v);
			}
		}
		
		if (code_arr.length <=0 ){
		    alert("No code to be activated.");
			return false;
		}else if (!confirm(code_arr.length + " records will be activated. Are you sure?"))
		    return false;
	}
	return true;
}

function toggle_date_type(ele){
	var date_type=ele.value;

	if (date_type=='valid_duration'){
		$('date_duration_id').show();
		$('date_duration_id2').show();

		$('inp_valid_duration').enable();

        $('date_end_id').hide();
        $('inp_valid_to').disable();
	}else{
		$('date_duration_id').hide();
		$('date_duration_id2').hide();
		
		$('inp_valid_duration').disable();

		$('date_end_id').show();
        $('inp_valid_to').enable();
	}
}

function calculate_date_end(){

	var date_arr = $('inp_valid_from').value.split("-");
	
	var t = new Date(parseInt(date_arr[0]),(parseInt(date_arr[1]-1,10)),parseInt(date_arr[2],10));
	
	if ($('rdo_end_id').value == 'valid_to')
		var duration=parseInt(duration_valid);
	else
		var duration=parseInt($('inp_valid_duration').value);

	t.setMonth(t.getMonth()+duration);
	t.setDate(t.getDate()-1);

	var d = (t.getDate()).toString();
	var m = (t.getMonth()+1).toString();
	var y = t.getFullYear();

    if (d.length==1) d="0"+d;
    if (m.length==1) m="0"+m;

	$('show_date_end').value = y+'-'+m+'-'+d;
	$('inp_valid_to').value = y+'-'+m+'-'+d;

}

function toggle_all_check(obj){
	$$("#branch_check_id .br_checkbox").each(function (ele,index){
		ele.checked=obj.checked;
	});
}

function count_voucher(ele){
	// trim trailing return char if exists
	var text = ele.value.replace(/\s+$/g,"");
	var split = text.split("\n");
	var num=0;
	for (var i=0;i<split.length;i++){
		if (split[i])	num++;
	}
	
	$('total_voucher_id').innerHTML=num;
}

function check_date(ele){
	var date_arr = $('inp_valid_to').value.split("-");
	
	var t = new Date(parseInt(date_arr[0]),(parseInt(date_arr[1]-1,10)),parseInt(date_arr[2],10),23,59,59);
	var today = new Date();

	if (t < today){
		alert("Date End must over today date.");

		today.setMonth(today.getMonth()+1);
		today.setDate(today.getDate());
	
		var d = (today.getDate()).toString();
		var m = (today.getMonth()).toString();
		var y = today.getFullYear();
	
	    if (d.length==1) d="0"+d;
	    if (m.length==1) m="0"+m;
	    
   		$(ele).value = y+'-'+m+'-'+d;
	}
}

function tick_all_batch(ele){
	$$(".batch_no_class").each(function(obj, index){
		obj.checked=ele.checked;
	});
	count_total_batch();	
}

function count_total_batch(){
	var total_batch = 0;
	$$(".batch_no_class").each(function(obj, index){
		if (obj.checked)	total_batch++;		
	});
	$('total_batch_id').update(total_batch);
}

function toggle_view_type(ele){
	if (ele.value == "batch_no"){
		$("view_batch_id").show();
		$("view_code_id").hide();
	}else{
		$("view_batch_id").hide();
		$("view_code_id").show();	
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err style="color:red;">
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul>
{/if}

{if $suc}
<ul class=err>
{foreach from=$suc item=s}
<li><font color="green"> {$s} </font></li>
{/foreach}
</ul>
{/if}
<p>
<font color="red">*</font> <b>Noted: </b>Codes can only be activated by assigned branches.

<form name="f_a" onsubmit="return check_form()">
    <input type="hidden" name="id" id="id" value="">
	<input type="hidden" name="branch_id" id="branch_id" value="">
	<input type="hidden" name="mr_id" id="mr_id" value="{$form.mr_id}">
	<input type="hidden" name="mr_branch_id" id="mr_branch_id" value="{$form.mr_branch_id}">

	<div class="stdframe">
	<table>
		<tr>
		    <td><b>Valid Date</b></td>
		    <td style="width:100px"><b>Date Start</b></td>
			<td>
				<input type="text" name="valid_from" value="{$form.valid_from}" id="inp_valid_from" readonly="1" size=12 onchange="calculate_date_end()" />
				<img align="absmiddle" src="ui/calendar.gif" id="img_valid_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
			</td>
		</tr>
		<tr>
		    <td>&nbsp;</td>
		    <td>
				<select name='rdo_end' id='rdo_end_id' onchange='calculate_date_end();toggle_date_type(this);'>
				    <option value='valid_to'>Date End</option>
				    <option id="opt_valid_duration" value='valid_duration'>Duration</option>
				</select>
			</td>
		    <td>
				<span id='date_end_id'>
					<input type="text" name="valid_to" value="{$form.valid_to}" id="inp_valid_to" onchange="check_date(this)" readonly="1" size=12 />
					<img align="absmiddle" src="ui/calendar.gif" id="img_valid_to" style="cursor: pointer;" title="Select Date"/>
				</span>
				<span id='date_duration_id'>
				    <select name="valid_duration" id="inp_valid_duration" onchange="calculate_date_end();">
				        {section name=mon loop=13 start=1}
					        <option id="opt_duration_{$smarty.section.mon.index}" value="{$smarty.section.mon.index}">{$smarty.section.mon.index}</option>
	              {/section}
				    </select>
					<b>(Months)</b>
				</span>
			</td>
		</tr>
		<tr id="date_duration_id2">
		    <td>&nbsp;</td>
		    <td>
				<b>Date End</b>
			</td>
			<td><input id="show_date_end" readonly="1" size=12></td>
		</tr>
		{if $config.voucher_add_activate_option}
			<tr>
				<td><b>Activate by</b></td>
				<td>
					<select id="activate_by_id" name="activate_by" onchange="toggle_view_type(this)" {if $form.mr_id && $form.mr_branch_id}onfocus="this.blur();" readonly{/if}>
						<option value="code" {if $smarty.request.activate_by eq "code"} selected {/if}>Codes</option>
						<option value="batch_no" {if $smarty.request.activate_by eq "batch_no"} selected {/if}>Batch No</option>
					</select>
				</td>
			</tr>
		{/if}
		<tr id="view_code_id">
		    <td>
				<b>List of codes</b><br /><br />
				<b>Total voucher<br /> in box = <span id="total_voucher_id">0</span></b>
			</td>
		    <td colspan=2><textarea id="id_codes" onkeyup="count_voucher(this)" cols="20" rows="20" height="200px" name="codes" {if $form.mr_id && $form.mr_branch_id}readonly{/if}>{$form.codes}</textarea></td>
		</tr>
		{if $config.voucher_add_activate_option}
			<tr id="view_batch_id">
				<td>
					<b>List of batch no</b><br /><br />
					<b>Total batch no<br /> selected in box = <span id="total_batch_id">0</span></b>
				</td>
				<td colspan=2>
					<div style="width:300px;height:330px;border:1px solid black;padding:5px;overflow-x:hidden;overflow-y:auto;">
						<input id="all_id" type="checkbox" onclick="tick_all_batch(this);"><label for="all_id"><b>All</b></label><br />
						{foreach name="foreach_batch" from=$batch_nos key=batch_no item=i}
							{if $config.voucher_allow_cross_branch_activate && ($smarty.foreach.foreach_batch.first || $i.branch_code ne $prv_branch_code)}
								<br /><b>{$i.branch_code}</b><br />
							{/if}
							<input id="batch_no_{$batch_no}_id" class="batch_no_class" name="batch_nos[{$batch_no}]" onchange="count_total_batch()" value="{$batch_no}" type="checkbox" {if $smarty.request.batch_nos.$batch_no eq $batch_no} checked {/if}><label for="batch_no_{$batch_no}_id"><b>Batch No:</b> {$batch_no} (From {$i.min_code} To {$i.max_code})</label><br />
							{assign var=prv_branch_code value=$i.branch_code}
						{/foreach}
					</div>
				</td>
			</tr>
		{/if}
		<tr>
		    <td><b>Active Remark</b></td>
		    <td colspan=2>
		        <select name="active_remark">
				{foreach from=$config.voucher_active_remark_prefix item=remark}
				    <option value="{$remark}" {if $form.active_remark eq $remark} selected {/if}>{$remark}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
		    <td><b>Interbranch</b></td>
		    <td colspan=2 id="branch_check_id">
				<input type="checkbox" id="all_branch_id" onclick="toggle_all_check(this)"> <label for="all_branch_id">All</label> &nbsp;&nbsp;
				{assign var=a value=$form.interbranch}
				{foreach from=$branches key=bid item=bcode}
					{if $bcode==$BRANCH_CODE}<img src="ui/checked.gif">{/if}
					<input {if $bcode==$BRANCH_CODE}style="display:none;" {else}class="br_checkbox" {/if} type="checkbox" name="interbranch[{$bid}]" id="interbranch_{$bid}" value="{$bid}" {if $bcode==$BRANCH_CODE || $form.interbranch.$bid} checked {/if} > <label for="interbranch_{$bid}">{$bcode}</label> &nbsp;&nbsp;
				{/foreach}
			</td>
		</tr>
		{if $config.voucher_show_advanced_options}
			<tr>
				<td valign="top"><b>More Options</b></td>
				<td colspan=2>
					<input type="checkbox" name="disallow_disc_promo" value="1" {if $form.disallow_disc_promo}checked{/if} > Disallow to use with discounts/promotions <br />
					<input type="checkbox" name="disallow_other_voucher" value="1" {if $form.disallow_other_voucher}checked{/if} > Disallow to use with other vouchers
				</td>
			</tr>
		{/if}
		
		<tr>
			<td>
				<b>
					Link to Membership
				</b>
			</td>
			<td colspan="2">
				<input type="text" name="member_link" maxlength="20" value="{$form.member_link}" /> (NRIC / Card No)
			</td>
		</tr>
	</table>
    </div>
    <p>
		<button class="btn btn-primary" name=a value="activate_form">Activate Codes</button>
	</p>
</form>
</p>

{include file=footer.tpl}

<script>
init_calendar();
{if $config.voucher_activation_pre_select_duration}
	$('opt_valid_duration').selected = true;
	$('opt_duration_{$config.voucher_activation_pre_select_duration}').selected = true;
	calculate_date_end($('inp_valid_duration'));
{/if}

{if $config.voucher_activation_pre_check_all_branches}
	$('all_branch_id').checked = true;
	toggle_all_check($('all_branch_id'));
{/if}

toggle_date_type($('rdo_end_id'));
{if !$form.valid_to}
	calculate_date_end($('inp_valid_duration'));
{/if}
count_voucher($('id_codes'));
if ($('activate_by_id')) toggle_view_type($('activate_by_id'));
</script>
