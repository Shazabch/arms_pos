{*
4/20/2011 6:37:38 PM Alex
- add link to download barcode font
4/22/2011 10:32:46 AM Alex
- Auto add min and max code at printing dialog box
- set branch id and batch no to multiple selected
4/26/2011 10:24:11 AM Alex
- add check config hq_print or subbranch_print and branch_code
4/26/2011 6:06:04 PM Alex
- add check config hq_reprint or subbranch_reprint and branch_code
6/15/2011 3:27:35 PM Alex
- add no of voucher used 
6/16/2011 4:12:29 PM Alex
- fix checking show cancel img bugs
8/22/2011 2:16:48 PM Alex
- add show numbering and multiple branch under allow interbranch
10/28/2011 3:34:32 PM Alex
- add format change able to set preprinted and without cutting_line
11/4/2011 11:53:32 AM Justin
- Fixed the data filter that shows transparent date menu.
- Temporary disable the date filter of "Used Time".
11/9/2011 11:02:56 AM Alex
- enable the date filter of "Used Time"
11/10/2011 4:42:43 PM Alex
- add expired filter 
1/31/2013 4:24 PM Justin
- Enhanced to feature voucher listing to able to print member redeem format.

2/21/2017 3:32 PM Justin
- Bug fixed on voucher value options is not showing as per voucher setup.

4/21/2017 9:43 AM Khausalya 
- Enhanced changes from RM to use config setting. 

1/13/2020 9:17 AM William
- Enhanced to add new column "Actual Voucher Code", only show if user got privilege "MST_VOUCHER_PRINT".

06/29/2020 10:34 AM Sheila
- Updated button css.
*}

{include file=header.tpl}

{literal}
<style>
#voucher_listing tr:nth-child(odd){
	background-color: #eeeeee;
}

#voucher_listing .red{
	color:red;
}

td .vbranch_hide {
	display:none;
	position:absolute;
	color:black; 
	background-color:#fff99d; 
	padding:5px;
	border:1px solid #ccc;
}

td.voucher_b:hover .vbranch_hide {
    display:block;
}

td.voucher_b .vbranch_short {
	color:#0000ff;
}
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
var sms_credit = 0;
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
function date_filter_changed(){
	if(document.f_a['date_filter'].value=='')   $('span_date_filter').hide();
	else	$('span_date_filter').show();
}

function list_sel(n,s){

	var i;
	for(i=1;i<=3;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('coupon_list_id').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
//	if (n==0) pg +='&search='+ $('search').value ;

	// reload list
	new Ajax.Updater('voucher_list_id', 'masterfile_coupon.php', {
		parameters: 'a=ajax_load_voucher_list&t='+n+pg,
		evalScripts: true
		});
}

function reset_page(){
	if (document.f_a.s)
		document.f_a.s.selectedIndex=0;
		
	document.f_a.submit();
}

function show_print(branch_id,batch_no){
	if (document.f_prn['branch_id'])
		document.f_prn['branch_id'].value=branch_id;

	prn_load_batch(document.f_prn['branch_id'],batch_no);
	curtain(false);
	center_div('print_dialog');
	$('print_dialog').style.display = '';
}

function print_cancel(){
	hidediv('print_dialog');
}

function print_ok(){
    var msg = check_min_max_code();
    if (msg){
		alert("Error: "+msg);
    	return;
    }

	new Ajax.Request(phpself,{
		method:'post',
		parameters: {
			a: "ajax_check_printed_voucher",
			branch_id: document.f_prn['branch_id'].value,
			batch_no: document.f_prn['batch_no'].value,
			from_code: document.f_prn['from_code'].value,
			to_code: document.f_prn['to_code'].value
		},
	    evalScripts: true,
		onComplete: function(m){
		    var msg = m.responseText.trim();
            if (msg == "got"){
				var p = prompt('Fount printed voucher. Enter reason to reprint the voucher:');
				if (!p){
					alert("Unable to print. No reason is entered.");		
					return false;
				}

				document.f_prn['p'].value=p;
			}else if (msg != "ok"){
				alert(msg);
				return false;
			}

			$('print_dialog').style.display = 'none';
			//document.f_prn.target = "ifprint";
			document.f_prn.target = "_blank";
			document.f_prn.submit();
			curtain(false);
		}
	});
}

function set_trigger(code){
	$('changes_'+code).value=1;
}

function ajax_cancel_voucher(code){
	var p = prompt('Enter reason to cancel this voucher:');
	if (p==null || p.trim()==''){
		alert("Unable to cancel. No reason is entered.");
		return;
	}
	new Ajax.Request(phpself,{
		method:'post',
		parameters: {
			a: "ajax_cancel_voucher",
			code: code,
			cancel_remark: p
		},
	    evalScripts: true,
		onComplete: function(m){
		    var msg = m.responseText.trim();
            if (msg == "ok")
				reset_page();
			else
			    alert(msg);
		}
	});
}

function ajax_load_batch(ele){

	$('batch_no_id').update(_loading_);
	new Ajax.Request(phpself,{
		method:'post',
		parameters: {
			a: "ajax_load_batch",
			branch_id: ele.value,
			data: 1
		},
		onComplete: function(m){
			if (m.responseText != "NO"){
				var option=m.responseText;
			}else{
				var option="<option value='' min='' max=''>No Data</option>";
			}

			$('batch_no_id').update(option);
//		    document.f_a['batch_no'].value=batch_no;
		}
	});

}

function prn_load_batch(ele,batch_no){
	$('prn_code_id').update(_loading_);
	new Ajax.Request(phpself,{
		method:'post',
		parameters: {
			a: "ajax_load_batch",
			branch_id: ele.value
		},
		onComplete: function(m){
			if (m.responseText != "NO"){
				var option=m.responseText;
			}else{
				var option="<option value='' min='' max=''>No Data</option>";
			}

			$('prn_batch_no').update(option);
		    document.f_prn['batch_no'].value=batch_no;

			get_min_max_code();
		}
	});
}

function get_min_max_code(){
    var selected=$('prn_batch_no').selectedIndex;
	var min_code=$('prn_batch_no').options[selected].getAttribute("min");
	var max_code=$('prn_batch_no').options[selected].getAttribute("max");
	var min_max_code="";

	if (min_code && max_code)	min_max_code="(From "+min_code+" to "+max_code+")";
	
	$('prn_code_id').update(min_max_code);
	$('print_from_code').value=min_code;
	$('print_to_code').value=max_code;
}

function check_min_max_code(){
    var selected=$('prn_batch_no').selectedIndex;
	var min_code=int($('prn_batch_no').options[selected].getAttribute("min"));
	var max_code=int($('prn_batch_no').options[selected].getAttribute("max"));
	
	var from_code= int($('print_from_code').value);
	var to_code= int($('print_to_code').value);

	if ((from_code > max_code || from_code < min_code) || (to_code > max_code || to_code < min_code))	
		var msg="The input code is out of range. Please check.";

	return	msg;
}

function toggle_additional_print_option(ele){
	if (/ARMS/.test(ele.value))
		$('additional_option_id').show();
	else
		$('additional_option_id').hide();
}

function toggle_expired(ele){
	if ($(ele).value == '0'){
		$('expired_id').disabled=true;
		$('active_remark_id').disabled=true;
	}else{
		$('expired_id').disabled=false;
		$('active_remark_id').disabled=false;
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>
<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;width:430px;height:250px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get>
<input type=hidden name=a value="print_voucher">
<input type=hidden name=p value="">
<table>
	<tr>
		<td rowspan="7">
			<img src=ui/print64.png hspace=10 align=left>
		</td>
		<td colspan=2>
			<h3>Print Options</h3>
		</td>
	</tr>
	<tr>
		<td>
			<b>Branch:</b>
		</td>
		<td>
		{if $BRANCH_CODE eq 'HQ'}
			<select name=branch_id id="print_branch_id" onchange="prn_load_batch(this,'');">
				<option value="">All</option>
				{foreach from=$branches item=branch}
				<option value="{$branch.id}" {if $branch.id eq $smarty.request.branch_id}selected{/if}>{$branch.code}</option>
				{/foreach}
			</select>
		{else}
		    <input name=branch_id type="hidden" id="print_branch_id" value="{$smarty.request.branch_id}">{$BRANCH_CODE}
		{/if}
		</td>
	</tr>
	<tr>
		<td>
			<b>Batch No:</b>
		</td>
		<td>
			<select name="batch_no" id="prn_batch_no" onchange="get_min_max_code()">
			</select>
		    <span id="prn_code_id" style="color:green;"></span>
		</td>
	</tr>
	<tr>
		<td>
			<b>Code:</b>
		</td>
		<td>
			<b>From</b><input id="print_from_code" type="text" size=12 maxlength="7" name="from_code"> 
			<b>To</b> <input id="print_to_code" type="text" size=12 maxlength="7" name="to_code">
		</td>
	</tr>
	<tr>
		<td>
			<b>Format:</b>
		</td>
		<td>
			<div id="tr_normal_print_format" {if $smarty.request.batch_type ne "normal"} style="display:none;"{/if}>
				<select id="print_format_id" name='print_format' onchange="toggle_additional_print_option(this);">
					{foreach from=$print_format key=id item=format}
						<option value="{$id}" {if $smarty.request.print_format eq $id} selected {/if} >{$format.description}</option>
					{/foreach}
				</select>
			</div>
			
			<div id="tr_member_redeem_print_format" {if $smarty.request.batch_type ne "member_redeem"} style="display:none;"{/if}>
				<select id="member_redeem_print_format_id" name='member_redeem_print_format'>
					{foreach from=$config.voucher_member_redeem_print_template key=pf item=format}
						<option value="{$pf}" {if $smarty.request.member_redeem_print_format eq $pf} selected{/if} >{$format.description}</option>
					{/foreach}
				</select>
			</div>
		</td>
	</tr>
	<tr id="additional_option_id">
		<td>&nbsp;</td>
		<td>
			<input type="checkbox" id="preprinted_id" name="preprinted" value="1" {if $smarty.request.preprinted}checked {/if} >
			<label for="preprinted_id"><b>Pre-printed Format</b></label>
			<input type="checkbox" id="no_cutting_line_id" name="no_cutting_line" value="1" {if $smarty.request.no_cutting_line}checked {/if} >
			<label for="no_cutting_line_id"><b>No Cutting Line</b></label>
			
		</td>
	</tr>
	<tr>
	    <td>
	        <b>Remark:</b>
		</td>
		<td>
	        <input name='print_remark' value="" maxlength=100 size=30>
	    </td>
	</tr>
</table>
<p align=center>
	<input type=button value="Print" onclick="print_ok()"> <input type=button value="Cancel" onclick="print_cancel()">
</p>
</form>
</div>
<!---------end print------------------>

<form name=f_a class=noprint style="line-height:24px" method="get">
	<input type="hidden" name="a" value="voucher_list_data">
	<div class=stdframe style='background:#fff;'>
		{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name=branch_id onchange="ajax_load_batch(this);">
			<option value="">All</option>
			{foreach from=$branches item=branch}
			<option value="{$branch.id}" {if $branch.id eq $smarty.request.branch_id}selected{/if}>{$branch.code}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
		<b>Batch No</b>
		<select name="batch_no" id="batch_no_id">
		{if !$batch_no}
			<option value="">No Data</option>
		{else}
		    <option value="">All</option>

			{foreach from=$batch_no item=bat_no}
			<option value="{$bat_no.batch_no}" {if $smarty.request.batch_no eq $bat_no.batch_no} selected {/if}>{$bat_no.batch_no}</option>
			{/foreach}
		{/if}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<b>Voucher Value({$config.arms_currency.symbol})</b>
		<select name="voucher_value">
		    <option value="">All</option>
			{foreach from=$vs_list item=voucher_value}
				<option value="{$voucher_value}" {if $smarty.request.voucher_value eq $voucher_value} selected {/if}>{$voucher_value}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<b>Allow Interbranch</b>
		<select name="interbranch">
		    <option value="">All</option>
			{foreach from=$branches item=branch}
			<option value="{$branch.id}" {if $branch.id eq $smarty.request.interbranch}selected{/if}>{$branch.code}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<b>Actived</b>
		<select id="active_id" name="active" onchange="toggle_expired(this)">
		    <option value="">All</option>
			<option value="1" {if $smarty.request.active eq '1'}selected {/if}>Yes</option>
			<option value="0" {if $smarty.request.active eq '0'}selected {/if}>No</option>
		</select>
        &nbsp;&nbsp;&nbsp;&nbsp;
		<b>Actived Remark</b>
		<select id="active_remark_id" name="active_remark">
		    <option value="" {if $smarty.request.active_remark eq ''} selected {/if} >All</option>
			{foreach from=$config.voucher_active_remark_prefix item=a_r}
			    <option value="{$a_r}" {if $smarty.request.active_remark eq $a_r} selected {/if} >{$a_r}</option>
			{/foreach}
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<b>Expired</b>
		<select id="expired_id" name="expired">
		    <option value="">All</option>
			<option value="1" {if $smarty.request.expired eq '1'}selected {/if}>Yes</option>
			<option value="0" {if $smarty.request.expired eq '0'}selected {/if}>No</option>
		</select>
        &nbsp;&nbsp;&nbsp;&nbsp;
		<b>Cancelled</b>
		<select name="cancel_status">
		    <option value="">All</option>
			<option value="1" {if $smarty.request.cancel_status eq '1'}selected {/if}>Yes</option>
			<option value="0" {if $smarty.request.cancel_status eq '0'}selected {/if}>No</option>
		</select>
        &nbsp;&nbsp;&nbsp;&nbsp;
		<b>Date Filter</b>
		<select name="date_filter" onChange="date_filter_changed();">
			<option value="">No Filter</option>
			<option value="activated" {if $smarty.request.date_filter eq 'activated'}selected {/if}>Activated</option>
			<option value="used_time" {if $smarty.request.date_filter eq 'used_time'}selected {/if}>Used Time</option>
			<option value="last_update" {if $smarty.request.date_filter eq 'last_update'}selected {/if}>Last Update</option>
			<option value="added" {if $smarty.request.date_filter eq 'added'}selected {/if}>Added</option>
		</select>
		<span id="span_date_filter" style="{if !$smarty.request.date_filter}display:none;{/if}">&nbsp;&nbsp;
			<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
			<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			&nbsp;&nbsp;&nbsp;&nbsp;
			<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			&nbsp;&nbsp;&nbsp;&nbsp;
		</span>
		<br>
		<b>Search by Code</b>
		<input name="search_value" value="{$smarty.request.search_value}">
		&nbsp;&nbsp;
		<input type="button" value="Refresh" onclick="reset_page();">
		{if $sessioninfo.privilege.MST_VOUCHER_PRINT && !$whole_voucher.cancel_status && 
		(((!$whole_voucher.printed && (($config.voucher_hq_print && $BRANCH_CODE eq 'HQ') || ($config.voucher_subbranch_print && $BRANCH_CODE ne 'HQ'))) || 
		($whole_voucher.printed && (($config.voucher_hq_reprint && $BRANCH_CODE eq 'HQ') || ($config.voucher_subbranch_reprint && $BRANCH_CODE ne 'HQ')))))}
			<input type="button" onclick="show_print('{$smarty.request.branch_id}','{$smarty.request.batch_no}');" value="Print Voucher">
		{/if}
	</div>
	<div style="margin:5px 0" align=right id="voucher_listing_id">
	{if $voucher}
		{assign var=can_update value=''}
		<div>

			<div style="float:left;">
				<ul>
					<li> <a href="./ui/3of9/mrvcode39extma.ttf">Click here to download and install the font for printing barcode</a></li>
				</ul>
			</div>
			<div style="margin:5px;float:right;" >{$pagination}
			<br />Total {$total_row|number_format} record(s) found.
			</div>
			<br style="clear:both;">
		</div>
		<input type="hidden" name="data_batch_no" value="{$smarty.request.batch_no}">
		<table width=100% cellpadding=2 cellspacing=1 border=0 style="padding:1px;border:1px solid #000;">
			<tr bgcolor=#ffee99>
				<th rowspan=2>#</th>
			    <th rowspan=2>&nbsp;</th>
				<th rowspan=2>Branch</th>
	   			<th rowspan=2>Batch no</th>
				<th rowspan=2>Code</th>
				{if $sessioninfo.privilege.MST_VOUCHER_PRINT }
				<th rowspan=2>Actual Voucher Code</th>
				{/if}
				<th rowspan=2>Voucher Value({$config.arms_currency.symbol})</th>
				<th rowspan=2>Allow Interbranch</th>
				<th rowspan=2>Print</th>
				<th rowspan=2>Print Remark</th>
				<th rowspan=2>Reprint Reason</th>
				<th rowspan=2>Activated</th>
				<th rowspan=2>Active Remark</th>
				<th rowspan=2>Activate By</th>
				<th colspan=2>Valid Date</th>
				<th rowspan=2>Cancelled</th>
				<th rowspan=2>Cancel Remark</th>
				<th rowspan=2>Cancel By</th>
				<th colspan=2>Used(Pcs)</th>
				<th rowspan=2>Last Update</th>
				<th rowspan=2>Added</th>
				<th rowspan=2>Created By</th>
			</tr>
			<tr bgcolor=#ffee99>
			    <th>Start</th>
			    <th>End</th>
				<th>Last Timestamp</th>
	   		    <th>Quantity (pcs)</th>
			</tr>
			<tbody id="voucher_listing">
			{assign var=start_no value=$num_start}
			{foreach from=$voucher item=vou}
			
				{if $vou.cancel_status eq '1' || (!$sessioninfo.privilege.MST_VOUCHER_EDIT)|| (!$config.voucher_edit_after_print && $vou.is_print > 0) || $vou.num_used>0}
					{assign var=disable_input value=1}
				{else}
				    {assign var=can_update value=1}
	                {assign var=disable_input value=0}
	            {/if}
				<tr {if $vou.cancel_status eq '1'}style="color:#ff0000;"{/if}>
					{assign var=start_no value=$start_no+1}
					<td nowrap>{$start_no}</td>

				    <td align=center>
						{if $sessioninfo.privilege.MST_VOUCHER_CANCEL && !$vou.num_used && !$vou.cancel_status}
						<img width='15px' src="/ui/rejected.png" onclick="ajax_cancel_voucher('{$vou.code}');" title="Cancel" align=absmiddle border=0>&nbsp;&nbsp;
						{else}
							{if $vou.cancel_status eq '1'}
								<img width='15px' src="/ui/cancel.png" title="Cancel" align=absmiddle border=0>&nbsp;&nbsp;
							{/if}
	       				{/if}
					</td>
					<td align=center>{$vou.branch_desc}</td>
				    <td align=center>{$vou.batch_no}</td>
					<td align=center>{$vou.code}</td>
					{if $sessioninfo.privilege.MST_VOUCHER_PRINT}
					<td align=center>{$vou.secur_barcode}</td>
					{/if}
					<td align=center>
						{if $disable_input eq '1'}
							{$vou.voucher_value}
						{else}
						    {assign var=got_voucher_value value=''}
							<input type='hidden' id='changes_{$vou.code}' name='changes_trigger[{$vou.code}]' value=''>
							<select name="voucher_value_data[{$vou.code}]" onchange='set_trigger("{$vou.code}");'>
								{foreach from=$config.voucher_value_prefix item=value}
								<option value="{$value}" {if $vou.voucher_value eq $value} {assign var=got_voucher_value value='1'} selected {/if}>{$value}</option>
								{/foreach}
								{if !$got_voucher_value}
								<option value="{$vou.voucher_value}" selected>{$vou.voucher_value}</option>
								{/if}
							</select>
	
						{/if}
					</td>
				    <td class="voucher_b">
						{if $vou.allow_interbranch_short}
							<span class="vbranch_short">{$vou.allow_interbranch_short}</span>
							<span class="vbranch_hide">{$vou.allow_interbranch_full}</span>
						{/if}
					</td>
					<td align=center>{$vou.is_print}</td>
					<td align=center>{$vou.print_remark}</td>
					<td align=center>{$vou.reprint_reason}</td>
					<td align=center>{$vou.activated|ifzero}</td>
					<td align=center>{$vou.active_remark}</td>
					<td align=center>{$vou.active_user}</td>
					<td align=center>{$vou.valid_from|ifzero}</td>
					<td align=center>{$vou.valid_to|ifzero}</td>
					<td align=center>{$vou.cancelled|ifzero}</td>
					<td align=center>{$vou.cancel_remark}</td>
					<td align=center>{$vou.cancel_user}</td>
					<td align=center>{$vou.max_pos_time|ifzero}</td>
					<td {if $vou.num_used>1}class="red"{/if} align=center>{$vou.num_used|ifzero}</td>
					<td align=center>{$vou.last_update}</td>
					<td align=center>{$vou.added}</td>
					<td align=center>{$vou.create_user}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		<p align=center>{$pagination2}</p>
		{if $can_update}
			<p align=center><button class="btn btn-primary" name='a' value="update_voucher" >Update</button> &nbsp;&nbsp;&nbsp;&nbsp;</p>
		{/if}
	{else}
		<p align=center>- No Data -</p>
	{/if}
	</div>
</form>

{include file=footer.tpl}
{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
    
toggle_additional_print_option($('print_format_id'));
toggle_expired($('active_id'));
   
</script>
{/literal}
{/if}
