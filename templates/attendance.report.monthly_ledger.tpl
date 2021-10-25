{*
12/3/2019 5:18 PM Andy
- Fixed wrong month label.

1/21/2020 3:03 PM Andy
- Enhanced the report to always show the users once they got shift assigned.
- Enhanced to show users as "Absent" if got shift but no scan and no take leave.
- Enhanced to show "Holiday" and "Leave".

2/5/2020 2:57 PM William
- Enhanced to get and disabled input "in early", "in late", "out early", "out late" value from system setting if exist.

06/25/2020 11:31 Sheila
- updated button css
*}

{include file='header.tpl'}

{if !$no_header_footer}

<style>
{literal}
div.div_ph{
	color: green;
}

div.div_leave{
	color: blue;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var MONTHLY_ATTENDANCE_LEDGER = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
	},
	// function when user change branch
	branch_changed: function(){
		this.reload_user_list();
	},
	// function when user change year
	year_changed: function(){
		this.reload_user_list();
	},
	// function when user change month
	month_changed: function(){
		this.reload_user_list();
	},
	// core function to reload user list
	reload_user_list: function(){
		if(!this.f['branch_id'].value || !this.f['y'].value || !this.f['m'].value){
			$('div_user_list').update('-');
			return;
		}
		
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
				
		var THIS = this;
		var params = $(this.f).serialize()+'&a=ajax_reload_userlist';
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update HTML
						$('div_user_list').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	toggle_all_user: function(){
		var c = $('inp_toggle_all_user').checked;
		$$('#div_user_list input.inp_user_id_list').each(function(inp){
			inp.checked = c;
		});
	},
	// core function to check form
	validate_form: function(){
		var selected_user_count = 0;
		$$('#div_user_list input.inp_user_id_list').each(function(inp){
			if(inp.checked)	selected_user_count++;
		});
		if(selected_user_count<=0){
			alert('Please Select User');
			return false;
		}
		return true;
	},
	// function when user click on show report
	submit_report: function(t){
		this.f['export_excel'].value = 0;
		
		if(t){
			if(t == 'excel'){
				this.f['export_excel'].value = 1;
			}
		}
		
		if(!this.validate_form())	return false;
		
		this.f.submit();
	}
};

function toggle_all_user(){
	MONTHLY_ATTENDANCE_LEDGER.toggle_all_user();
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
	<ul class="errmsg">
		{foreach from=$err item=e}
			<div class="alert alert-danger rounded mx-3">
				<li> {$e}</li>
			</div>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" onSubmit="return false" class="noprint stdframe" method="post">
			<input type="hidden" name="load_report" value="1" />
			<input type="hidden" name="export_excel" />
			
			<div class="row">
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<span>
					<b class="form-label">Branch: </b>
					<select class="form-control" name="branch_id" onChange="MONTHLY_ATTENDANCE_LEDGER.branch_changed();">
						{foreach from=$branch_list key=bid item=b}
							<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
				</span>
			{else}
				<input class="form-control" type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
			{/if}
				</div>
			
		
				<div class="col-md-4">
					<b class="form-label">Year: </b>
				<select class="form-control" name="y" onChange="MONTHLY_ATTENDANCE_LEDGER.year_changed();">
					<option value="">-- Please Select --</option>
					{foreach from=$year_list item=y}
						<option value="{$y}" {if $smarty.request.y eq $y}selected {/if}>{$y}</option>
					{/foreach}
				</select>
				</div>
				
				<div class="col-md-4">
					<b class="form-label">Month: </b>
				<select class="form-control" name="m" onChange="MONTHLY_ATTENDANCE_LEDGER.month_changed();">
					<option value="">-- Please Select --</option>
					{foreach from=$appCore->monthsList key=m item=m_label}
						<option value="{$m}" {if $smarty.request.m eq $m}selected {/if}>{$m_label}</option>
					{/foreach}
				</select>
				</div>
			</div>
		
			<fieldset style="width: 300px;">
				<legend>User:</legend>
				<div id="div_user_list">
					{include file='attendance.report.user_list.tpl'}
				</div>
			</fieldset>
			
			
			<fieldset >
				<legend>Settings:</legend>
				<table>
					{* Early In *}
					<tr>
						<div class="form-inline mt-2">
							<b class="form-label">Early In</b>&nbsp;
							&nbsp;&nbsp;&nbsp;=&nbsp;&nbsp; <input class="form-control" type="text" name="in_early" {if $has_settings_val}disabled{/if} value="{$smarty.request.in_early|default:$system_settings.in_early}"  onChange="mi(this);" />&nbsp; mins before start time
						</div>
						
					</tr>
					
					{* Late In *}
					<tr>
						<div class="form-inline mt-2">
							<b class="form-label">Late In</b>&nbsp;
							&nbsp;&nbsp;	&nbsp;=&nbsp;&nbsp; <input class="form-control" type="text" name="in_late" {if $has_settings_val}disabled{/if} value="{$smarty.request.in_late|default:$system_settings.in_late}"  onChange="mi(this);" />&nbsp; mins after start time
						</div>
						
					</tr>
					
					{* Early Exity *}
					<tr>
						<div class="form-inline mt-2">
							<b class="form-label">Early Exit</b>&nbsp;
							&nbsp;= &nbsp;<input class="form-control" type="text" name="out_early" {if $has_settings_val}disabled{/if} value="{$smarty.request.out_early|default:$system_settings.out_early}"  onChange="mi(this);" /> mins before end time
						</div>
						
					</tr>
					
					{* Late Exit *}
					<tr>
						<div class="form-inline mt-2">
							<b class="form-label">Late Exit</b>&nbsp;
							&nbsp;&nbsp;=&nbsp; <input class="form-control" type="text" name="out_late" {if $has_settings_val}disabled{/if} value="{$smarty.request.out_late|default:$system_settings.out_late}"  onChange="mi(this);" />&nbsp; mins after end time
						</div>
						
					</tr>
				</table>
			</fieldset>
			
			<p>
				<span>
					<b class="form-label">Filter by Status: </b>
					<select class="form-control" name="filter_status_code">
						<option value="">No Filter</option>
						{foreach from=$status_code_list key=status_code item=v}
							<option value="{$status_code}" {if $smarty.request.filter_status_code eq $status_code}selected {/if}>{$v}</option>
						{/foreach}
					</select>
				</span>
			</p>
			
			<br />
			
			<input type="button" class="btn btn-primary" value='Show Report' onClick="MONTHLY_ATTENDANCE_LEDGER.submit_report();" /> &nbsp;&nbsp;
			{if $sessioninfo.privilege.EXPORT_EXCEL}
				<button class="btn btn-info" name="output_excel" onClick="MONTHLY_ATTENDANCE_LEDGER.submit_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
			{/if}
		</form>
	</div>
</div>
{/if}

{if $smarty.request.load_report and !$err}
	{if !$data}
		<p>* No Data *</p>
	{else}
		<br />
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
		
	<div class="card mx-3">
		<div class="card-body">
			{foreach from=$data.user_list key=user_id item=user_date_data}
			<h3>{$user_date_data.user_info.u} ({$user_date_data.user_info.fullname})</h3>
			<div class="table-responsive">
				<table class="report_table table mb-0 text-md-nowrap  table-hover" width="100%">
					<thead class="bg-gray-100">
						<tr class="header">
							<th rowspan="2" width="80">Date</th>
							<th rowspan="2" width="200">Shift</th>
							<th colspan="4">Scan</th>
							<th rowspan="2" width="100">Total Working Duration</th>
							<th rowspan="2">Status</th>
						</tr>
						<tr class="header">
							<th width="60">In</th>
							<th width="60">Out</th>
							<th width="100">Duration</th>
							<th width="200">Status</th>
						</tr>
					</thead>
					
					{foreach from=$user_date_data.date_list key=date item=user_data}
						{count var=$user_data.scan_records_pair assign=rowspan}
						{assign var=pair_no value=0}
						<tbody class="fs-08">
							<tr valign="top">
								<td rowspan="{$rowspan}">{$date}</td>
								<td rowspan="{$rowspan}" align="center">
									{if $user_data.info.shift_id}
										{$user_data.info.shift_code|default:'-'} - {$user_data.info.shift_description|default:'-'}
									{else}
										-
									{/if}
									
									{if $user_data.ph_id_list}
										<div class="div_ph">
											{foreach from=$user_data.ph_id_list item=ph_id name=fph}
												[Holiday: {$ph_data.ph_list.$ph_id.ph_code} - {$ph_data.ph_list.$ph_id.ph_description}]
												{if !$smarty.foreach.fph.last}<br />{/if}
											{/foreach}
										</div>
									{/if}
								
									{if $user_data.leave_data}
										<div class="div_leave">
											[On Leave: {$user_data.leave_data.leave_code} - {$user_data.leave_data.leave_desc}]
										</div>
									{/if}
								</td>
								
								{* Scan Result *}
								{assign var=scan_result value=$user_data.scan_records_pair.$pair_no}
								
								{* In *}
								<td align="center">{$scan_result.scan_record.0.scan_time|date_format:"%T"|default:'-'}</td>
								
								{* Out *}
								<td align="center">{$scan_result.scan_record.1.scan_time|date_format:"%T"|default:'-'}</td>
								
								{* Duration *}
								<td align="right">{show_duration seconds=$scan_result.total_time display_type='short'}</td>
								
								{* Status *}
								<td align="center">
									{foreach from=$scan_result.status_code item=status_code name=sc}
										<font {if $status_code ne 'prompt'}color="red"{/if}>
											{$status_code_list[$status_code]}{if !$smarty.foreach.sc.last}, {/if}
										</font>
									{/foreach}
								</td>
								
								{* Total Working Duration *}
								<td rowspan="{$rowspan}" align="right">
									{show_duration seconds=$user_data.total_time display_type='short'}
								</td>
								
								{* All Status *}
								<td align="center" rowspan="{$rowspan}">
									{foreach from=$user_data.status_code item=status_code name=sc}
										
										<font {if $status_code ne 'prompt' and $status_code ne 'onleave'}color="red"{/if}>
											{$status_code_list.$status_code}{if !$smarty.foreach.sc.last}, {/if}
										</font>
										
									{/foreach}
								</td>
							</tr>
						</tbody>
						
						{section loop=$rowspan start=$pair_no+1 name=pn}
							{assign var=pair_no value=$smarty.section.pn.index}
							<tbody class="fs-08">
								<tr>
									{* Scan Result *}
									{assign var=scan_result value=$user_data.scan_records_pair.$pair_no}
									
									{* In *}
									<td align="center">{$scan_result.scan_record.0.scan_time|date_format:"%T"|default:'-'}</td>
									
									{* Out *}
									<td align="center">{$scan_result.scan_record.1.scan_time|date_format:"%T"|default:'-'}</td>
									
									{* Duration *}
									<td align="right">{show_duration seconds=$scan_result.total_time display_type='short'}</td>
									
									{* Status *}
									<td align="center">
										{foreach from=$scan_result.status_code item=status_code name=sc}
											<font {if $status_code ne 'prompt'}color="red"{/if}>
												{$status_code_list[$status_code]}{if !$smarty.foreach.sc.last}, {/if}
											</font>
										{/foreach}
									</td>
								</tr>
							</tbody>
						{/section}
					{/foreach}
					
					<tr class="header">
						<th align="right" colspan="6">Summary</th>
						<th align="right">{show_duration seconds=$user_date_data.total_time display_type='short'}</th>
						<td>
							{foreach from=$user_date_data.summary.by_status_code key=status_code item=day_count name=sc}
								{$status_code_list[$status_code]}: {$day_count} days{if !$smarty.foreach.sc.last},<br />{/if}
							{/foreach}
						</td>
					</tr>
				</table>
			</div>
		{/foreach}
		</div>
	</div>
	{/if}
{/if}

{if !$no_header_footer}
<script>MONTHLY_ATTENDANCE_LEDGER.initialize();</script>
{/if}
{include file='footer.tpl'}