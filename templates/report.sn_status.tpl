{*
4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

4/19/2017 2:54 PM Justin
- Enhanced to have SKU items filter, system will now check S/N and SKU items filter both cannot be null at the same time.

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

function check_form(){
	passArrayToInput();
	if (document.f_a['serial_no'].value == "" && document.f_a['sku_code_list_2'].value == "") {
		alert("Please assign Serial No or SKU item.");
		return false;
	}
	
	return true;
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
		<form method="post" class="form" action="report.sn_status.php" name="f_a" onSubmit="return check_form();">
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
					<b class="form-label">Serial No</b> 
				<input class="form-control" type="text" name="serial_no" size="20" value="{$smarty.request.serial_no}" />
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Status</b>
				<select class="form-control" name="status">
					<option value="" {if !$smarty.request.status}selected{/if}>All</option>
					<option value="Available" {if $smarty.request.status eq 'Available'}selected{/if}>Available</option>
					<option value="Sold" {if $smarty.request.status eq 'Sold'}selected{/if}>Sold</option>
				</select>
				</div>

				<div class="col-md-3">
					<div class="form-label form-inline mt-4">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
				</div>
				</div>
			</p>
			
			<p>
			{include file="sku_items_autocomplete_multiple.tpl" check_sn=1}
			</p>
			
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
<h2>{$report_title}</h2>
<br />
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
						<th>Remarks</th>
						<th>Status</th>
						<!--th>Active/Inactive</th-->
						<th>Date Added</th>
						<th>User</th>
					</tr>
				</thead>
				<tbody class="fs-08">
				{foreach from=$table key=hid item=r name=pisnf}
					<tr>
						<td>{$smarty.foreach.pisnf.iteration}.</td>
						<td align="center">{$r.sku_item_code}</td>
						<td>
							{$r.si_description}
							{if $r.status eq "Sold"}
								<br />
								<font color="blue">{$r.nric|default:"-"} / {$r.name|default:"-"} / {$r.address|default:"-"} / {$r.contact_no|default:"-"} / {$r.email|default:"-"} / {$r.warranty_period|default:"-"}</font>
							{/if}
						</td>
						<td align="center">{$r.current_branch}</td>
						<td>{$r.serial_no}</td>
						<td>{$r.remark}</td>
						<td align="center">{$r.status}</td>
						<!--td align="center">{if $r.active}Active{else}<font color="red">Inactive</font>{/if}</td-->
						<td align="center">{$r.added}</td>
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
	reset_sku_autocomplete();
</script>
{/literal}
{/if}
{include file=footer.tpl}
