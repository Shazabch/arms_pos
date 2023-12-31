{*
10/14/2011 11:03:32 AM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/10/2016 10:00 Edwin
- Enhanced on show branch group in report.

7/10/2017 5:17 PM Justin
- Bug fixed on wording wrongly.
- Enhanced to show title while mouseover to sales qty and amount.

10/16/2018 5:37 PM Justin
- Bug fixed on system couldn't differentiate SKU items with "Un-categorised" or "No 4th Level Category".

06/30/2020 02:08 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
{literal}
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

function close_sub(tbody_id,img_id){
    $(tbody_id).style.display = 'none';
    $(img_id).src = '/ui/expand.gif';
}

function changeLabel(val){
	if(val=='yearly'){
		$('month_label').innerHTML = 'Start from';
		$('notice').innerHTML = 'Note: Report will Shown 1 year Only';
	}else{
        $('month_label').innerHTML = '';
        $('notice').innerHTML = 'Note: Report will Shown 1 month Only';
	}

}
{/literal}
</script>

<style>
{literal}
.c1 { background:#ff9; }
.c2 { background:#fff; }

.c3 { background:#9f9; }
.c3_2 { background:#70ffc0; }

.c4 { background:#ffccff; }
.c4_2 { background:#ffa0f0; }
.c5 { background:#6699ff; }
.c5_2 { background:#00d0ff; }
.c6 { background:#ffbb00;}
.c6_2 { background:#e5ff00;}
.c7 { background:#7fc0f0;}
.c7_2 { background:#7ff0c0;}

.r1 { background:#c0fff0;}
.r1_2 { background:#ffe4e1;}
.r2 { background:#f0f0f0;}
.r3 { background:#33ff00;}
.r4 { background:#fff0c0;}

.h1 { background:#00f0ff !important;}

option.bg {
	font-weight:bold;
	padding-left:10px;
}

option.bg_item {
	padding-left:20px;
}
{/literal}
</style>
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
		<form method=post class=form name=report_form>
			<input type=hidden name=report_title value="{$report_title}">
			<p>
				<div class="form-label">
					<input name=view_type type=radio value='monthly' {if $smarty.request.view_type ne 'yearly'} checked {/if} onClick="changeLabel(this.value)"><b>
						By Day</b>
				<input name=view_type type=radio value='yearly' {if $smarty.request.view_type eq 'yearly'} checked {/if} onClick="changeLabel(this.value)"><b>
					By Month</b>
				</div>
			</p>
			<p>
			<b>
			<span id="month_label">{if $smarty.request.view_type eq 'yearly'} Start from {/if}</span></b>
			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Month</b> 
				<select class="form-control" name=month>
				{section loop=12 name=i}
					<option value="{$smarty.section.i.iteration}" {if $smarty.request.month eq $smarty.section.i.iteration}selected{/if}>{$months[$smarty.section.i.iteration]}</option>
				{/section}
				</select>
				</div>
	
				<div class="col-md-4">
					<b class="form-label">Year</b> 
				<select class="form-control" name=year>
				{foreach from=$years item=y}
					<option value="{$y.year}" {if $smarty.request.year eq $y.year}selected{/if}>{$y.year}</option>
				{/foreach}
				</select>
				</div>
	
				<div class="col-md-4">
					<div class="form-label mt-4">
						<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
					</div>
				</div>
				
			</div>

			</p>
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Department</b>
				<select class="form-control" name="department_id">
				<option value=0>-- All --</option>
				{foreach from=$departments item=dept}
				<option value={$dept.id} {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
				{/foreach}
			</select>
				</div>

			
		<div class="col-md-4">
			{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b>
			 <select class="form-control" name="branch_id">
					<option value="">-- All --</option>
					{foreach from=$branches item=b}
						{if !$branch_group.have_group[$b.id]}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code} - {$b.description}</option>
						{/if}
					{/foreach}
					{if $branch_group.header}
						<optgroup label="Branch Group">
							{foreach from=$branch_group.header key=bgid item=bg}
								<option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
								{foreach from=$branch_group.items.$bgid item=r}
									{if $config.sales_report_branches_exclude}
									{if in_array($r.code, $config.sales_report_branches_exclude)}
									{assign var=skip_this_branch value=1}
									{else}
									{assign var=skip_this_branch value=0}
									{/if}
									{/if}
									{if !$skip_this_branch}
									<option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
									{/if}
								{/foreach}
							{/foreach}
						</optgroup>
					{/if}
				</select>
			
				{*{if $branch_group.header}
					<optgroup label="Branch Group">
						{foreach from=$branch_group.header item=r}
							{capture assign=bgid}bg,{$r.id}{/capture}
							<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
						{/foreach}
					</optgroup>
				{/if}*}
			{/if}
		</div>
			</div>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<br>
			
			<span id="notice">
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				Note:
			{if $smarty.request.view_type eq 'yearly'}
				Report will Shown 1 year Only
			{else}
			Report will Shown 1 month Only
			{/if}
			</div>
			</span>
			
			</form>
	</div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<h2>{$report_title}<!--<br>Branch: {$branch_name} 
	 From: {$date_msg}--></h2>
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover" width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th rowspan=2>Category</th>
						{foreach from=$label item=lbl}
							<th rowspan=2>{$lbl}</th>
						{/foreach}
						<th rowspan=2>Total</th>
						<th rowspan=2>Contribution</th>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<th colspan=2>Gross Profit</th>
						{/if}
						<th rowspan=2>AVG S.price</th>
						<th colspan=2>Selling Price</th>
						<th colspan=3>PWP</th>
					</tr>
					<tr class=header>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<th>Amt</th>
							<th>%</th>
						{/if}
						<th>High</th>
						<th>Low</th>
						<th>Amt</th>
						<th>GP Amt</th>
						<th>GP %</th>
					</tr>
				</thead>
				{foreach from=$table key=code item=c}
				{cycle values="c2,c1" assign=row_class name=row1}
				<tbody class="fs-08">
					<tr>
						<td class="{$row_class}" rowspan=2>{$category.$code.name}
							{if $code > 0}
								{if !$no_header_footer}
								<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$code}',this)">
								{/if}
							{/if}
						</td>
						{assign var=needchange value=1}
						{foreach from=$label key=lbl item=day}
							{if $needchange eq 1}
								<td class="c7 r" title="Sales Qty">{$category.$code.qty.$lbl|qty_nf|ifzero:'-'}</td>
								{assign var=needchange value=0}
							{else}
								<td class="r1_2 r" title="Sales Qty">{$category.$code.qty.$lbl|qty_nf|ifzero:'-'}</td>
								{assign var=needchange value=1}
							{/if}
							
						{/foreach}
						<td class="r1_2 r" title="Sales Qty">{$category.$code.qty.total|qty_nf|ifzero:'-'}</td>
						{if $category.total.qty.total > 0}
							{assign var=unit_cont value=$category.$code.qty.total/$category.total.qty.total}
						{else}
							{assign var=unit_cont value=0}
						{/if}
						<td class="r1_2 r">{$unit_cont*100|number_format:2|ifzero:'-':'%'}</td>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
							<td class="{$row_class} r" rowspan=2>{$category.$code.cost_amt|number_format:2|ifzero:'-'}</td>
							<td class="{$row_class} r" rowspan=2>{$category.$code.cost_per|number_format:2|ifzero:'-':'%'}</td>
						{/if}
					
						{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
					
						<td class="{$amt_class} r" rowspan=2>{$category.$code.avg_sprice|number_format:2|ifzero:'-'}</td>
						<td class="{$row_class} r" rowspan=2>{$category.$code.highest_price|number_format:2|ifzero:'-'}</td>
						<td class="{$row_class} r" rowspan=2>{$category.$code.lowest_price|number_format:2|ifzero:'-'}</td>
						
						{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
						
						<td class="{$amt_class} r" rowspan=2>{$category.$code.pwp|number_format:2|ifzero:'-'}</td>
						<td class="{$amt_class} r" rowspan=2>{$category.$code.pwp_gp_amount|number_format:2|ifzero:'-'}</td>
						<td class="{$amt_class} r" rowspan=2>{$category.$code.pwp_gp_per|number_format:2|ifzero:'-':'%'}</td>
					</tr>
					<tr>
						{assign var=needchange value=1}
						{foreach from=$label key=lbl item=day}
							{if $needchange eq 1}
								<td class="c7_2 r" title="Sales Amount">{$category.$code.amount.$lbl|number_format:2|ifzero:'-'}</td>
								{assign var=needchange value=0}
							{else}
								<td class="r2 r" title="Sales Amount">{$category.$code.amount.$lbl|number_format:2|ifzero:'-'}</td>
								{assign var=needchange value=1}
							{/if}
						{/foreach}
						<td class="r2 r" title="Sales Amount">{$category.$code.amount.total|number_format:2|ifzero:'-'}</td>
						{if $category.total.amount.total > 0}
							{assign var=amount_cont value=$category.$code.amount.total/$category.total.amount.total}
						{else}
							{assign var=amount_cont value=0}
						{/if}
						<td class="r2 r">{$amount_cont*100|number_format:2|ifzero:'-':'%'}</td>
					</tr>
				</tbody>
				
				<tbody class="fs-08" style="display:none" id="tbody_{$code}">
					{foreach from=$c key=s item=r}
					{cycle values="c3,c4" assign=row_class2 name=row2}
					<tr>
						<td rowspan=2 class="{$row_class2}">{$sku.$s.description}</td>
						{assign var=needchange value=1}
						{foreach from=$label key=lbl item=day}
							{if $needchange eq 1}
								<td class="r c6" title="Sales Qty">{$r.qty.$lbl|qty_nf|ifzero:'-'}</td>
								{assign var=needchange value=0}
							{else}
								<td class="r r3" title="Sales Qty">{$r.qty.$lbl|qty_nf|ifzero:'-'}</td>
								{assign var=needchange value=1}
							{/if}
							
						{/foreach}
						<td class="r r3" title="Sales Qty">{$r.qty.total|qty_nf|ifzero:'-'}</td>
						{if $category.$code.qty.total > 0}
							{assign var=unit_cont value=$r.qty.total/$category.$code.qty.total}
						{else}
							{assign var=unit_cont value=0}
						{/if}
						<td class="r r3">{$unit_cont*100|number_format:2|ifzero:'-':'%'}</td>
						{if $sessioninfo.privilege.SHOW_REPORT_GP}
						<td class="r {$row_class2}" rowspan=2>{$r.cost_amt|number_format:2|ifzero:'-'}</td>
						<td class="r {$row_class2}" rowspan=2>{$r.cost_per|number_format:2|ifzero:'-':'%'}</td>
						{/if}
						
						{if $row_class2 eq c3}{assign var=amt_class2 value=c5}{else}{assign var=amt_class2 value=c4_2}{/if}
						
						<td class="r {$amt_class2}" rowspan=2>{$r.avg_sprice|number_format:2|ifzero:'-'}</td>
						<td class="r {$row_class2}" rowspan=2>{$r.highest_price|number_format:2|ifzero:'-'}</td>
						<td class="r {$row_class2}" rowspan=2>{$r.lowest_price|number_format:2|ifzero:'-'}</td>
						
						{if $row_class2 eq c3}{assign var=amt_class2 value=c5}{else}{assign var=amt_class2 value=c4_2}{/if}
						
						<td class="r {$amt_class2}" rowspan=2>{$r.pwp|number_format:2|ifzero:'-'}</td>
						<td class="r {$amt_class2}" rowspan=2>{$r.pwp_gp_amount|number_format:2|ifzero:'-'}</td>
						<td class="r {$amt_class2}" rowspan=2>{$r.pwp_gp_per|number_format:2|ifzero:'-':'%'}</td>
					</tr>
					<tr>
						{assign var=needchange value=1}
						{foreach from=$label key=lbl item=day}
							{if $needchange eq 1}
								<td class="r c6_2" title="Sales Amount">{$r.amount.$lbl|number_format:2|ifzero:'-'}</td>
								{assign var=needchange value=0}
							{else}
								<td class="r r4" title="Sales Amount">{$r.amount.$lbl|number_format:2|ifzero:'-'}</td>
								{assign var=needchange value=1}
							{/if}
						{/foreach}
						<td class="r r4" title="Sales Amount">{$r.amount.total|number_format:2|ifzero:'-'}</td>
						{if $category.$code.amount.total > 0}
							{assign var=amount_cont value=$r.amount.total/$category.$code.amount.total}
						{else}
							{assign var=amount_cont value=0}
						{/if}
						<td class="r r4">{$amount_cont*100|number_format:2|ifzero:'-':'%'}</td>
					</tr>
					{/foreach}
				</tbody>
				{/foreach}
				{cycle values="c2,c1" assign=row_class name=row1}
				<tr>
					<th class="{$row_class} r" rowspan=2>Total</th>
					{assign var=needchange value=1}
					{foreach from=$label key=lbl item=day}
						{if $needchange eq 1}
							<td class="r c7" title="Sales Qty">{$category.total.qty.$lbl|qty_nf|ifzero:'-'}</td>
							{assign var=needchange value=0}
						{else}
							<td class="r r1_2" title="Sales Qty">{$category.total.qty.$lbl|qty_nf|ifzero:'-'}</td>
							{assign var=needchange value=1}
						{/if}
					{/foreach}
					<td class="r r1_2" title="Sales Qty">{$category.total.qty.total|qty_nf|ifzero:'-'}</td>
					<td class="r r1_2">100.00%</td>
					{if $sessioninfo.privilege.SHOW_REPORT_GP}
						<td class="{$row_class} r" rowspan=2>{$category.total.cost_amt|number_format:2|ifzero:'-'}</td>
						<td class="{$row_class} r" rowspan=2>{$category.total.cost_per|number_format:2|ifzero:'-':'%'}</td>
					{/if}
					
					{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
					
					<td class="{$amt_class} r" rowspan=2>{$category.total.avg_sprice|number_format:2|ifzero:'-'}</td>
					<td class="{$row_class} r" rowspan=2>{$category.total.highest_price|number_format:2|ifzero:'-'}</td>
					<td class="{$row_class} r" rowspan=2>{$category.total.lowest_price|number_format:2|ifzero:'-'}</td>
					
					{if $row_class eq c1}{assign var=amt_class value=r1}{else}{assign var=amt_class value=r2}{/if}
					
					<td class="{$amt_class} r" rowspan=2>{$category.total.pwp|number_format:2|ifzero:'-'}</td>
					<td class="{$amt_class} r" rowspan=2>{$category.total.pwp_gp_amount|number_format:2|ifzero:'-'}</td>
					<td class="{$amt_class} r" rowspan=2>{$category.total.pwp_gp_per|number_format:2|ifzero:'-':'%'}</td>
				</tr>
				<tr>
					{assign var=needchange value=1}
					{foreach from=$label key=lbl item=day}
						{if $needchange eq 1}
							<td class="r c7_2" title="Sales Amount">{$category.total.amount.$lbl|number_format:2|ifzero:'-'}</td>
							{assign var=needchange value=0}
						{else}
							<td class="r r2" title="Sales Amount">{$category.total.amount.$lbl|number_format:2|ifzero:'-'}</td>
							{assign var=needchange value=1}
						{/if}
					{/foreach}
					<td class="r r2" title="Sales Amount">{$category.total.amount.total|number_format:2|ifzero:'-'}</td>
					<td class="r r2">100.00%</td>
				</tr>
				</table>
		</div>
	</div>
</div>
{/if}

{include file=footer.tpl}
