{*
3/13/2012 12:47:43 PM Justin
- Fixed bugs of limit of 10 vendors checkboxes checking.

7/4/2012 5:41:23 PM Justin
- Enhanced to show GP and GP % and only user with cost privilege can view it.
- Modified the popup message for Use Last Vendor instead of Use GRN.
- Enhanced to show vendor code.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 11:17 AM Sheila
- Updated button css.
*}

{include file=header.tpl}

{if !$no_header_footer}
{literal}

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
</style>
{/literal}

<script>
var check_count = '{$vd_count|default:0}';
{literal}
function vd_toggle(obj){
	if(obj.checked == true) check_count++;
	else check_count--;

	if(check_count>10){
		obj.checked = false;
		alert("Only 10 vendors can be ticked.");
		check_count--;
		return;
	}
	
	if(check_count <= 0){
		document.f_a.use_last_vendor.disabled = true;
		document.f_a.use_last_vendor.checked = false;
	}else document.f_a.use_last_vendor.disabled = false;
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
		<form method="post" class="form" name="f_a">
			<p>
				<div class="row">
					{if $BRANCH_CODE eq 'HQ'}
					<div class="col-md-4">
						<b class="form-label">Branch</b>
					<select class="form-control" name="branch_id" onChange="chk_vd_filter();">
						<option value="">-- All --</option>
						{foreach from=$branches item=b}
						
							{if $config.sales_report_branches_exclude}
							{if in_array($b.code,$config.sales_report_branches_exclude)}
							{assign var=skip_this_branch value=1}
							{else}
							{assign var=skip_this_branch value=0}
							{/if}
							{/if}
						
							{if !$skip_this_branch}
							<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
							{/if}
							
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
				<div class="col-md-4">
					<b class="form-label">Year</b> 
				{dropdown name=year values=$years selected=$smarty.request.year key=year value=year}&nbsp;&nbsp;&nbsp;&nbsp; 
				</div>
				<div class="col-md-4">
					<b class="form-label">Month</b>
				<select class="form-control" name="month">
					{foreach from=$months key=k item=r}
						<option value="{$k}" {if $smarty.request.month eq $k}selected{/if}>{$r}</option>
					{/foreach}
				</select>
				</div>
				</div>
			</p>
			<div>
				<b class="form-label">Vendor</b>
				<div style="padding: 10px; width:50%;height:200px;border:1px solid #ddd;overflow:auto;">
					{assign var=has_vd_checked value=0}
					{foreach from=$vendor item=vd}
						{assign var=vd_id value=$vd.id}
						<input type="checkbox" name="vendor_id_list[{$vd_id}]" value="{$vd_id}" {if $smarty.request.vendor_id_list.$vd_id}checked {assign var=has_vd_checked value=1}{/if} onclick="vd_toggle(this);">&nbsp;{$vd.description}<br />
					{/foreach}
				</div>
				<div class="row form-inline form-label ml-1 mt-2 mb-2">
					<input type="checkbox" id="use_last_vendor" name="use_last_vendor" {if $smarty.request.use_last_vendor}checked{elseif !$vd_count}disabled{/if}> <label for="use_grn"><b>&nbsp;Use Last Vendor</b></label> [<a href="javascript:void(0)" onclick="alert('The last Vendor before the selected Month and Year is the selected vendor.')">?</a>]
				&nbsp;&nbsp;
				<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
				&nbsp;&nbsp;
				</div>
			</div>
			<p>
			<input type="hidden" name="submit" value="1" />
			<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name="output_excel">{#OUTPUT_EXCEL#}</button>
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
				<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width="100%">
					<thead class="bg-gray-100">
						<tr class="header">
							<th width="3%" nowrap>#</th>
							<th width="10%" nowrap>Code</th>
							<th width="56%" nowrap>Vendor</th>
							<th width="10%" nowrap>Sales</th>
							{if	$sessioninfo.privilege.SHOW_COST}
								<th width="7%" nowrap>Cost</th>
								<th width="7%" nowrap>GP</th>
								<th width="7%" nowrap>GP (%)</th>
							{/if}
						</tr>
					</thead>
					{foreach from=$table item=vd key=vd_id name=vd_loop}
						<tbody class="fs-08">
							<tr>
								<td>{$smarty.foreach.vd_loop.iteration}.</td>
								<td>{$vd.code}</td>
								<td>{$vd.description}</td>
								<td align="right">{$vd.amount|number_format:2}</td>
								{if	$sessioninfo.privilege.SHOW_COST}
									<td align="right">{$vd.cost|number_format:$config.global_cost_decimal_points}</td>
									<td align="right">{$vd.gp|number_format:2}</td>
									<td align="right">{$vd.gp_per|number_format:2}</td>
								{/if}
							</tr>
						</tbody>
						{assign var=ttl_amt value=$ttl_amt+$vd.amount}
						{assign var=ttl_cost value=$ttl_cost+$vd.cost}
					{/foreach}
					<tr class="header">
						<th class="r" colspan="3">Total</th>
						<th class="r">{$ttl_amt|number_format:2|ifzero:'-'}</th>
						{if	$sessioninfo.privilege.SHOW_COST}
							<th class="r">{$ttl_cost|number_format:$config.global_cost_decimal_points|ifzero:'-'}</th>
							<th class="r">
								{assign var=ttl_gp value=$ttl_amt-$ttl_cost}
								{$ttl_gp|number_format:2|ifzero:'-'}
							</th>
							<th class="r">
								{assign var=ttl_gp_per value=$ttl_gp/$ttl_amt*100}
								{$ttl_gp_per|number_format:2|ifzero:'-'}
							</th>
						{/if}
					</tr>
				</table>
			</div>
		</div>
	</div>
{/if}

{include file=footer.tpl}
