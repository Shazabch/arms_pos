{*
10/16/2014 1:41 PM Justin
- Enhanced to show extra note.
*}

{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var date_from = '{$smarty.request.date_from}';
var date_to = '{$smarty.request.date_to}';
</script>
{literal}
<style>

</style>
<script>

</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<!-- Item Details -->
<div id="div_details" style="display:none;width:800px;height:400px;">
<div style="float:right;padding-bottom:5px;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
<div id="div_content">
</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form method="post" name="myForm" class="form">
			<input type="hidden" name="a" value="load_table" />
			
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">From</b> 
				<div class="form-inline">
					<input class="form-control" size=15 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=15 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
				
				</div>
				<div class="col-md-3">
					<b class="form-label">Cashier</b> 
				<select class="form-control" name="cashier_id">
					<option value="all">-- All --</option>
					{foreach from=$cashier key=cid item=r}
						<option value="{$cid}" {if $smarty.request.cashier_id eq $cid}selected {/if}>{$r.u}</option>
					{/foreach}
				</select>
				</div>
				{if $BRANCH_CODE eq 'HQ'}
				<div class="col-md-3">
					
				<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id">
					<option value="all">-- All --</option>
					{foreach from=$branches key=bid item=r}
						<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$r}</option>
					{/foreach}
				</select>
				</div>
				{/if}	
			</div>
			
			
			<input class="btn btn-primary mt-2" type="submit" name="submits" value="{#SHOW_REPORT#}" />
			<br />
			<p>
			<div class="alert alert-primary rounded mt-2" style="max-width: 500px;">
				<b>Note:</b> <br />
			- Report maximum shown in 1 month.<br />
			- Please ensure the sales date selected above have been fully finalised.
			</div>
			</p>
			</form>
	</div>
</div>


{if isset($smarty.request.submits)}
{if !$table}
No data
{else}
{foreach from=$table key=bid item=p}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$branches.$bid}: {count var=$p} record(s)</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table width="100%" class="sortable report_table small_printing table mb-0 text-md-nowrap  table-hover" id="table_{$bid}">
				<thead class="bg-gray-100">
					<tr class="header">
						<th >No.</th>
						<th >Cashier Name</th>
						<th >Counter</th>
						<th >Date</th>
						<th >Time</th>
						<th>ARMS Code</th>
						<th>Description</th>
						<th>Selling Price</th>
						<th >Cost</th>
						<th >Different</th>
					</tr>
				</thead>
				{foreach from=$p item=r name=f}
				   <tbody class="fs-08">
					<tr>
						<td>{$smarty.foreach.f.iteration}</td>
						<td>{$r.u|default:'-'}</td>
						<td>{$r.counter_id}</td>
						<td>{$r.timestamp|date_format:'%Y-%m-%d'}</td>
						<td>{$r.timestamp|date_format:'%I:%M:%S %p'}</td>
						<td>{$r.sku_item_code}</td>
						<td>{$r.description}</td>
						<td class="r">{$r.sell|number_format:2}</td>
						<td class="r">{$r.grn_cost|number_format:2}</td>
						<td class="r">{$r.different|number_format:2}</td>
					</tr>
				   </tbody>
				{/foreach}
			<div class="tbody fs-08">
				<tr class="header sortbottom">
					<td colspan="7" class="r"><b>Total</b></td>
					<td class="r">{$total.$bid.sell|number_format:2}</td>
					<td class="r">{$total.$bid.grn_cost|number_format:2}</td>
					<td class="r">{$total.$bid.different|number_format:2}</td>
				</tr>
			</div>
			</table>
		</div>
	</div>
</div>
{/foreach}
{/if}
{/if}

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
{include file=footer.tpl}
