{*
2/4/2013 5:34 PM Justin
- Enhanced to show region filter under branch group.
- Enhanced to show and filter branches from regions or branch group base on user's regions.

1/23/2015 5:40 PM Justin
- Enhanced to add a note that shows this report is exclude GST.

06/30/2020 11:31 AM Sheila
- Updated button css.

10/16/2020 12:00 PM William
- Enhanced to add tax checking.
*}

{include file=header.tpl}
{literal}
<style>
.c0 {
	background:#eff;
}
.c1 {
	background:#efa;
}
</style>
{/literal}
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


			<!--
			
			>> june to jan 2007
			june 2006 to jan 2007
			june 2007 to jan 2008
			
			>> jan to march 2007
			jan to march 2007
			jan to march 2008
			
			-->
			
		<div class="row">
			<div class="col-md-3">
				<b class="form-label">Year</b> {dropdown name=year values=$years selected=$smarty.request.year key=year value=year}
			</div>
				
			<div class="col-md-3">
				<b class="form-label">Month</b>
				{*dropdown name=month_from values=$months is_assoc=1 selected=$smarty.request.month_from*}
				<select class="form-control" name="month_from">
				{foreach from=$months key=k item=r}
				<option value="{$k}" {if $smarty.request.month_from eq $k}selected{/if}>{$r}</option>
			  {/foreach}
			  </select>
			</div>
				
			  <div class="col-md-3">
				<b class="form-label">to</b>
				{*dropdown name=month_to values=$months is_assoc=1 selected=$smarty.request.month_to*}
				<select class="form-control" name="month_to">
				{foreach from=$months key=k item=r}
				<option value="{$k}" {if $smarty.request.month_to eq $k}selected{/if}>{$r}</option>
			  {/foreach}
			  </select>
			  </div>
				
				
			{if $branch_group.header}
			<div class="col-md-3">
				<b class="form-label">Branch Group</b>
				<select class="form-control" name="branch_group">
				<option value="">-- All --</option>
				{foreach from=$branch_group.header item=r}
					<option value="{$r.id}" {if $smarty.request.branch_group eq $r.id}selected {/if}>{$r.code}</option>
				{/foreach}
				
				{if $config.consignment_modules && $config.masterfile_branch_region}
					<optgroup label='Region'>
					{foreach from=$config.masterfile_branch_region key=type item=f}
						{if ($sessioninfo.regions && $sessioninfo.regions.$type) || !$sessioninfo.regions}
							{assign var=curr_type value="REGION_`$type`"}
							<option value="REGION_{$type}" {if $smarty.request.branch_group eq $curr_type}selected {/if}>{$type|upper}</option>
						{/if}
					{/foreach}
					</optgroup>
				{/if}
				</select>
			</div>
			{/if}
				
			
			<div class="col-md-3">
				<b class="form-label">Department</b>
				<select class="form-control" name="department_id">
				<option value=0>-- All --</option>
				{foreach from=$departments item=dept}
				<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
				{/foreach}
				</select>
			</div>
		</div>

			<input type=hidden name=submit value=1>
			<input type=hidden name=report_title value="{$report_title}">
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			{if $config.enable_gst || $config.enable_tax}
			<p>
			* Amount show in this report is Tax exclusive.
			</p>
			{/if}
			</form>
	</div>
</div>
{/if}

{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}
				<!--Year: {$smarty.request.year} 
				Month: {$months[$smarty.request.month_from]} to {$months[$smarty.request.month_to]} 
				Department: {$dept_desc}
				{if $smarty.request.branch_group}
				Branch Group: {$branch_group.header[$smarty.request.branch_group].code}
				{/if}--></h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table small_printing table mb-0 text-md-nowrap  table-hover" width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th>Branch</th>
						<th>Year</th>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
							<th>{$i|str_month}</th>
						<!-- {$i++} -->
						{/section}
					</tr>
				</thead>
				{foreach from=$data key=branch item=l}
				<tbody class="fs-08">
					{assign var=y value=$smarty.request.year-1}
					{section name=y loop=2}
					<tr>
						{if $used_branch <> $branch}
							<td rowspan=7>
								{if $branch < 10000}
									{$branches.$branch.code}
								{else}
									{assign var=bg_id value=$branch-10000}
									{$branch_group.header.$bg_id.code}
								{/if}
							</td>
						{/if}
						{assign var=used_branch value=$branch}
						<td>{$y}</td>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
						<td class=c{$i%2} align=right>{$l.$y.$i|number_format:2|ifzero:"-"}</td>
						<!-- {$i++} -->
						{/section}
					</tr>
					<!-- {$y++} -->
					{/section}
					<tr>
						<td>Var $</td>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
						<td class=c{$i%2} align=right>{$var.$branch.$i|number_format:2|ifzero:"-"}</td>
						<!-- {$i++} -->
						{/section}
					</tr>
					<tr>
						<td>Var %</td>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
						{if $l[$smarty.request.year].$i > 0}
						{*math equation= "x / y * 100"  x=$var.$branch.$i y=$l[$smarty.request.year].$i assign=varp*}
						{assign var=x value=$var.$branch.$i}
						{assign var=y value=$l[$smarty.request.year].$i}
					{assign var=varp value=$x/$y*100}
					
						{else}
						{assign var=varp value=0}
						{/if}
						<td class=c{$i%2} align=right>{$varp|number_format:2|ifzero:"0"}%</td>
						<!-- {$i++} -->
						{/section}
					</tr>
					<tr>
					<td>Target</td>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
						<td class=c{$i%2} align=right>{$target.$branch.$i|number_format:2|ifzero:"-"}</td>
						<!-- {$i++} -->
						{/section}
					</tr>
					<tr>
						<td>Var $</td>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
						<td class=c{$i%2} align=right>{$target_var.$branch.$i|number_format:2|ifzero:"-"}</td>
						<!-- {$i++} -->
						{/section}
					</tr>
					<tr>
						<td>Var %</td>
						{assign var=i value=$smarty.request.month_from}
						{section name=i start=$smarty.request.month_from loop=$smarty.request.month_to+1}
						{if $l[$smarty.request.year].$i > 0}
							{*math equation="x / y * 100" x=$target_var.$branch.$i y=$l[$smarty.request.year].$i assign=varp*}
							
							{assign var=x value=$var.$branch.$i}
							{assign var=y value=$l[$smarty.request.year].$i}
							{assign var=varp value=$x/$y*100}
						{else}
							{assign var=varp value=0}
						{/if}
						<td class=c{$i%2} align=right>{$varp|number_format:2|ifzero:"0"}%</td>
						<!-- {$i++} -->
						{/section}
					</tr>
				</tbody>
				{/foreach}
				</table>
		</div>
	</div>
</div>
{/if}
{include file=footer.tpl}

