{*
06/29/2020 05:55 PM Sheila
- Updated button css.
*}
{include file='header.tpl'}

{if !$no_header_footer}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<style>
{literal}

option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}

{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var masterfile_enable_sa = int('{$config.masterfile_enable_sa}');

{literal}
var MEMBER_PACKAGE_REPORT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		this.init_calendar();
	},
	init_calendar: function(){
		Calendar.setup({
			inputField     :    "inp_date_from",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_from",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
			//,
			//onUpdate       :    load_data
		});

		Calendar.setup({
			inputField     :    "inp_date_to",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_to",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
			//,
			//onUpdate       :    load_data
		});
	},
	// function when user toggle all sales agent checkbox
	toggle_all_sa: function(){
		if(this.f['all_sa'].checked){
			$('div_sa_id_list').hide();
		}else{
			$('div_sa_id_list').show();
		}
	},
	// core function to validate form
	validate_form: function(){
		if(masterfile_enable_sa){
			// Not all sales agent
			if(!this.f['all_sa'].checked){
				var sa_count = 0;
				$$('#div_sa_id_list input.cbx_sa_id_list').each(function(inp){
					if(inp.checked)	sa_count++;
				});
				
				if(sa_count<=0){
					alert('Please select at least one Sales Agent');
					return false;
				}
			}
		}
		
		return true;
	},
	// function when users click on show report
	show_report: function(t){
		this.f['export_excel'].value = '';
		if(t != undefined){
			// Export Excel
			if(t == 'excel'){
				this.f['export_excel'].value = 1;
			}
		}
		
		if(!this.validate_form())	return false;
		
		this.f.submit();
	}
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


{if $err}
	<div class="alert alert-danger">
		<ul class="errmsg">
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
		</ul>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="noprint stdframe">
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="load_report" value="1" />
				<input type="hidden" name="export_excel" />
				
				<div class="row">
					<div class="col">
						{if $BRANCH_CODE eq 'HQ'}
						<span>
							<b class="form-label">Branch</b>
							<select class="form-control" name="branch_id">
								<option value=''>-- All --</option>
								{foreach from=$branch_list key=bid item=b}
									{if !$branch_group_list.have_group.$bid}
										<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code} - {$b.description}</option>
									{/if}
								{/foreach}
								
								{if $branch_group_list.group}
									<optgroup label='Branch Group'>
										{foreach from=$branch_group_list.group key=bgid item=bg}
											<option  class="bg" value="{$bgid*-1}" {if $bgid*-1 eq $smarty.request.branch_id}selected {/if}>{$bg.code} - {$bg.description}</option>
											{foreach from=$bg.itemList key=bid item=b}
												<option class="bg_item" value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>&nbsp;&nbsp;&nbsp;&nbsp;{$b.code} - {$b.description}</option>
											{/foreach}
										{/foreach}
									</optgroup>
								{/if}
							</select>
						</span>
					{else}
						<input class="form-control" type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
					{/if}
					</div>
					
					

					<div class="col">
							<b class="form-label">Date From</b>
							<div class="form-inline">
								<input class="form-control" type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" readonly="1" size=22 />
							&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/> &nbsp;
							</div>
					</div>

					<div class="col">
						<b>To</b>
						<div class="form-inline">
							<input class="form-control" type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" readonly="1" size=22 />
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/> &nbsp;&nbsp;
						</div>
					</div>	
					
					
					
				</div>
				<p>
					
					<div class="row">
						<div class="col">
							<b class="form-label">Show By</b>
						<select class="form-control" name="show_by">
							{foreach from=$show_by_list key=k item=v}
								<option value="{$k}" {if $smarty.request.show_by eq $k}selected {/if}>{$v}</option>
							{/foreach}
						</select>
						</div>
					
						<div class="col">
							<b class="form-label">Sort By</b>
						<div class="form-inline">
							<select class="form-control" name="sort_by">
								{foreach from=$sort_by_list key=k item=v}
									<option value="{$k}" {if $smarty.request.sort_by eq $k}selected {/if}>{$v}</option>
								{/foreach}
							</select>
							&nbsp;&nbsp;&nbsp;<select class="form-control" name="sort_order">
								<option value="top" {if $smarty.request.sort_order eq 'top'}selected {/if}>Top</option>
								<option value="btm" {if $smarty.request.sort_order eq 'btm'}selected {/if}>Bottom</option>
							</select>
						</div>
						</div>
					</div>
					
				<p>
				
				{if $config.masterfile_enable_sa}
					<p>
					<div class="form-inline form-label">
						<b class="">Sales Agent</b>
						&nbsp;&nbsp;<input type="checkbox" name="all_sa" value="1" {if !$smarty.request.sa_id_list || $smarty.request.all_sa}checked{/if} onChange="MEMBER_PACKAGE_REPORT.toggle_all_sa();" />&nbsp; All
						<div id="div_sa_id_list" style="border: 1px solid black;background-color: #fff; height: 150px;overflow-y:auto;width: 300px;{if !$smarty.request.sa_id_list || $smarty.request.all_sa}display:none;{/if}" >
							{foreach from=$sa_list key=sa_id item=sa}
								<input type="checkbox" class="cbx_sa_id_list" name="sa_id_list[{$sa_id}]" value="{$sa_id}" {if !$smarty.request.all_sa and $smarty.request.sa_id_list.$sa_id}checked {/if}>{$sa.code} - {$sa.name}<br />
							{/foreach}
						</div>
					</div>
					</p>
				{/if}
				
				<input class="btn btn-primary" type="button" value='Show Report' onClick="MEMBER_PACKAGE_REPORT.show_report();" /> &nbsp;&nbsp;
		
				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-primary" name="output_excel" onClick="MEMBER_PACKAGE_REPORT.show_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
				{/if}
			</form>
		</div>
	</div>
</div>

{if $smarty.request.load_report and !$err}
	<br />
	{if !$data}
		* No Data *
	{else}
		<h3>{$report_title}</h3>
		
		{if $smarty.request.show_by eq 'sa'}
			{include file='membership.package.rating_report.by_sa.tpl'}
		{else}
			{include file='membership.package.rating_report.by_package.tpl'}
		{/if}
	{/if}
{/if}

<script>MEMBER_PACKAGE_REPORT.initialize();</script>
{include file='footer.tpl'}