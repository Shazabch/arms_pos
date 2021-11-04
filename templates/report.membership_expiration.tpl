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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.weekend{
	color:red;
}

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

#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	width:600px;
	height:400px;
	position:absolute;
	z-index:10000;
}

#div_item_content{
	width:100%;
	height:100%;
	overflow-y:auto;
}

</style>
{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var apply_branch_id = "{$smarty.request.apply_branch_id}";
var sales_branch_id = "{$smarty.request.sales_branch_id}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var trans_count_from = "{$smarty.request.trans_count_from}";
var trans_count_to = "{$smarty.request.trans_count_to}";
var card_no = "{$smarty.request.card_no}";

{literal}
function show_member_details(member_card_no, issue_date, expiry_date, obj){

	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.member_child_"+member_card_no);
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
			a: 'ajax_show_member_details',
			ajax: 1,
			apply_branch_id: apply_branch_id,
			sales_branch_id: sales_branch_id,
			date_from: issue_date,
			date_to: expiry_date,
			trans_count_from: trans_count_from,
			trans_count_to: trans_count_to,
			card_no: member_card_no
		},
		onComplete: function(e){
			new Insertion.After($('tr_member_'+member_card_no), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}

function items_details(branch_id, counter_id, id, date){
	
	curtain(true);
    center_div($('div_item_details'));

    $('div_item_details').show()
	$('div_item_content').update(_loading_+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			branch_id: branch_id,
			pos_id: id,
			date: date
		}
	});
}

function curtain_clicked(){
	curtain(false);
	hidediv('div_item_details');
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


<!-- Item Details -->
<div id="div_item_details" style="display:none;width:700px;height:450px;">
<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<h3 align="center">Items Details</h3>
<div id="div_item_content">
</div>
</div>

{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method="post" class="form" name="f_a">
			<p>
				<div class="row">
					{if $BRANCH_CODE eq 'HQ'}
					<div class="col">
						<b class="form-label">Apply Branch</b>
					<select class="form-control" name="apply_branch_id">
						 <option value="">-- All --</option>
						 {foreach from=$branches key=bid item=b}
							{if !$branches_group.have_group.$bid}
								<option value="{$bid}" {if $smarty.request.apply_branch_id eq $bid}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
						{foreach from=$branches_group.header key=bgid item=bg}
							<optgroup label="{$bg.code}">
								{foreach from=$branches_group.items.$bgid key=bid item=b}
									<option value="{$bid}" {if $smarty.request.apply_branch_id eq $bid}selected {/if}>{$b.code}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					</div>
					<div class="col">
						<b class="form-label">Sales From Branch</b>
					<select class="form-control" name="sales_branch_id">
						 <option value="">-- All --</option>
						 {foreach from=$branches key=bid item=b}
							{if !$branches_group.have_group.$bid}
								<option value="{$bid}" {if $smarty.request.sales_branch_id eq $bid}selected {/if}>{$b.code}</option>
							{/if}
						{/foreach}
						{foreach from=$branches_group.header key=bgid item=bg}
							<optgroup label="{$bg.code}">
								{foreach from=$branches_group.items.$bgid key=bid item=b}
									<option value="{$bid}" {if $smarty.request.sales_branch_id eq $bid}selected {/if}>{$b.code}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					</div>
				{/if}
			
				<div class="col">
					<b class="form-label">Expire Date From</b> 
				<div class="form-inline">
					<input class="form-control" size="20" type="text" name="date_from" value="{$smarty.request.date_from}{$form.from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
				</div>
				</div>
				
			
				<div class="col">
					<b class="form-label">To</b>
			<div class="form-inline">
				<input class="form-control" size="20" type="text" name="date_to" value="{$smarty.request.date_to}{$form.to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
			</div>
				</div>
				</div>
			</p>
			
			<p>
				<div class="row">
					<div class="col">
						<b class="form-label">Transaction Count From</b>
				<input class="form-control" type="text" name="trans_count_from" value="{$smarty.request.trans_count_from}" size="5" />
					</div>
				<div class="col">
					<b class="form-label">To</b>
				<input class="form-control" type="text" name="trans_count_to" value="{$smarty.request.trans_count_to}" size="5" />
				
				</div>
				<div class="col">
					<b class="form-label">Member Card</b>
					<input class="form-control" type="text" name="card_no" value="{$smarty.request.card_no}" size="15" /> (Optional)
					</div>
				</div>	
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
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
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
				<table id="report_tbl" class="rpt_table mb-0 text-md-nowrap  table-hover" width=100% >
					<thead class="bg-gray-100">
						<tr class="header">
							<th width="3%">#</th>
							<th width="10%">Card No</th>
							<th width="10%">NRIC</th>
							<th width="47%">Address</th>
							<th width="10%">DOB</th>
							<th width="5%">Transaction Count</th>
							<th width="5%">Points</th>
						</tr>
					</thead>
				{foreach from=$table key=r item=item name=mm}
					<tr id="tr_member_{$item.card_no}" bgcolor="{cycle name=r values=',#eeeeee'}">
						<td>{$smarty.foreach.mm.iteration}. <img src="/ui/expand.gif" onclick="javascript:void(show_member_details('{$item.card_no}', '{$item.issue_date}', '{$item.expiry_date}', this));" align="absmiddle"> </td>
						<td align="center">{$item.card_no}</td>
						<td align="center">{$item.nric}</td>
						<td>{$item.address|default:'-'}</td>
						<td align="center">{$item.dob|default:'-'}</td>
						<td align="right">{$item.trans_count|number_format:0|default:'-'}</td>
						<td align="right">{$item.points|number_format:0|ifzero:'-'}</td>
					</tr>
					{assign var=ttl_tc value=$ttl_tc+$item.trans_count}
					{assign var=ttl_points value=$ttl_points+$item.points}
				{/foreach}
					<tr class="header">      
						<th class="r" colspan="5">Total</th>
						<th class="r">{$ttl_tc|number_format:0|ifzero:'-'}</th>
						<th class="r">{$ttl_points|number_format:0|ifzero:'-'}</th>
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
