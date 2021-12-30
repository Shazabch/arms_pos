{*
8/18/2010 11:08:54 AM Justin
- Added filter features which consists of Branch (if it is HQ mode) and Date filters when it is in verification mode.

8/27/2010 9:26:01 AM Justin
- Added 2 new tabs which represent as approval use (need config).
- Added the message display when redemption is being verified or cancel.

10/28/2010 5:00:14 PM Justin
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.

2/14/2012 11:48:43 AM Justin
- Modified to pre-fix the tab pane when access from Notification for Redemption Verification.

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".
*}

{include file='header.tpl'}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var do_verify = '{$smarty.request.do_verify}';
{if $smarty.request.do_verify}
	var tab_num = 1;
{else}
	var tab_num = '{$smarty.request.t|default:3|ifzero:3}';
{/if}
var page_num = 0;
{literal}
var search_str='';

function list_sel(selected){
	var url = '';

	if(selected==5){
		var tmp_search_str = $('inp_item_search').value.trim();

		if(tmp_search_str==''){
			return;
		}else 	search_str = tmp_search_str;
	}
	
	if(document.f_a) url = Form.serialize(document.f_a);
	
	if(typeof(selected)!='undefined'){
		tab_num = selected;
		page_num = 0;
	}
	var all_tab = $$('.tab .a_tab');
	//alert(all_tab.length);
	for(var i=0;i<all_tab.length;i++){
		$(all_tab[i]).removeClassName('active');
	}
	$('lst'+tab_num).addClassName('active');

	$('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=ajax_list_sel&ajax=1&t='+tab_num+'&p='+page_num+'&do_verify='+do_verify+'&'+url,{
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
		list_sel(5);
	}
}

function page_change(ele){
	page_num = ele.value;
	list_sel();
}

function print_slip(id,branch_id){
	document.f_print.target = 'if_print';
	document.f_print['id'].value = id;
	document.f_print['branch_id'].value = branch_id;
	//document.f_print.target = '_blank';
	document.f_print.submit();
}

function cancel_redemption(id,branch_id){
	if(!confirm('Are you sure to cancel this redemption?')) return;
	var rdmpt_code = $('rdmpt_code_'+branch_id+"_"+id).value;
	$('items_list').update(_loading_);
	new Ajax.Updater('items_list',phpself+'?a=cancel&ajax=1&t='+tab_num+'&p='+page_num,{
		parameters:{
			search_str: search_str,
			id: id,
			branch_id: branch_id
		},
		onComplete: function(msg){
			$('status_msg').innerHTML = "<img src=/ui/icons/delete.png align=top> Membership Redemption No "+rdmpt_code+" has been Cancelled<br><br>";
		},
		evalScripts: true
	});
}

{/literal}
</script>

<iframe name="if_print" style="visibility:hidden;width:1px;height:1px;"></iframe>
<form name="f_print" style="display:none;" action="membership.redemption_history.php">
	<input type="hidden" name="a" value="print_slip" />
	<input type="hidden" name="id" />
	<input type="hidden" name="branch_id" />
</form>

{if !$smarty.request.do_verify}
	
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


{else}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Redemption Verification</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{/if}
<div id="status_msg">
{if $config.membership_redemption_use_enhanced && $smarty.request.verified eq '1'}
	<img src=/ui/approved.png align=top> Membership Redemption No {$BRANCH_CODE}{$smarty.request.id|string_format:"%05d"} has been verified<br><br>
{elseif $config.membership_redemption_use_enhanced && $smarty.request.verified eq '0'}
	<img src=/ui/icons/delete.png align=top> Membership Redemption No {$BRANCH_CODE}{$smarty.request.id|string_format:"%05d"} has been Cancelled<br><br>
{/if}
</div>

{*if $smarty.request.do_verify}
	<form method=post class="form" name="f_a">
		{if $BRANCH_CODE eq 'HQ'}
		<b class="form-label">Branch</b>
		<select class="form-control" name=branch_id onchange="list_sel('1');">
		<option value="">All</option>
			{foreach from=$branches item=branch_id key=branch}
				<option value="{$branch_id}" {if $branch_id eq $smarty.request.branch_id}selected{/if}>{$branch}</option>
			{/foreach}
		</select>
		{else}
			<input name=branch_id value="{$branches.$BRANCH_CODE}" type=hidden>
		{/if}&nbsp;&nbsp;&nbsp;&nbsp;
		
		<b class="form-label">Date</b> <input class="form-control" size=10 type=text name=date value="{$smarty.request.date}" id="date">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date" onchange="list_sel('1');">&nbsp;&nbsp;&nbsp;&nbsp;
		<input class="form-control" name="search" type="button" value="Search" onclick="list_sel('1');" >
	</form>
{/if*}

<div class="card mx-3">
	<div class="card-body">
		<div class="tab row" style="white-space:nowrap;">
			<div class="col-md-6">
				{if $config.membership_redemption_use_enhanced}
				{if $smarty.request.do_verify}
				<a href="javascript:void(list_sel(1))" id=lst1 class="a_tab btn btn-outline-primary btn-rounded">Verification List</a>
				{else}
				&nbsp;<a href="javascript:void(list_sel(2))" id=lst2 class="a_tab btn btn-outline-primary btn-rounded">Waiting for Verification</a>
				{/if}
			{/if}
			{if !$smarty.request.do_verify}
				&nbsp;<a href="javascript:void(list_sel(3))" id=lst3 class="a_tab btn btn-outline-primary btn-rounded">Completed</a>
				&nbsp;<a href="javascript:void(list_sel(4))" id=lst4 class="a_tab btn btn-outline-primary btn-rounded">Cancelled</a>
			{/if}
			</div>
			<div class="col-md-6">
				<div class="form-inline">
					<a class="a_tab mt-1" id=lst5><b class="text-dark">Find Redemption</b> <input class="form-control" id="inp_item_search" onKeyPress="search_input_keypress(event);" value="{$smarty.request.search_str}" /> <input type="button" class="btn btn-primary mt-2 mt-md-0" value="Go" onClick="list_sel(5);" /></a>
				</div>
			</div>
			</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div id="items_list" >
		</div>
	</div>
</div>

{*if $smarty.request.do_verify}
{literal}
<script type="text/javascript">


    Calendar.setup({
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

</script>
{/literal}
{/if*}

<script>
list_sel(tab_num);
</script>
{include file='footer.tpl'}
