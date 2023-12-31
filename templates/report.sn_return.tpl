{*
4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 02:42 PM Sheila
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

.font_bgcolor{
	background-color:#ff9;
	opacity:0.8;
}
</style>

{/literal}

<script>
var phpself = "{$smarty.server.PHP_SELF}";
var date_from = "{$smarty.request.date_from}";
var date_to = "{$smarty.request.date_to}";
var branch_id = "{$smarty.request.branch_id}";
var vendor_id = "{$smarty.request.vendor_id}";
var use_grn = "{$smarty.request.use_grn}";
var owner_id = "{$smarty.request.owner_id}";

{literal}

function show_date_details(dept_id, obj){

	if(obj.src.indexOf('clock')>0) return false;
	var all_tr = $$("#report_tbl tr.dept_chid_"+dept_id);
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
			dept_id: dept_id,
			date_from: date_from,
			date_to: date_to,
			branch_id: branch_id,
			vendor_id: vendor_id,
			use_grn: use_grn,
			owner_id: owner_id
		},
		onComplete: function(e){
			new Insertion.After($('tr_dept_'+dept_id), e.responseText);
			obj.src = '/ui/collapse.gif';
		}
	});
}

function chk_vd_filter(){
	val=$('vendor_id').value;

	if(val){
		//$('dept_id').disabled=true;
		$('use_grn').disabled=false;
	}
	else{
		//$('dept_id').disabled=false;
		$('use_grn').checked=false;	
		$('use_grn').disabled=true;	
	}
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
		<form method="post" class="form" action="report.sn_return.php" name="f_a">
			<p>
				<div class="row">
					<div class="col-md-3">
						{if $BRANCH_CODE eq 'HQ'}
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
					{/if}
					</div>
					
					<div class="col-md-3">
						<b class="form-label">Date</b>
					<div class="form-inline">
						 <input class="form-control" size=17 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
					</div>
					</div>
					
					
					<div class="col-md-3">
						<b class="form-label">To</b>
					<div class="form-inline">
						 <input class="form-control" size=17 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
					</div>
					</div>
					
					
					<div class="col-md-3">
						<div class="form-label form-inline mt-4">
							<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
						</div>
					</div>
				</div>
			</p>
			<!--p>
				<b>Serial No</b> <input type="text" name="serial_no" size="20" value="{$smarty.request.serial_no}" />
				
				<b>Status</b>
				<select name="status">
					<option value="Available" {if !$smarty.request.status || $smarty.request.status eq 'Available'}selected{/if}>Available</option>
					<option value="Sold" {if $smarty.request.status eq 'Sold'}Returned{/if}>Sold</option>
				</select>
			</p-->
			<p><b>
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				Important: <br>
			* Report Maximum Shown in 1 year period.
			</div>
			</b></p>
			
			<input type="hidden" name="subm" value="1">
			<button class="btn btn-primary mt-2" name="a" value="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name="a" value="output_excel">{#OUTPUT_EXCEL#}</button>
			{/if}
			</form>
	</div>
</div>
{/if}
{if !$table}
<p align=center>-- No data --</p>
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
			<table class="report_table small_printing" width="100%" id="report_tbl">
				<thead class="bg-gray-100">
					<tr class="header">
						<th>#</th>
						<th>SKU Item Code</th>
						<th>Description</th>
						<th>Located Branch</th>
						<th>Serial No</th>
						<th>Transaction Date</th>
						<th>User</th>
					</tr>
				</thead>
				<tbody class="fs-08">
				{foreach from=$table key=hid item=r name=pisnf}
					<tr>
						<td>{$smarty.foreach.pisnf.iteration}.</td>
						<td align="center">{$r.sku_item_code}</td>
						<td>{$r.si_description}</td>
						<td align="center">{$r.current_branch}</td>
						<td>{$r.serial_no}</td>
						<td align="center">{$r.date}</td>
						<td align="center">{$r.user}</td>
					</tr>
				{/foreach}
				</tbody>
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
