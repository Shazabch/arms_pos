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

<h1>Purchase Order Summary</h1>

<form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method=get style="border:1px solid #eee;padding:5px;white-space:nowrap;">
<input type=hidden name=a value="show">
<p>
<b>PO Date From</b> <input type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> &nbsp; <b>To</b> <input type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=12 /> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>

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
	&nbsp;
	<b>By user</b>
	<select name=user_id>
	<option value=0>-- All --</option>
	{section name=i loop=$user}
	<option value={$user[i].id} {if ($smarty.request.user_id eq '' && $sessioninfo.id == $user[i].id) or ($smarty.request.user_id eq $user[i].id)}selected{assign var=_u value=`$user[i].u`}{/if}>{$user[i].u}</option>
	{/section}
	</select>
	
	{if $config.foreign_currency}
		&nbsp;
		<b>Currency</b>
		<select name="currency_code">
			<option value="">-- All --</option>
			<option value="base_currency" {if $smarty.request.currency_code eq 'base_currency'}selected {/if}>Base Currency</option>
			<optgroup label="Foreign Currency">
				{foreach from=$currency_code_list item=code}
					<option value="{$code}" {if $smarty.request.currency_code eq $code}selected {/if}>{$code}</option>
				{/foreach}
			</optgroup>
		</select>
	{/if}
</p>

<p>
<!--input type=hidden name=a value="list"-->
{if $BRANCH_CODE eq 'HQ'}
<b>Filter by Branch</b>
<select name=branch_id>
<option value="">-- All --</option>
{section name=i loop=$branch}
<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
{/section}
</select>
&nbsp;
{/if}
<b>Department</b>
<select name=department_id>
<option value="">-- All --</option>
{section name=i loop=$dept}
<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
{/section}
</select>
&nbsp;
<b>PO Status</b>
<select id="status" name=status onchange="status_selected()">
<option value=0 {if $smarty.request.status == 0}selected{/if}>All</option>
<option value=1 {if $smarty.request.status == 1}selected{/if}>Draft</option>
<option value=2 {if $smarty.request.status == 2}selected{/if}>Proforma</option>
<option value=3 {if $smarty.request.status == 3}selected{/if}>Actual PO</option>
</select>
<span id="po_deliver_grn_status" {if $smarty.request.status != 3}style="display:none;"{/if}>&nbsp;
	<b>Deliver GRN Status</b>
	<select id="delivery_grn_status" name="delivery_grn_status" {if $smarty.request.status != 3}disabled{/if}>
	<option value="0" {if $smarty.request.delivery_grn_status == 0}selected{/if}>All</option>
	<option value="1" {if $smarty.request.delivery_grn_status == 1}selected{/if}>Completed Delivery</option>
	<option value="2" {if $smarty.request.delivery_grn_status == 2}selected{/if}>Incomplete</option>
	</select>
</span>
</p>

<p>
<b>Vendor</b>
<select name=vendor_id>
<option value="">-- All --</option>
{section name=i loop=$vendor}
<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
{/section}
</select>
&nbsp;
<input type=submit value="Refresh">
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>

</form>

{php}
show_report();
{/php}

{include file=footer.tpl}{**}
