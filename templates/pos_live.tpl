{*
10/3/2011 5:51:46 PM Andy
- Add can unset "current login user".

10/20/2011 5:09:20 PM Andy
- Fix unset user status cannot function in popup.

3/29/2012 4:25:33 PM Andy
- Add notice legend "Total Sales included discount.".

1/13/2014 5:52 PM Fithri
- show db outdated (sync error), if any, for counter
- fix bug unset status 'Current Login User' when viewing from branch

3/2/2017 1:47 PM Andy
- Disable the auto reload error when change branch.
- Add explanation of Counter Status.

5/12/2017 8:21 AM Qiu Ying
- Enhanced to show all branches sales

06/30/2020 04:00 PM Sheila
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

<script>var bid='{$smarty.request.branch_id}';</script>
{literal}
<style>
.hsort{
	cursor:pointer;
}
.hsort th:hover{
	color:#ce0000;
}


.highlight_row_title {
	background:none repeat scroll 0 0 #F9AAAE !important;
	color:#FF0000;
	font-weight:bold;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var t_date = '{$smarty.request.date}';
{literal}
var curr_sort_order = new Array();
var curr_sort = new Array();

function sort_reloadTable(col,grp)
{
	if (curr_sort[grp]==undefined || curr_sort[grp] != col)
	{
		curr_sort[grp] = col;
		curr_sort_order[grp] = 'asc';
	}
	else
	{
		curr_sort_order[grp] =  (curr_sort_order[grp] == 'asc' ? 'desc' : 'asc' );
	}
	
	SetCookie('_tbsort_'+grp, curr_sort[grp],1);
	SetCookie('_tbsort_'+grp+'_order', curr_sort_order[grp],1);

	if(grp=='branch'){
        ajax_load_branch();
	}else if(grp=='counter'){
        ajax_load_counter(bid);
	}
}

function SetCookie(cookieName,cookieValue,nDays) {
 var today = new Date();
 var expire = new Date();
 if (nDays==null || nDays==0) nDays=1;
 expire.setTime(today.getTime() + 3600000*24*nDays);
 document.cookie = cookieName+"="+escape(cookieValue)
                 + ";expires="+expire.toGMTString();
}

function LoadError(){
    new Ajax.Updater('cc_tracking', phpself+'?'+Form.serialize(document.f_pos_live)+'&a=get_cc_tracking_error',
	{
		method: 'post',
		evalScripts: true,
	});
}

function periodicLoadError(){
	new PeriodicalExecuter(function(){
		LoadError();
	}, 30);
}

function ajax_load_branch(){
	new Ajax.Updater('div_branchs_table', phpself, {
		method: 'post',
		evalScripts: true,
		parameters:{
			a: 'load_branchs_table',
			date: t_date
		}
	});
}
function periodicLoadBranch(){
    new PeriodicalExecuter(function(){
		ajax_load_branch();
	}, 30);
}

function showMore(){
	var d = document.f_pos_live["date"].value;
	window.open(phpself+'?a=load_all_branches_all_counter&branch_id=1&date='+d,'','menubar=0,toolbar=0,location=0,scrollbars=1');
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
		<div class=stdframe>
			<form method="post" name="f_pos_live">
			<input type="hidden" name="a" value="load_counter">
		
			<div class="row">
				<div class="col">
					{if $BRANCH_CODE eq 'HQ'}
					<b class="form-label">Branch: </b>
					<select class="form-control" name="branch_id">
						<option value="-1">-- All --</option>
						{foreach from=$branch_list item=r}
							<option value="{$r.id}" {if $smarty.request.branch_id eq $r.id} selected {/if}>{$r.code}</option>
							{if $smarty.request.branch eq $r.id}
								{assign var=bcode value=$r.code}
							{/if}
						{/foreach}
					</select>
					
					{else}
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
					{/if}
				</div>
	
				<div class="col">
					<b class="form-label">Date</b> 
				<div class="form-inline">
					<input class="form-control" size=23 type=text name=date value="{$smarty.request.date}" id="date">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				
				<div class="col">
					<input class="btn btn-primary mt-4" type="submit" name="submits" value="Load">
				</div>
				
			</div>
			</form>
			</div>
	</div>
</div>

<div class="alert alert-primary mx-3 rounded">
	<ul>
		<li> Click on header to sort.</li>
		<li> Click on a row for more options.</li>
		<li> Total Transaction include cancelled bill.</li>
		<li> Total Sales does not include cancelled bill.</li>
		<li> Total Sales included discount.</li>
		<li> Counter Status: <br />
			* Login: Got user login.<br />
			* Lock: User locked the counter.<br />
			* Offline: Counter failed to ping more than 30 minutes.
		</li>
	</ul>
</div>
{if  $smarty.request.branch_id eq -1 and $BRANCH_CODE eq 'HQ'}
    {include file="pos_live.all_branchs.tpl"}
{else}
	{include file="pos_live.all_counters.tpl"}
{/if}
<br>
<div class="card mx-3">
	<div class="card-body">
		<div id="cc_tracking">
			{include file="pos_live.cc_tracking_error.tpl"}
			</div>
	</div>
</div>

{literal}
<script type="text/javascript">
	periodicLoadError();
    Calendar.setup({
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
</script>
{/literal}

{include file=footer.tpl}
