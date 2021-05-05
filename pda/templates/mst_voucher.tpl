{*
10/18/2011 10:38:11 AM Justin
- Modified the form layout to fill under PDA screen.

10/18/2011 11:21:14 AM Alex
- change form same as backend form

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

11/03/2020 5:15 PM Sheila
- Fixed title, table and form css

11/05/2020 11:49 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>

var duration_valid = '{if $config.voucher_active_month_duration}{$config.voucher_active_month_duration}{else}3{/if}';

{literal}
function submit_form(){
	if(document.f_a['valid_from'].value==''){
		alert('Please key in date start.');
		return false;
	}else if(document.f_a['valid_to'].value==''){
      	alert('Please key in date end');
  		return false;
	}else if($.trim(document.f_a['codes'].value)==''){
      	alert('Please key in code');
  		return false;
	}
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function toggle_date_type(ele){
	var date_type=ele.value;

	if (date_type=='valid_duration'){
        $('#date_duration_id').show();
        $('#date_duration_id2').show();
		$('#inp_valid_duration').attr('disabled',false);

        $('#date_end_id').hide();
		$('#inp_valid_to').attr('disabled',true);
	}else{
        $('#date_duration_id').hide();
        $('#date_duration_id2').hide();
		$('#inp_valid_duration').attr('disabled',true);
		
		$('#date_end_id').show();
		$('#inp_valid_to').attr('disabled',false);
	}
}

function calculate_date_end(){
	
	var date_arr = $('#inp_valid_from').attr('value').split("-");
	var t = new Date(parseInt(date_arr[0]),(parseInt(date_arr[1])-1),parseInt(date_arr[2]));
	
	if ($('#rdo_end_id').attr('value') == 'valid_to')
		var duration=parseInt(duration_valid);
	else
		var duration=parseInt($('#inp_valid_duration').attr('value'));

	t.setMonth(t.getMonth()+duration);

	var d = (t.getDate()).toString();
	var m = (t.getMonth()+1).toString();
	var y = t.getFullYear();

    if (d.length==1) d="0"+d;
    if (m.length==1) m="0"+m;

	$('#show_date_end').attr('value',y+'-'+m+'-'+d);
	$('#inp_valid_to').attr('value',y+'-'+m+'-'+d);
}

function toggle_all_check(obj){
	$("#branch_check_id .br_checkbox").each(function (index,ele){
		ele.checked=obj.checked;
	});
}
{/literal}
</script>

<h1>
Voucher Activation
</h1>

<span class="breadcrumbs"><a href="home.php"> < Dashboard</a></span>
<div style="margin-bottom: 10px"></div>

{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
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

<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="activate_voucher" />
<input type="hidden" name="branch_id" value="{$branch_id}" />

<table cellspacing="0" cellpadding="4" border="0" width="100%">
	<tr>
	    <th align="left">Date Start</th>
	    <td>
			<input type="text" id="inp_valid_from" name="valid_from" value="{$smarty.request.valid_from|default:$smarty.now|date_format:"%Y-%m-%d"}" onchange="calculate_date_end();" maxlength="10"/> <br/> <span class="small">(YYYY-MM-DD)</span>
		</td>
	</tr>
	<tr>
	    <th align="left" style="vertical-align: top; width:20%;">
			<select name='rdo_end' id='rdo_end_id' onchange='calculate_date_end();toggle_date_type(this);' style="width:100px;">
			    <option value='valid_to'>Date End</option>
			    <option value='valid_duration'>Duration</option>
			</select>
		</th>
	    <td>
			<span id='date_end_id'>
				<input type="text" name="valid_to" value="{$smarty.request.valid_to|date_format:"%Y-%m-%d"}" id="inp_valid_to" maxlength="10" /> <br/> <span class="small">(YYYY-MM-DD)</span>
			</span>
			<span id='date_duration_id'>
			    <select name="valid_duration" id="inp_valid_duration" onchange="calculate_date_end();">
			        {section name=mon loop=13 start=1}
				        <option value="{$smarty.section.mon.index}">{$smarty.section.mon.index}</option>
              		{/section}
			    </select>

			{*
				<input type="text" name="valid_duration" value="{$smarty.request.valid_duration}" id="inp_valid_duration" size=12 />
			*}
				<b>(Months)</b>
			</span>
		</td>
	</tr>
	<tr id="date_duration_id2">
		<th align="left">Date End</th>
		<td><input id="show_date_end" readonly="1" size=12></td>
	</tr>
	<tr>
	    <th valign="top" align="left">Code</th>
		<td><textarea name="codes" rows="10" cols="16">{$smarty.request.codes}</textarea></td>
	</tr>
	<tr>
	    <th align="left">Active Remark</th>
	    <td>
			<select name="active_remark">
			{foreach from=$config.voucher_active_remark_prefix item=remark}
			    <option value="{$remark}" {if $smarty.request.active_remark eq $remark} selected {/if} >{$remark}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr>
	    <td valign="top"><b>Inter-<br />branch</b></td>
	    <td colspan=2 id="branch_check_id">
			<input type="checkbox" id="all_branch_id" onclick="toggle_all_check(this)"> <label for="all_branch_id">All</label> &nbsp;&nbsp;
			{assign var=a value=$form.interbranch}
			{foreach from=$branches key=bid item=bcode}
				<br>
				{if $bcode==$BRANCH_CODE}<input type="checkbox" checked disabled>{/if}
				<input {if $bcode==$BRANCH_CODE}style="display:none;" {else}class="br_checkbox" {/if} type="checkbox" name="interbranch[{$bid}]" id="interbranch_{$bid}" value="{$bid}" {if $bcode==$BRANCH_CODE || $form.interbranch.$bid} checked {/if} > <label for="interbranch_{$bid}">{$bcode}</label> &nbsp;&nbsp;

			{/foreach}
		</td>
	</tr>	
</table>
<p align="center">
	<input type="submit" class="btn btn-success" name="submit_btn" value="Activate" onClick="submit_form();" />
</p>
</form>
</div>
<script>
toggle_date_type($('#rdo_end_id'));
calculate_date_end();
</script>
{include file='footer.tpl'}
