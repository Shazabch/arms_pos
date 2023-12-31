{*
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

#div_sales_details,#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
</style>
{/literal}
<script>
var LOADING = '<img src="/ui/clock.gif" />';

{literal}
function sales_details(date, card_no, type, bid, ord_date){
    curtain(true);
    center_div('div_sales_details');

    $('div_sales_details').show()
	$('div_sales_content').update(LOADING+' Please wait...');
	if(type == 'AUTO_REDEEM'){
		new Ajax.Updater('div_sales_content','membership.php?a=ajax_get_auto_redemption_history'+URLEncode('&date='+date+'&card_no='+card_no+'&bid='+bid+'&type='+type+'&ord_date='+ord_date),
		{
		    method: 'post'
		});
	}else if(type=='REDEEM' || type=='CANCELED'){
        new Ajax.Updater('div_sales_content','membership.php?a=ajax_get_redemption_history&date='+date+'&card_no='+card_no+'&bid='+bid+'&type='+type,
		{
		    method: 'post'
		});
	}else{
        new Ajax.Updater('div_sales_content','counter_collection.php?a=sales_details&date='+date+'&card_no='+card_no+'&branch_id='+bid,
		{
		    method: 'post'
		});
	}	
}

function trans_detail(counter_id,cashier_id,date,pos_id,branch_id){
	curtain(true);
	center_div('div_item_details');
	
    $('div_item_details').show();
	$('div_item_content').update(LOADING+' Please wait...');

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
	hidediv('div_sales_details');
	hidediv('div_item_details');
	curtain(false);
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
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<!-- Transaction Details-->
<div id="div_sales_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
<div id="div_sales_content">
</div>
</div>
<!-- End of Transaction Details-->
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="hidediv('div_item_details');" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->

<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">
			<p>
				<div class="row">
					{if $BRANCH_CODE eq 'HQ'}
				<div class="col-md-3">
					<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id">
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
					</select>
				</div>
				{/if}
				<div class="col-md-3">
					<b class="form-label">Date From</b> 
			<div class="form-inline">
				<input class="form-control" size="20" type="text" name="date_from" value="{$smarty.request.date_from|default:$form.date_from}" id="date_from">
				&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
			</div>
				</div>
				<div class="col-md-3">
					<b class="form-label">To</b>
				 <div class="form-inline">
					<input class="form-control" size="20" type="text" name="date_to" value="{$smarty.request.date_to|default:$form.date_to}" id="date_to">
					&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
				 </div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Type</b>
				<select class="form-control" name="type">
					<option value="">-- All --</option>
					{foreach from=$type key=val item=desc}
						<option value="{$val}" {if $smarty.request.type eq $val}selected {/if}>{$desc}</option>
					{/foreach}
				</select>
				</div>
				</div>
			</p>
			<p class="alert alert-primary" style="max-width:250px;">
			* View in maximum 1 year
			</b></p>
			</p>
			<p>
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-primary" name="a" value="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			</form>
	</div>
</div>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align="center">-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table class="rpt_table" width="100%" id="report_tbl">
					<thead class="bg-gray-100">
						<tr class="header">
							<th>Date</th>
							<th>Branch</th>
							<th>Card No</th>
							<th>Name</th>
							<th>NRIC</th>
							<th>Issue Date</th>
							<th>Expiry Date</th>
							<th>Remarks</th>
							<th>Type</th>
							<th>Cashier</th>
							<th>Points</th>
						</tr>
					</thead>
					<tbody class="fs-08">
						{foreach from=$table key=ar item=r}
							<tr>
								<td align="center">
									{if $r.type ne 'ENTRY' && $r.type ne 'ADJUST' && $r.type ne ''}
										<a href="javascript:void(0);" onclick="sales_details('{$r.points_date}','{$r.card_no}','{$r.type}','{$r.branch_id}','{$r.date}')">
										{$r.points_date|default:"&nbsp;"}
										</a>
									{else}
										{$r.points_date|default:"&nbsp;"}
									{/if}
								</td>
								<td align="center">{$r.branch_code}</td>
								<td>{$r.card_no|default:"&nbsp;"}</td>
								<td>{$r.name|default:"&nbsp;"}</td>
								<td>{$r.nric|default:"&nbsp;"}</td>
								<td align="center">{$r.issue_date|default:"&nbsp;"}</td>
								<td align="center">{$r.expiry_date|default:"&nbsp;"}</td>
								<td>{$r.remark|default:"&nbsp;"}</td>
								<td align="center">
									{assign var=curr_type value=$r.type}
									{$type.$curr_type}
								</td>
								<td align="center">{$r.cashier_name|default:"-"}</td>
								<td align="right">
									{$r.points|default:0|number_format:0}
								</td>
								{assign var=ttl_points value=$ttl_points+$r.points}
							</tr>
						{/foreach}
					</tbody>
					<tr class="header">
						<th colspan="10" align="right">Total</th>
						<th align="right">{$ttl_points|number_format:0}</th>
					</tr>
				</table>
			</div>
		</div>
	</div>
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
