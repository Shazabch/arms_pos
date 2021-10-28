z{*
12/13/2010 10:37:31 AM Alex
- add used_time filter
4/22/2011 11:11:19 AM Alex
- fix bugs while choose branch and show min and max code
8/22/2011 3:56:56 PM Alex
- add multibranch under allow interbranch and check min and max code function => check_min_max_code()
9/22/2011 3:09:45 PM Alex
- fix print get wrong voucher code
10/28/2011 3:34:25 PM Alex
- add format change able to set preprinted and without cutting_line
11/4/2011 11:53:32 AM Justin
- Fixed the data filter that shows transparent date menu.
- Temporary disable the date filter of "Used Time".
11/9/2011 11:02:56 AM Alex
- enable the date filter of "Used Time"
11/10/2011 4:42:43 PM Alex
- add expired filter 
11/21/2011 10:53:48 AM Alex
- add batch no

6/21/2012 4:01:00 PM Andy
- Add feature to voucher listing to able to print member redeem format.

8/14/2012 5:19 PM Justin
- Enhanced to have export voucher feature.

2/21/2017 3:32 PM Justin
- Bug fixed on voucher value options is not showing as per voucher setup.

4/21/2017 9:57 AM Khausalya 
- Enhanced changes from RM to use config setting.

07/16/2020 11:21 Sheila
- Updated dialog box width.
*}

{include file=header.tpl}

{if !$no_header_footer}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

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
	var tmp_a = "voucher_list_data";
	var org_a = document.f_a.a.value;
	
	if (document.f_a.search_value.value.trim()!=''){
		document.f_a.a.value=tmp_a;
		document.f_a.target="_blank";
	}
	if (document.f_a.s)	document.f_a.s.selectedIndex=0;
	document.f_a.submit();
	
	document.f_a.a.value=org_a;
	document.f_a.target="";

}

function show_print(branch_id, batch_no, type){
	if (document.f_prn['branch_id'])
		document.f_prn['branch_id'].value=branch_id;

	prn_load_batch(document.f_prn['branch_id'],batch_no);
   document.f_prn['print_batch_type'].value = type;
   $('tr_member_redeem_print_format').hide();
   $('tr_normal_print_format').show();
   
   if(type=='member_redeem'){
   
		$('tr_member_redeem_print_format').show();
		$('tr_normal_print_format').hide();
   }
	curtain(false);
	center_div('print_dialog');
	$('print_dialog').style.display = '';
}

function print_cancel(){
	hidediv('print_dialog');
}

function print_ok(){
    var msg = check_min_max_code();
    
    // check printing format
    if(!msg){
    	if(document.f_prn['print_batch_type'].value == 'member_redeem'){
    		if($('member_redeem_print_format_id').value==''){
    			alert('Please select printing format.');
    			return;
    		}
    	}else{
    		if($('print_format_id').value==''){
    			alert('Please select printing format.');
    			return;
    		}
    	}
    }
    
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

function ajax_cancel_batch(batch){

	new Ajax.Request(phpself,{
		method:'post',
		parameters: {
			a: "ajax_check_printed_voucher",
			batch_no: batch
		},
	    evalScripts: true,
		onComplete: function(m){
		    var msg = m.responseText.trim();
            if (msg == "got"){
				if (!confirm('Fount printed voucher. Are you sure you want to continue?'))
					return;				
			}
			
			var p = prompt('Please enter reason to cancel this batch:');
			if (p==null || p.trim()==''){
				alert("Unable to cancel. No reason is entered.");		
				return;
			}
			new Ajax.Request(phpself,{
				method:'post',
				parameters: {
					a: "ajax_cancel_batch",
					batch_no: batch,
					cancel_remark: p
				},
			    evalScripts: true,
				onComplete: function(m){
				    var msg = m.responseText.trim();
		            if (msg == "ok")
						document.f_a.submit();
					else
					    alert(msg);
				}
			});			
 			
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
	}else{
		$('expired_id').disabled=false;
	}
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

function export_voucher(batch_no, branch_id){
	document.f_e.batch_no.value = batch_no;
	document.f_e.branch_id.value = branch_id;
	document.f_a.target = 'ifprint';
	document.f_e.submit();
}
{/literal}
</script>
{/if}

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<!-- voucher export -->
<form name="f_e" method="post">
	<input type="hidden" name="a" value="export_voucher" />
	<input type="hidden" name="batch_no" />
	<input type="hidden" name="branch_id" />
</form>

<iframe style="visibility:hidden;" width="1" height="1" name="ifprint"></iframe>

<!-- print dialog -->
<div id="print_dialog" style="background:#fff;border:3px solid #000;width:500px;height:250px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get onsubmit="return false">
<input type=hidden name=a value="print_voucher">
<input type=hidden name=p value="">
<input type="hidden" name="print_batch_type" value="normal" />

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
			<b>From</b><input id="print_from_code" maxlength="7" type="text" size=12 name="from_code"> 
			<b>To</b> <input id="print_to_code" maxlength="7" type="text" size=12 name="to_code">
		</td>
	</tr>
	<tr id="tr_normal_print_format">
		<td>
			<b>Format:</b>
		</td>
		<td>
			<select id="print_format_id" name='print_format' onchange="toggle_additional_print_option(this);">
			    {foreach from=$print_format key=id item=format}
			    <option value="{$id}" {if $smarty.request.print_format eq $id} selected {/if} >{$format.description}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	
	<tr id="tr_member_redeem_print_format" style="display:none;">
		<td>
			<b>Format:</b>
		</td>
		<td>
			<select id="member_redeem_print_format_id" name='member_redeem_print_format'>
			    {foreach from=$config.voucher_member_redeem_print_template key=pf item=format}
				    <option value="{$pf}" {if $smarty.request.member_redeem_print_format eq $pf} selected{/if} >{$format.description}</option>
				{/foreach}
			</select>
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

<iframe style="visibility:hidden;" width="1" height="1" name="ifprint"></iframe>
<form name=f_a class=noprint style="line-height:24px" method="post">
	<input type='hidden' name=a value="voucher_list">
	<div class="card mx-3">
		<div class="card-body">
			<div class=stdframe >
				<div class="row">
					<div class="col-md-3">
						{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b>
				<select class="form-control" name=branch_id onchange="ajax_load_batch(this);">
					<option value="">All</option>
					{foreach from=$branches item=branch}
					<option value="{$branch.id}" {if $branch.id eq $smarty.request.branch_id}selected{/if}>{$branch.code}</option>
					{/foreach}
				</select>	
				{/if}
					</div>

				<div class="col-md-3">
					<b class="form-label">Batch No</b>
				<select class="form-control" name="batch_no" id="batch_no_id">
				{if !$batch_no}
					<option value="">No Data</option>
				{else}
					<option value="">All</option>
		
					{foreach from=$batch_no item=bat_no}
					<option value="{$bat_no.batch_no}" {if $smarty.request.batch_no eq $bat_no.batch_no} selected {/if}>{$bat_no.batch_no}</option>
					{/foreach}
				{/if}
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label"> Voucher Value({$config.arms_currency.symbol})</b>
				<select class="form-control" name="voucher_value">
					<option value="">All</option>
					{foreach from=$vs_list item=voucher_value}
						<option value="{$voucher_value}" {if $smarty.request.voucher_value eq $voucher_value} selected {/if}>{$voucher_value}</option>
					{/foreach}
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Allow Interbranch</b>
				<select class="form-control" name="interbranch">
					<option value="">All</option>
					{foreach from=$branches item=branch}
						<option value="{$branch.id}" {if $branch.id eq $smarty.request.interbranch}selected{/if}>{$branch.code}</option>
					{/foreach}
				</select>
				</div>

				<div class="col-md-3">
					<b class="form-label">Actived</b>
				<select class="form-control" id="active_id" name="active"  onchange="toggle_expired(this)">
					<option value="">All</option>
					<option value="1" {if $smarty.request.active eq '1'}selected {/if}>Yes</option>
					<option value="0" {if $smarty.request.active eq '0'}selected {/if}>No</option>
				</select>
				</div>

				<div class="col-md-3">
					<b class="form-label">Expired</b>
				<select class="form-control" id="expired_id" name="expired">
					<option value="">All</option>
					<option value="1" {if $smarty.request.expired eq '1'}selected {/if}>Yes</option>
					<option value="0" {if $smarty.request.expired eq '0'}selected {/if}>No</option>
				</select>
				</div>
			
				<div class="col-md-3">
					<b class="form-label">Cancelled</b>
				<select class="form-control" name="cancel_status">
					<option value="">All</option>
					<option value="1" {if $smarty.request.cancel_status eq '1'}selected {/if}>Yes</option>
					<option value="0" {if $smarty.request.cancel_status eq '0'}selected {/if}>No</option>
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Date Filter</b>
				<select class="form-control" name="date_filter" onChange="date_filter_changed();">
					<option value="">No Filter</option>
					<option value="activated" {if $smarty.request.date_filter eq 'activated'}selected {/if}>Activated</option>
					<option value="used_time" {if $smarty.request.date_filter eq 'used_time'}selected {/if}>Used Time</option>
					<option value="last_update" {if $smarty.request.date_filter eq 'last_update'}selected {/if}>Last Update</option>
					<option value="added" {if $smarty.request.date_filter eq 'added'}selected {/if}>Added</option>
				</select>
				</div>

				<span id="span_date_filter" style="{if !$smarty.request.date_filter}display:none;{/if}">&nbsp;&nbsp;
					<b>From</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
					<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
					&nbsp;&nbsp;&nbsp;&nbsp;
					<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
					<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
					&nbsp;&nbsp;&nbsp;&nbsp;
				</span>
				</div>
				<br>
			<div class="form-inline">
				<b class="form-label">Search by Code</b>
				&nbsp;<input class="form-control" name="search_value" value="{$smarty.request.search_value}">
			
				&nbsp;&nbsp;<input type="button" class="btn btn-primary" onclick="reset_page();" value='Refresh'>
			</div>
			</div>
		</div>
	</div>
	<div style="margin:5px 0" align=right id="voucher_listing_id">
		{include file="masterfile_voucher.list.tpl"}
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
new Draggable('print_dialog');
toggle_additional_print_option($('print_format_id'));
toggle_expired($('active_id')); 
</script>
{/literal}
{/if}
