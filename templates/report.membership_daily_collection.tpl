{*
1/16/2013 5:17 PM Justin
- Enhanced to load custom remark while presenting history remark.

3/27/2015 6:20 PM Justin
- Enhanced to have GST info.

4/10/2015 4:56 PM Andy
- Fix gst amount not show.

06/29/2020 04:26 PM Sheila
- Updated button css.
*}

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

.rpt_table tr.header td, .rpt_table tr.header th{
	background:#fe9;
	padding:6px 4px;
}

.clr_red {
	color:#FF0000;
}

.clr_blue {
	color:#306EFF;
}
</style>
{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var branch_id = "{$smarty.request.branch_id}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var sales_type = "{$smarty.request.sales_type}";
var sa_id = "{$smarty.request.sa_id}";
{literal}
function toggle_date_details(obj, said, bid, ym, target_sales_amt){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.dtl_"+said+"_"+bid+"_"+ym);

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
	
	if(all_tr.length>0)	return false;
	
	obj.src = '/ui/clock.gif';
	new Ajax.Request(phpself, {
		parameters: {
			a: 'ajax_show_date_details',
			ajax: 1,
			sa_id: said,
			bid: bid,
			ym: ym,
			target_sales_amt: target_sales_amt,
			sales_type: sales_type
		},
		onComplete: function(e){
			new Insertion.After($("mst_"+said+"_"+bid+"_"+ym), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}


{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

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
	<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from|default:$form.date_from}" id="date_from">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	<b>To</b> <input size="10" type="text" name="date_to" value="{$smarty.request.date_to|default:$form.date_to}" id="date_to">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
</p>
<p>
* View in maximum 1 year
</b></p>
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align="center">-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" width="100%" cellspacing="0" cellpadding="0" id="report_tbl">
		<tr class="header">
			<th>Card No</th>
			<th>Name</th>
			<th>NRIC</th>
			<th>Apply Branch</th>
			<th>Last Update</th>
			<th>Issue Date</th>
			<th>Expiry Date</th>
			<th>Transaction<br />Type</th>
			<th>Card Type</th>
			<th>Amount</th>
			{if $have_gst}
				<th>GST</th>
				<th>Amount<br />Incl. GST</th>
			{/if}
			<th>Cashier</th>
		</tr>
		<tbody>
			{foreach from=$table key=id item=bid_list}
				{foreach from=$bid_list key=date item=cid_list}
					{foreach from=$cid_list key=type item=r}
					<tr>
						<td>{$r.card_no|default:"&nbsp;"}</td>
						<td>{$r.name|default:"&nbsp;"}</td>
						<td>{$r.nric|default:"&nbsp;"}</td>
						<td align="center">{$r.apply_branch}</td>
						<td align="center">{$r.last_update}</td>
						<td align="center">{$r.issue_date|default:"&nbsp;"}</td>
						<td align="center">{$r.expiry_date|default:"&nbsp;"}</td>
						<td>
							{assign var=remark_type value=$r.remark}
							{if $config.membership_custom_remark.$remark_type.description}
								{$config.membership_custom_remark.$remark_type.description}
							{elseif $r.remark eq 'N'}
								New Card
							{elseif $r.remark eq 'L'}
								Lost Card &amp; Replacement
							{elseif $r.remark eq 'R'}
								Renewal
							{elseif $r.remark eq 'LR'}
								Lost &amp; Renewal
							{elseif $r.remark eq 'UC'}
								Upgrade
							{elseif $r.remark eq 'C'}
								Change Card
							{elseif $r.remark eq 'U'}
								Change NRIC or Name
							{elseif $r.remark eq 'ER'}
								Exchange &amp; Renew
							{else}
								{$r.remark}
							{/if}&nbsp;
						</td>
						<td align="center">
							{assign var=curr_type value=$r.card_type}
							{$config.membership_cardtype.$curr_type.description|default:"&nbsp;"}
						</td>
						{assign var=curr_remark value=$r.remark}
						<td align="{if $r.gross_amount eq 0 && $r.approval_name}center{else}right{/if}" {if $have_gst && $r.gross_amount eq 0 && $r.approval_name}colspan="3"{/if}>
							{if $r.gross_amount eq 0 && $r.approval_name}
								<font size="1" color="red">Waived by {$r.approval_name}</font>
							{else}
								{$r.gross_amount|number_format:2}
							{/if}
						</td>
						{if $have_gst}
							{if $r.is_under_gst && $r.gross_amount > 0}
								<td align="right">{$r.gst_amount|number_format:2}</td>
								<td align="right">{$r.amount|number_format:2}</td>
								{assign var=ttl_gst_amt value=$ttl_gst_amt+$r.gst_amount}
								{assign var=ttl_amt value=$ttl_amt+$r.amount}
							{elseif $r.gross_amount eq 0 && $r.approval_name}
								{*do nothing for waive*}
							{else}
								<td align="right">0.00</td>
								<td align="right">{$r.gross_amount|number_format:2}</td>
								{assign var=ttl_amt value=$ttl_amt+$r.gross_amount}
							{/if}
						{/if}
						<td align="center">{$r.cashier_name}</td>
						{assign var=ttl_gross_amt value=$ttl_gross_amt+$r.gross_amount}
					</tr>
					{/foreach}
				{/foreach}
			{/foreach}
		</tbody>
		<tr class="header">
			<th colspan="9" align="right">Total</th>
			<th align="right">{$ttl_gross_amt|number_format:2}</th>
			{if $have_gst}
				<th align="right">{$ttl_gst_amt|number_format:2}</th>
				<th align="right">{$ttl_amt|number_format:2}</th>
			{/if}
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
</script>
{/literal}
{/if}

{include file=footer.tpl}
