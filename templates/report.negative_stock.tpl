{*
7/8/2010 5:09:27 PM Andy
- Fix report showing in-correct data.

7/10/2010 2:10:21 AM yinsee
- show cost and selling in single branch mode

7/12/2010 11:03:32 AM Alex
- add privilege show cost

7/13/2010 3:20:11 PM Andy
- Fix Negative Stock Report when show by parent will display wrong SKU ARMS Code.

10/4/2010 11:17:02 AM Andy
- Enhance branch selection dropdown, allow it to show branch group and individual branch data.

10/14/2011 4:44:12 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

2/4/2013 5:34 PM Justin
- Converted this report to extend module instead of report.
- Enhanced to show and filter branches from regions or branch group base on user's regions.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

12/2/2014 5:03 PM Andy
- Add a legend to let user know the selling price is before GST.

06/30/2020 02:25 PM Sheila
- Updated button css.
*}

{include file='header.tpl'}
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
<script>
function toggle_sub(tbody_id, el)
{
	if ($(tbody_id).style.display=='none')
	{
	    el.src='/ui/collapse.gif';
	    $(tbody_id).style.display='';
	}
	else
	{
	    el.src='/ui/expand.gif';
	    $(tbody_id).style.display='none';
	}
}

</script>
<style>
.c1 { background:#fff; }
.c2 { background:#eee; }
.c3 { background:#efefef; }

option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
</style>
{/literal}
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
		<form method=post class=form>
			<input type=hidden name=report_title value="{$report_title}">
			
			<div class="row">
				<div class="col">
					<b class="form-label">Date</b>
			<div class="form-inline">
				<input class="form-control" size=45 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
				</div>

			<div class="col">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b>
			<select class="form-control" name="branch_id">
			<option value=''>-- All --</option>
			{foreach from=$branches key=bid item=r}
				{if !$branches_group.have_group.$bid}
					<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
				{/if}
			{/foreach}
			{if $branches_group.header}
				<optgroup label='Branch Group'>
				{foreach from=$branches_group.header key=bgid item=bg}
						<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
						{foreach from=$branches_group.items.$bgid item=r}
							<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
						{/foreach}
					{/foreach}
				</optgroup>
			{/if}
			{if $config.consignment_modules && $config.masterfile_branch_region}
				<optgroup label='Region'>
				{foreach from=$config.masterfile_branch_region key=type item=f}
					{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
						{assign var=curr_type value="REGION_`$type`"}
						<option value="REGION_{$type}" {if $smarty.request.branch_id eq $curr_type}selected {/if}>{$type|upper}</option>
					{/if}
				{/foreach}
				</optgroup>
			{/if}
			</select>
			{/if}
			</div>
			</div>
			
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p>
			
			<input type="hidden" name="subm" value="1" />
			<input class="btn btn-primary" type="submit" value='Show Report' />
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="output_excel"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
			{/if}
			<div class="form-label form-inline mt-2">
				<input type="checkbox" name="group_sku" {if $smarty.request.group_sku}checked {/if}> <b>&nbsp;Group by SKU</b>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</div>
			
			{if $config.enable_gst}
				<p>
					<div class="alert alert-primary mt-2" style="max-width: 300px;">
						* Selling price show in this report is GST exclusive.
					</div>
				</p>
			{/if}
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

{foreach from=$table key=cat_id item=s}
{*<h2>{$category.$cat_id.cname}</h2>*}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing" width="100%">
				<thead class="bg-gray-100">
					<tr class="header">
						<th width="10%">Arms Code</th>
						<th width="10%">MCode</th>
						<th width="5%">Art No.</th>
						<th>Description</th>
						{if $branch_name|upper eq 'ALL'}
							{if $branch_group.header}
								{foreach from=$branch_group.header key=bgid item=bg}
									<th width="5%">{$bg.code}</th>
								{/foreach}
							{/if}
							{foreach from=$branches key=bid item=b}
								{if !$branch_group.have_group.$bid}
									<th width="5%">{$b.code}</th>
								{/if}
							{/foreach}
							<th width="5%">Total</th>
						{else}
							{if $is_bg_id>0}
								{foreach from=$branch_group.items.$is_bg_id key=bid item=b}
									<th width="5%">{$b.code}</th>
								{/foreach}
								<th width="5%">Total</th>
							{else}
								<th width="10%">Quantity</th>
								{if !$smarty.request.group_sku}
								<th width="10%">Unit Price</th>
								<th width="10%">Total Selling</th>
								{/if}
								{if	$sessioninfo.privilege.SHOW_COST}
								<th width="10%">Unit Cost</th>
								<th width="10%">Total Cost</th>
								{/if}
							{/if}
						{/if}
					</tr>
				</thead>
					{foreach from=$s key=sku_key item=r}
						{cycle values="c2,c1" assign=row_class name=row1}
						<div class="tbody fs-08">
							<tr class="{$row_class}">
								<td>{$sku.$sku_key.code}</td>
								<td class="c">{$sku.$sku_key.mcode|default:'-'}</td>
								<td align="center">{$sku.$sku_key.artno|default:'-'}</td>
								<td>{$sku.$sku_key.description}</td>
								{if $branch_name|upper eq 'ALL'}
										{if $branch_group.header}
										{foreach from=$branch_group.header key=bgid item=bg}
											{assign var=bgid value=$bgid+10000}
											<td class="r" nowrap>
												{$r.qty.$bgid|qty_nf|ifzero:'-'}
												 {if $sessioninfo.privilege.SHOW_COST}
												<br>
												{$r.tcost.$bgid/$r.qty.$bgid|number_format:2|ifzero:'-'}
											  {/if}
											</td>
										{/foreach}
										{/if}
					
										{foreach from=$branches key=bid item=b}
											{if !$branch_group.have_group.$bid}
												<td class="r" nowrap>
													{$r.qty.$bid|qty_nf|ifzero:'-'}
					
													 {if $sessioninfo.privilege.SHOW_COST}
													<br>
													{$r.tcost.$bid/$r.qty.$bid|number_format:$config.global_cost_decimal_points|ifzero:'-'}
												{/if}
												</td>
											{/if}
										{/foreach}
									<td class="r" nowrap>
										{$r.qty.total|qty_nf|ifzero:'-'}<!--<br>
										{$r.tcost.total|number_format:2|ifzero:'-'}-->
									</td>
								{else}
									{if $is_bg_id>0}
										{foreach from=$branch_group.items.$is_bg_id key=bid item=b}
											<td class="r" nowrap>{$r.qty.$bid|qty_nf|ifzero:'-'}</td>
										{/foreach}
										<td class="r" nowrap>{$r.qty.total|qty_nf|ifzero:'-'}</td>
									{else}
										<td class="r" nowrap>{$r.qty|qty_nf|ifzero:'-'}</td>
										{if !$smarty.request.group_sku}
										<td class="r" nowrap>{$r.price|number_format:2|ifzero:'-'}</td>
										<td class="r" nowrap>{$r.tprice|number_format:2|ifzero:'-'}</td>
										{/if}
											  {if $sessioninfo.privilege.SHOW_COST}
										<td class="r" nowrap>{$r.tcost/$r.qty*-1|number_format:$config.global_cost_decimal_points|ifzero:'-'}</td>
										<td class="r" nowrap>{$r.tcost|number_format:2|ifzero:'-'}</td>
										{/if}
									{/if}
								{/if}
							</tr>
						</div>
					{/foreach}
				<tr class="header">
					<th colspan="4" class="r">Total</th>
					{if $branch_name|upper eq 'ALL'}
						{if $branch_group.header}
							{foreach from=$branch_group.header key=bgid item=bg}
								{assign var=bgid value=$bgid+10000}
								<th class="r" nowrap>
									{$total.$cat_id.qty.$bgid|qty_nf|ifzero:'-'}<!--<br>
									{$total.$cat_id.tcost.$bgid|number_format:2|ifzero:'-'}-->
								</th>
							{/foreach}
						{/if}
						{foreach from=$branches key=bid item=b}
							{if !$branch_group.have_group.$bid}
								<th class="r" nowrap>
									{$total.$cat_id.qty.$bid|qty_nf|ifzero:'-'}<!--<br>
									{$total.$cat_id.tcost.$bid|number_format:2|ifzero:'-'}-->
								</th>
							{/if}
						{/foreach}
						<th class="r" nowrap>
							{$total.$cat_id.qty.total|qty_nf|ifzero:'-'}<!--<br>
							{$total.$cat_id.tcost.total|number_format:2|ifzero:'-'}-->
						</th>
					{else}
						{if $is_bg_id>0}
							{foreach from=$branch_group.items.$is_bg_id key=bid item=b}
								<th class="r" nowrap>{$total.$cat_id.qty.$bid|qty_nf|ifzero:'-'}</th>
							{/foreach}
							<th class="r" nowrap>{$total.$cat_id.qty.total|qty_nf|ifzero:'-'}</th>
						{else}
							<th class="r" nowrap>{$total.$cat_id.qty|qty_nf|ifzero:'-'}</th>
							{if !$smarty.request.group_sku}
							<th class="r" nowrap>&nbsp;</th>
							<th class="r" nowrap>{$total.$cat_id.tprice|number_format:2|ifzero:'-'}</th>
							{/if}
							  {if $sessioninfo.privilege.SHOW_COST}
							<th class="r" nowrap>&nbsp;</th>
							<th class="r" nowrap>{$total.$cat_id.tcost|number_format:2|ifzero:'-'}</th>
							{/if}
						{/if}
					{/if}
				</tr>
				</table>
		</div>
	</div>
</div>
{/foreach}
{/if}
{if !$no_header_footer}
{literal}
<script type="text/javascript">
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

