{include file=header.tpl}

{if !$no_header_footer}
{literal}

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
/*.rpt_table tr:nth-child(even){
	background-color:#eeeeee;
}*/

/* standard style for report table */
.rpt_table {
	border-top:1px solid #000;
	border-right:1px solid #000;
}

.rpt_table td, .rpt_table th{
	border-left:1px solid #000;
	border-bottom:1px solid #000;
	padding:4px;
}

.rpt_table tr.b_header th{
	background:#edffed;
	padding:6px 4px;
}

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.rpt_table tr.sub_total th{
	background:#adffea;
	padding:6px 4px;
}

#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
</style>
{/literal}

<script>
{literal}

function show_date_details(date, obj){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$(".rpt_table tr.dept_child_"+date);

	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).show();
		}
		
	}else{
		obj.src = '/ui/expand.gif';
		for(var i=0; i<all_tr.length; i++){
			$(all_tr[i]).hide();
		}
	}
}

function trans_detail(counter_id,cashier_id,date,pos_id,branch_id){
	curtain(true);
	center_div('div_item_details');
	
	$('div_item_details').show();
	$('div_item_content').update(_loading_+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
		method: 'post',
		parameters:{
			a: 'item_details',
			branch_id: branch_id,
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}

function curtain_clicked(){
	hidediv('div_item_details');
	curtain(false);
}

{/literal}
</script>
{/if}

<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
<form method="post" class="form" name="f_a">
<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id">
		    <option value="">-- All --</option>
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
		    {/foreach}
		    {if $branch_group.header}
		        <optgroup label="Branch Group">
					{foreach from=$branch_group.header item=r}
					    {capture assign=bgid}bg,{$r.id}{/capture}
						<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from}{$form.from}" id="date_from">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	
	<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to}{$form.to}" id="date_to">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">&nbsp;&nbsp;&nbsp;&nbsp;

	<!--b>Counter</b>
	<select name="counter_id">
		<option value="">-- All --</option>
		{foreach from=$counter_list item=c}
			<option value="{$c.id}" {if $smarty.request.counter_id eq $c.id}selected{/if}>{$c.network_name}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;-->

</p>
<p>
	<b>Cashier</b>
	<select name="cashier_id">
		<option value="">-- All --</option>
		{foreach from=$user_list item=u}
			<option value="{$u.id}" {if $smarty.request.cashier_id eq $u.id}selected{/if}>{$u.u}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>View By</b>
	<select name="view_type">
		<option value="1" {if !$smarty.request.view_type || $smarty.request.view_type eq 1}selected{/if}>Daily</option>
		<option value="2" {if $smarty.request.view_type eq 2}selected{/if}>Monthly</option>
	</select>
</p>
<p>
* View in maximum 1 {if !$smarty.request.view_type || $smarty.request.view_type eq 1}month{else}year{/if}
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
	{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width=100% cellspacing=0 cellpadding=0>
		<tr class="header">
			<th width="3%">#</th>
			<th>Return Type</th>
			<th>SKU Item Code</th>
			<th>Description</th>
			<th>MCode</th>
			{assign var=colspan value=6}
			<th>Status</th>
			<th>Refund/Expired<br />Charges</th>
			<th>Actual<br />Refund/Expired Charges</th>
			<th>Variance</th>
			<th>Extra Charges</th>
			<th>Remark</th>
		</tr>
		<tbody>
		{foreach from=$branch_list item=b key=f_bid name=branch}
			<tr class="b_header">
				<th colspan="{$colspan+5}" align="left">{$b.branch_code} - {$b.description}</th>
			</tr>
			{foreach from=$table.$f_bid key=date item=date_list name=dl}
				{if $smarty.request.view_type eq 2}
					<tr>
						<td>
							{$smarty.foreach.dl.iteration}. 
							<img src="/ui/expand.gif" onclick="javascript:void(show_date_details('{$date}', this));" align="absmiddle">
						</td>
						<td colspan="5">{$date|date_format:"%d-%m-%Y"}</td>
						<td align="right">{$monthly_table.$f_bid.$date.refund|default:$monthly_table.$f_bid.$date.charges|number_format:2}</td>
						<td align="right">{$monthly_table.$f_bid.$date.actual_refund|default:$monthly_table.$f_bid.$date.actual_charges|number_format:2}</td>
						<td align="right">
							{assign var=variance value=$monthly_table.$f_bid.$date.refund-$monthly_table.$f_bid.$date.actual_refund+$monthly_table.$f_bid.$date.charges-$monthly_table.$f_bid.$date.actual_charges}
							{$variance|number_format:2}
						</td>
						<td align="right">{$monthly_table.$f_bid.$date.extra_charges|number_format:2}</td>
						<td>&nbsp;</td>
					</tr>
					{assign var=row_count value=0}
				{/if}
				{foreach from=$date_list key=k item=d name=rp}
					{assign var=row_count value=$row_count+1}
					<tr {if $smarty.request.view_type eq 2}class="dept_child_{$date}" style="display:none;"{/if}>
						<td>{$row_count}.</td>
						<td>{$d.more_info.return_policy.title|default:'-'}</td>
						<td align="center">{$d.sku_item_code}</td>
						<td>{$d.description}</td>
						<td>{$d.mcode|default:'-'}</td>
						<td align="center">
							{if $d.more_info.return_policy.expired eq "Yes"}
								Expired ({$d.more_info.return_policy.expired_day} {$d.more_info.return_policy.expired_type|ucfirst}s)
								{assign var=ttl_exp_charges value=$ttl_exp_charges+$d.more_info.return_policy.charges}
								{assign var=ttl_exp_act_charges value=$ttl_exp_act_charges+$d.more_info.return_policy.actual_charges}
								{assign var=variance value=$d.more_info.return_policy.actual_charges-$d.more_info.return_policy.charges}
							{else}
								Returned
								{assign var=variance value=$d.more_info.return_policy.actual_refund-$d.more_info.return_policy.refund}
							{/if}
						</td>
						<td align="right">{$d.more_info.return_policy.refund|default:$d.more_info.return_policy.charges|number_format:2}</td>
						<td align="right">{$d.more_info.return_policy.actual_refund|default:$d.more_info.return_policy.actual_charges|number_format:2}</td>
						<td align="right">{$variance|number_format:2}</td>
						<td align="right">{$d.more_info.return_policy.extra_charges|default:0|number_format:2}</td>
						<td>{$d.more_info.return_policy.remark|default:'-'}</td>
						{assign var=ttl_refund_charges value=$ttl_refund_charges+$d.more_info.return_policy.refund+$d.more_info.return_policy.charges}
						{assign var=sub_ttl_refund_charges value=$sub_ttl_refund_charges+$d.more_info.return_policy.refund+$d.more_info.return_policy.extra_charges}
						{assign var=ttl_act_refund_charges value=$ttl_act_refund_charges+$d.more_info.return_policy.refund+$d.more_info.return_policy.charges}
						{assign var=sub_ttl_act_refund_charges value=$sub_ttl_act_refund_charges+$d.more_info.return_policy.actual_refund+$d.more_info.return_policy.actual_charges}
						{assign var=ttl_var value=$ttl_var+$variance}
						{assign var=sub_ttl_var value=$sub_ttl_var+$variance}
						{assign var=ttl_extra_charges value=$ttl_extra_charges+$d.more_info.return_policy.extra_charges}
						{assign var=sub_ttl_extra_charges value=$sub_ttl_extra_charges+$d.more_info.return_policy.extra_charges}
					</tr>
				{/foreach}
			{/foreach}
			{if count($branch_list) > 1 && $BRANCH_CODE eq 'HQ'}
				<tr class="sub_total">
					<th class="r" colspan="{$colspan}">Sub Total</th>
					<th align="right">{$sub_ttl_refund_charges|number_format:2|ifzero:'-'}</th>
					<th align="right">{$sub_ttl_act_refund_charges|number_format:2|ifzero:'-'}</th>
					<th align="right">{$sub_ttl_var|number_format:2|ifzero:'-'}</th>
					<th align="right">{$sub_ttl_extra_charges|number_format:2|ifzero:'-'}</th>
					<th>&nbsp;</th>
				</tr>
				{assign var=sub_ttl_refund_charges value=0}
				{assign var=sub_ttl_act_refund_charges value=0}
				{assign var=sub_ttl_var value=0}
				{assign var=sub_ttl_extra_charges value=0}
			{/if}
		{/foreach}
		</tbody>
		<tr class="header">
			<th class="r" colspan="{$colspan}">Total</th>
			<th align="right">{$ttl_refund_charges|number_format:2|ifzero:'-'}</th>
			<th align="right">{$ttl_act_refund_charges|number_format:2|ifzero:'-'}</th>
			<th align="right">{$ttl_var|number_format:2|ifzero:'-'}</th>
			<th align="right">{$ttl_extra_charges|number_format:2|ifzero:'-'}</th>
			<th>&nbsp;</th>
		</tr>
	</table>
{/if}

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
	new Draggable('div_item_details');
</script>
{/literal}
{/if}

{include file=footer.tpl}
