{*
*}
{if !$form.approval_screen}
	{include file=header.tpl}
{else}
	<hr noshade size=2>
{/if}

{literal}
<style>
a{
	cursor:pointer;
}

.div_multi_select{
	
	overflow:auto;
	overflow-x:hidden;
	display: inline-block;
	padding: 4px;
}

input[disabled] {
  color:black;
  background: white;
}

input[readonly] {
  color:black;
  background: white;
}

select[disabled] {
  color:black;
  background: white;
}

.future_selling_price {
	background: none repeat scroll 0 0 #f90;
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

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var readonly = '{$readonly}';
var id = '{$form.id}';
var curr_bcode = '{$BRANCH_CODE}';
var sku_change_price_always_apply_to_same_uom = int('{$config.sku_change_price_always_apply_to_same_uom}');
var show_cost = '{$sessioninfo.privilege.SHOW_COST}';

{if $gst_settings}
var is_gst_active = 1;
{else}
var is_gst_active = 0;
{/if}

{literal}
var sku_autocomplete = undefined;

function curtain_clicked(){
	$('div_multiple_add_popup').hide();
	curtain(false);
}

var MST_FUTURE_PRICE_MODULE = {
	form_element: undefined,
	initialize: function(){
		this.form_element = document.f_a;
		var sku_autocomplete = undefined;
		var THIS = this;
		if(!this.form_element){
			alert('Batch Price Change module failed to initialize.');
			return false;
		}

		if(curr_bcode == "HQ"){
			// event to toggle branches
			$('toggle_branches').observe('click', function(){
				THIS.toggle_branches_chx(this);
			});
			// event to toggle set date by branch
			$('date_by_branch').observe('click', function(){
				THIS.toggle_date(this);
			});
		}

		var curr_date = new Date();
		var curr_year = curr_date.getFullYear();
		var curr_mth = curr_date.getMonth();
		var curr_day = curr_date.getDate();
		allowed_date = new Date(curr_year, curr_mth, curr_day);
		//allowed_date.setDate(allowed_date.getDate()-1);
		Calendar.setup({
			inputField     :    "date",     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "ds1",  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true,
			dateStatusFunc :    function (date) { // disable those date <= today
							return (date.getTime() < allowed_date.getTime()) ? true : false;
						}
		});

		// event to close commission without save
		$('submit_btn').observe('click', function(){
			if(!confirm("Are you sure want confirm?")) return;
			document.f_a.submit();
		});

		// event to close commission without save
		$('close_btn').observe('click', function(){
			if(!confirm("Close without confirm?")) return;
            window.location = phpself;
		});
	},

	toggle_branches_chx: function (obj){
		
		$$('.effective_branch').each(function(chx){
			if(obj.checked == true) chx.checked = true;
			else chx.checked = false;
			//if(chx.checked) sku_items_list.push(chx.value);
		});
	},
	toggle_date: function (obj){
		if(obj.checked == true){
			this.form_element['hour'].disabled = true;
			this.form_element['minute'].disabled = true;
			this.form_element['date'].disabled = true;
			$('ds1').hide();
		}else{
			this.form_element['hour'].disabled = false;
			this.form_element['minute'].disabled = false;
			this.form_element['date'].disabled = false;
			$('ds1').show();
		}
		$$('.dt').each(function(td){
			if(obj.checked == true) td.show();
			else td.hide();
			//if(chx.checked) sku_items_list.push(chx.value);
		});
		$$('.hr').each(function(td){
			if(obj.checked == true) td.show();
			else td.hide();
			//if(chx.checked) sku_items_list.push(chx.value);
		});
		$$('.min').each(function(td){
			if(obj.checked == true) td.show();
			else td.hide();
			//if(chx.checked) sku_items_list.push(chx.value);
		});
	},

	calendar_updated: function(cal){
		alert(cal.params.inputField.value);
		var currentTime = new Date(Y-m-d);
		alert(currentTime);
		var date_id = cal.params.inputField.id.replace("date_from_", "");

		var selected_date_list = $('items').getElementsByClassName("selected_date_list");
		var selected_date_count = selected_date_list.length;

		$A(selected_date_list).each(
			function (r,idx){
				if(r.id == cal.params.inputField.id) return;
				
				if(r.value == cal.params.inputField.value){
					alert("The date ["+cal.params.inputField.value+"] is already existed in other commission.");
					cal.params.inputField.value = "";
				}
			}
		);
	},
	
	
}

</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE} - CSV Upload</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div id=err>
	<div class="errmsg" id="errmsg">
		{foreach from=$errm.mst item=e}
	<div class="alert alert-danger rounded mx-3">
			<ul style="list-style-type: none;">
				<b><li> {$e} </li></b>
		</ul>
	</div>
	{/foreach}
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post" enctype="multipart/form-data" onsubmit="return false;">
			<input type="hidden" name="a" value="save_csv">
			<input type="hidden" name="branch_id" value="{$form.branch_id}">
			<div class="stdframe">
			<h4>General Information</h4>
			<table border="0" cellspacing="0" cellpadding="4">
				{if $BRANCH_CODE eq 'HQ'}
					<tr>
				<div class="form-inline form-label">
					<b>Set Date by Branch</b>
					&nbsp;&nbsp;<input  type="checkbox" name="date_by_branch" id="date_by_branch" value="1" {if $form.date_by_branch}checked{/if} {if $form.approval_screen}onclick="toggle_date(this);"{/if} />
				</div>
					</tr>
				{/if}
				<tr>
					<b class="form-label">Date <span class="text-danger"> *</span></b>
				
					
					<div class="row">
						<div class="col">
							<div class="form-inline">
								<input class="form-control"  type="text" name="date" id="date" value="{$form.date|ifzero:''}" class="date" readonly>
								{if !$readonly || $form.approval_screen}
									&nbsp; <img align="absmiddle" src="ui/calendar.gif" id="ds1" style="cursor: pointer; {if $form.date_by_branch}display:none{/if}" title="Select Date">&nbsp;
								{/if}
						
					
						
						<label>	H: </label>
							&nbsp;&nbsp;<select class="form-control"  name="hour" {if $form.date_by_branch}disabled{/if}>
								{section name=hr loop=24 start=0}
									{assign var=hour value=$smarty.section.hr.iteration-1}
									<option value="{$hour}" {if $form.hour eq $hour}selected{/if}>{$hour}</option>
								{/section}
							</select>
					
				
						&nbsp;&nbsp;	<label>M: </label>
						&nbsp;&nbsp;<select class="form-control" name="minute" {if $form.date_by_branch}disabled{/if}>
							<option value="0" {if !$form.minute}selected{/if}>0</option>
							<option value="30" {if $form.minute eq "30"}selected{/if}>30</option>
						</select> 
						</div>
					
					</div>
					
				</tr>
				<tr>
					<b class="form-label mt-2">Branch</b>
					<td>
						{if $BRANCH_CODE eq 'HQ'}
						<!-- Branch -->
							<div class="div_multi_select" id="div_multi_select">
								<ul >
									<table width="100%" border="0" cellspacing="0">
										{if !$form.approval_screen}
											<tr>
												<td><input type="checkbox" id="toggle_branches" /></td>
												<td colspan="3"><b>All</b></td>
											</tr>
										{/if}
										{foreach from=$branches key=bid item=r}
											{if !$form.approval_screen || ($form.approval_screen && $form.effective_branches.$bid)}
												<tr>
													<td>
														{if $form.approval_screen}
															<input type="hidden" name="effective_branches[{$bid}]" {if $form.effective_branches.$bid}value="{$bid}"{/if} class="effective_branch" />
														{else}
															<input type="checkbox" name="effective_branches[{$bid}]" value="{$bid}" {if $form.effective_branches.$bid}checked{/if} class="effective_branch" />
														{/if}
													</td>
													<td>{$r.code}</td>
													<td class="dt" {if !$form.date_by_branch}style="display:none;"{/if}>
														<span class="form-inline">
															<input class="form-control" type="text" name="branch_date[{$bid}]" id="branch_date_{$bid}" value="{$form.effective_branches.$bid.date|ifzero:''}" class="date" readonly>
														{if !$readonly || ($form.approval_screen && $form.effective_branches.$bid)}
															<img align="absmiddle" src="ui/calendar.gif" id="ds1_{$bid}" style="cursor: pointer;" title="Select Date">&nbsp;
														{/if}
														</span>
													</td>
													<td class="hr" {if !$form.date_by_branch}style="display:none;"{/if} nowrap>
													<span class="form-inline">
														&nbsp;&nbsp;H: 
														&nbsp;&nbsp;<select class="form-control" name="branch_hour[{$bid}]">
															{section name=hr loop=24 start=0}
																{assign var=hour value=$smarty.section.hr.iteration-1}
																<option value="{$hour}" {if $form.effective_branches.$bid.hour eq $hour}selected{/if}>{$hour}</option>
															{/section}
														</select>	
													</span>
													</td>
													<td class="min" {if !$form.date_by_branch}style="display:none;"{/if} nowrap>
														<span class="form-inline">
															&nbsp;&nbsp;M: 
															&nbsp;&nbsp;<select class="form-control" name="branch_minute[{$bid}]">
																<option value="0" {if !$form.effective_branches.$bid.minute}selected{/if}>0</option>
																<option value="30" {if $form.effective_branches.$bid.minute eq "30"}selected{/if}>30</option>
															</select></span>
													</td>
												</tr>
										
												{literal}
												<script>
												var curr_date = new Date();
												var curr_year = curr_date.getFullYear();
												var curr_mth = curr_date.getMonth();
												var curr_day = curr_date.getDate();
												allowed_date = new Date(curr_year, curr_mth, curr_day);
												Calendar.setup({
													inputField     :    "branch_date_"+{/literal}{$bid}{literal},     // id of the input field
													ifFormat       :    "%Y-%m-%d",      // format of the input field
													button         :    "ds1_"+{/literal}{$bid}{literal},  // trigger for the calendar (button ID)
													align          :    "Bl",           // alignment (defaults to "Bl")
													singleClick    :    true,
													dateStatusFunc :    function (date) { // disable those date <= today
																	return (date.getTime() < allowed_date.getTime()) ? true : false;
																}
												});
												</script>
												{/literal}
											{/if}
										{/foreach}
									</table>
								</ul>
							</div>
						{else}
							{$BRANCH_CODE}
							<input type="hidden" name="effective_branches[{$sessioninfo.branch_id}]" value="{$sessioninfo.branch_id}" />
						{/if}
					</td>
				</tr>
				<tr>
					<td><b><span class="form-label">Created By</span></b></td>
					<td>
						{$form.username|default:$sessioninfo.u}
					</td>
				</tr>
				<tr>
					<td><b><span class="form-label">File Upload (CSV)</span></b></td>
					<td>
						<input type="file" name="csv">
					</td>
				</tr>
			</table>
			</div>
		</div>
			<div align="center">
				<div class="alert alert-danger rounded">
					<b>This will become approved Batch Price Change once it is confirmed</b>
				</div>
				<input type="submit" id="submit_btn" value="Confirm" class="btn btn-success mb-2">
				<input type="button" id="close_btn" name="close_btn" value="Close" class="btn btn-info mb-2">
			</div>
		</form>
		
	</div>
</div>
<script>
MST_FUTURE_PRICE_MODULE.initialize();
</script>
{if !$form.approval_screen}
	{include file=footer.tpl}
{/if}