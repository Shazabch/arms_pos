{*
8/26/2013 10:51 AM Andy
- Remove transaction status filter.
- Add missing date to calendar.
- Add "Refund" and "Cancel Previous".

2/14/2017 10:49 AM Andy
- Fixed item details wrongly group same receipt_no amount together.
- Change group by receipt_no to group by receipt_ref_no.
- Change transaction details width to 800px and height to 450px.

06/30/2020 04:43 PM Sheila
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

.negative{
	color: red;
	font-weight: bold;
}
</style>
{/literal}

<script type="text/javascript">

var phpself = "{$smarty.server.PHP_SELF}";
var filtered_cashier_id = "{$smarty.request.cashier_id}";
//var filtered_tran_status = "{$smarty.request.tran_status}";

{literal}
	
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

function show_date_details(bid, date, obj){
	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#rpt_table tr.date_child_"+bid+"_"+date);
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
			a: 'ajax_load_details_by_date',
			ajax: 1,
			branch_id: bid,
			date: date,
			cashier_id: filtered_cashier_id,
			//tran_status: filtered_tran_status
		},
		onComplete: function(e){
			new Insertion.After($('tr_date_'+bid+'_'+date), e.responseText);
			obj.src = '/ui/collapse.gif';
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
<div id="div_item_details" style="display:none;width:800px;height:450px;">
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
	<b>Date From</b> <input size="10" type="text" name="date_from" value="{$smarty.request.date_from}" id="date_from">
	<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	
	<b>To</b> 
	<input size="10" type="text" name="date_to" value="{$smarty.request.date_to}" id="date_to">
	<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date From">
</p>
<p>
	<!--b>Counter</b>
	<select name="counter_id">
		<option value="">-- All --</option>
		{foreach from=$counter_list item=c}
			<option value="{$c.id}" {if $smarty.request.counter_id eq $c.id}selected{/if}>{$c.network_name}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;-->
	<b>Cashier</b>
	<select name="cashier_id">
		<option value="">-- All --</option>
		{foreach from=$user_list item=u}
			<option value="{$u.id}" {if $smarty.request.cashier_id eq $u.id}selected{/if}>{$u.u}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;

	{*<b>Transaction Status</b>
	<select name="tran_status">
		<option value="">-- All --</option>
		{foreach from=$transaction_status key=status item=t}
			<option value="{$status}" {if $smarty.request.tran_status eq $status}selected {/if}>{$t}</option>
		{/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;*}
</p>
<p>
* View in maximum 1 month
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
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	<h2>{$report_title}</h2>
	<table class="rpt_table" id="rpt_table" width=100% cellspacing=0 cellpadding=0>
		<tr class="header">
			<th width="40">#</th>
			<th width="80">Date</th>
			<th width="80">Receipt Ref No</th>
			<th width="80">Cashier</th>
			<th width="80">Approved By</th>
			<th width="">Received Amount</th>
			<th width="">Used Amount</th>
			<th>Refund</th>
			<th>Cancel<br />Previous</th>
		</tr>
		<tbody>
		{foreach from=$table item=b_list key=bid name=fbranch}
			<tr class="b_header">
				<th colspan="9" align="left">{$branches.$bid.code} - {$branches.$bid.description}</th>
			</tr>
			{foreach from=$b_list key=date item=d name=deposit}
				<tr  id="tr_date_{$bid}_{$date}">
					<td>
						{$smarty.foreach.deposit.iteration}.
						{if $print_excel == ''}<img src="/ui/expand.gif" onclick="javascript:void(show_date_details('{$bid}', '{$date|default:0}', this));" align=absmiddle>{/if} 
					</td>
					<td align="center">{$date}</td>
					<td colspan="3">&nbsp;</td>
					<td align="right">{$d.rcv_amt|number_format:2}</td>
					<td align="right">{$d.used_amt|number_format:2}</td>
					
					{* Refund *}
					<td align="right" class="{if $d.refund>0}negative{/if}">{$d.refund*-1|number_format:2|ifzero:'-'}</td>
					
					{* Cancel Previous *}
					<td align="right" class="{if $d.cancel_amt<0}negative{/if}">{$d.cancel_amt|number_format:2|ifzero:'-'}</td>
				</tr>
				{assign var=ttl_rcv_amt value=$ttl_rcv_amt+$d.rcv_amt}
				{assign var=sub_ttl_rcv_amt value=$sub_ttl_rcv_amt+$d.rcv_amt}
				{assign var=ttl_used_amt value=$ttl_used_amt+$d.used_amt}
				{assign var=sub_ttl_used_amt value=$sub_ttl_used_amt+$d.used_amt}
				{assign var=ttl_refund_amt value=$ttl_refund_amt+$d.refund}
				{assign var=sub_ttl_refund_amt value=$sub_ttl_refund_amt+$d.refund}
				{assign var=ttl_cancel_amt value=$ttl_cancel_amt+$d.cancel_amt}
				{assign var=sub_ttl_cancel_amt value=$sub_ttl_cancel_amt+$d.cancel_amt}
			{/foreach}
			{if count($branch_list) > 1 && $BRANCH_CODE eq 'HQ'}
				<tr class="sub_total">
					<th class="r" colspan="5">Sub Total</th>
					<th align="right">{$sub_ttl_rcv_amt|number_format:2|ifzero:'-'}</th>
					<th align="right">{$sub_ttl_used_amt|number_format:2|ifzero:'-'}</th>
					
					{* Refund *}
					<th align="right" class="{if $sub_ttl_refund_amt>0}negative{/if}">{$sub_ttl_refund_amt*-1|number_format:2|ifzero:'-'}</th>
					
					{* Cancel Previous *}
					<th align="right" class="{if $sub_ttl_cancel_amt<0}negative{/if}">{$sub_ttl_cancel_amt|number_format:2|ifzero:'-'}</th>
				</tr>
				{assign var=sub_ttl_rcv_amt value=0}
				{assign var=sub_ttl_used_amt value=0}
				{assign var=sub_ttl_refund_amt value=0}
			{/if}
		{/foreach}
		</tbody>
		<tr class="header">
			<th class="r" colspan="5">Total</th>
			<th align="right">{$ttl_rcv_amt|number_format:2|ifzero:'-'}</th>
			<th align="right">{$ttl_used_amt|number_format:2|ifzero:'-'}</th>
			
			{* Refund *}
			<th align="right"  class="{if $ttl_refund_amt>0}negative{/if}">{$ttl_refund_amt*-1|number_format:2|ifzero:'-'}</th>
			
			{* Cancel Previous *}
			<th align="right"  class="{if $ttl_cancel_amt<0}negative{/if}">{$ttl_cancel_amt|number_format:2|ifzero:'-'}</th>
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
        button         :    "img_date_to",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
	
	new Draggable('div_item_details');
</script>
{/literal}
{/if}

{include file=footer.tpl}
