{*
4/20/2018 2:19 PM Andy
- Added Foreign Currency feature.

7/31/2019 11:49 AM William
- Added new "Deliver GRN Status" filter when PO Status is "Actual PO".

02/17/2021 10:46 AM Rayleen
- Added button for Export PO
*}

{include file=header.tpl}
{literal}
<script>
function zoom_dept(dept_id){
	document.location = '/purchase_order.summary.php?'+Form.serialize(document.f1)+'&department_id='+dept_id;
}

function status_selected(){
	var po_deliver_grn_status = $('po_deliver_grn_status');
	var status = $('status').value;
	var delivery_grn_status = $('delivery_grn_status');
	if(status == '3') {
		delivery_grn_status.disabled =false;
		po_deliver_grn_status.show();
	}
	else  {
		delivery_grn_status.disabled =true;
		po_deliver_grn_status.hide();
	}
}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Purchase Order Summary</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method=get >
			<input type=hidden name=a value="show">
			
		<div class="form-inline">
			<b class="form-label">PO Date From</b> 
			&nbsp;&nbsp;<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> 
			&nbsp;&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> 
			&nbsp;&nbsp;<b class="form-label">To</b> 
			&nbsp;&nbsp;<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> 
			&nbsp;&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
		</div>
			
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
			<script type="text/javascript">
			
			
				Calendar.setup({
					inputField     :    "added1",     // id of the input field
					ifFormat       :    "%Y-%m-%d",      // format of the input field
					button         :    "t_added1",  // trigger for the calendar (button ID)
					align          :    "Bl",           // alignment (defaults to "Bl")
					singleClick    :    true
					//,
					//onUpdate       :    load_data
				});
			
				Calendar.setup({
					inputField     :    "added2",     // id of the input field
					ifFormat       :    "%Y-%m-%d",      // format of the input field
					button         :    "t_added2",  // trigger for the calendar (button ID)
					align          :    "Bl",           // alignment (defaults to "Bl")
					singleClick    :    true
					//,
					//onUpdate       :    load_data
				});
			
			</script>
			{/literal}
				<div class="row">
					
				<div class="col-md-6">
					<b class="form-label mt-2">By user</b>
				<select class="form-control" name=user_id>
				<option value=0>-- All --</option>
				{section name=i loop=$user}
				<option value={$user[i].id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.user_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
				{/section}
				</select>
				</div>

				{if $config.foreign_currency}
				<div class="col-md-6">
					<b class="form-label mt-2">Currency</b>
					<select class="form-control" name="currency_code">
						<option value="">-- All --</option>
						<option value="base_currency" {if $smarty.request.currency_code eq 'base_currency'}selected {/if}>Base Currency</option>
						<optgroup label="Foreign Currency">
							{foreach from=$currency_code_list item=code}
								<option value="{$code}" {if $smarty.request.currency_code eq $code}selected {/if}>{$code}</option>
							{/foreach}
						</optgroup>
					</select>
				</div>
				{/if}
	
			<!--input type=hidden name=a value="list"-->
			
			{if $BRANCH_CODE eq 'HQ'}
				<div class="col-md-6">
			<b class="form-label mt-2">Filter by Branch</b>
			<select class="form-control" name=branch_id>
			<option value="">-- All --</option>
			{section name=i loop=$branch}
			<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
			{/section}
			</select>
				</div>
			{/if}
			

			<div class="col-md-6">
				<b class="form-label mt-2">Department</b>
				<select class="form-control" name=department_id>
				<option value="">-- All --</option>
				{section name=i loop=$dept}
				<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
				{/section}
				</select>
			</div>

			<div class="col-md-6">
				<b class="form-label mt-2">PO Status</b>
				<select class="form-control" id="status" name=status onchange="status_selected()">
				<option value=0 {if $smarty.request.status == 0}selected{/if}>All</option>
				<option value=1 {if $smarty.request.status == 1}selected{/if}>Draft</option>
				<option value=2 {if $smarty.request.status == 2}selected{/if}>Proforma</option>
				<option value=3 {if $smarty.request.status == 3}selected{/if}>Actual PO</option>
				</select>
			</div>
			<span id="po_deliver_grn_status" {if $smarty.request.status != 3}style="display:none;"{/if}>&nbsp;
			<div class="col-md-6">
				
					<b class="form-label mt-2">Deliver GRN Status</b>
					<select class="form-control" id="delivery_grn_status" name="delivery_grn_status" {if $smarty.request.status != 3}disabled{/if}>
					<option value="0" {if $smarty.request.delivery_grn_status == 0}selected{/if}>All</option>
					<option value="1" {if $smarty.request.delivery_grn_status == 1}selected{/if}>Completed Delivery</option>
					<option value="2" {if $smarty.request.delivery_grn_status == 2}selected{/if}>Incomplete</option>
					</select>
				
			</div>
		</span>
			
			
			<div class="col-md-6">
				<b class="form-label mt-2">Vendor</b>
			<select class="form-control" name=vendor_id>
			<option value="">-- All --</option>
			{section name=i loop=$vendor}
			<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
			{/section}
			</select>
			</div>
				</div>
			
			<input type="submit" class="btn btn-primary mt-2" value="Refresh">
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			
			
			</form>
	</div>
</div>

{php}
show_report();
{/php}

{include file=footer.tpl}{**}
