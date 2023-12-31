{*
10/14/2011 5:18:27 PM Alex
- Modified the Ctn and Pcs round up to base on config set. 

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items
*}

{include file=header.tpl}
{if !$no_header_footer}
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
{literal}
.c1 { background:#ff9; }
.c2 { background:none; }
.r1 { background:#33ff99;}
.r2 { background:#ff99ff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
{/literal}
</style>

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
</script>
{/literal}
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE|default:"Daily PWP SKU Sales"}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if !$no_header_footer}
<div class="card mx-3">
	<div class="card-body">
		<form method=post class=form name="f_a">
			<input type=hidden name=report_title value="{$report_title}">
			
			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Date From</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-4">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" size=22 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				
				<div class="col-md-4">
					{if $BRANCH_CODE eq 'HQ'}
				<b class="form-label">Branch</b> 
				<select class="form-control" name="branch_id">
					  <option value="" {if $smarty.request.branch_id eq ''}selected{/if}>-- All --</option>
						{foreach from=$branches item=b}
							{if !$branch_group.have_group[$b.id]}
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
				{/if}
				</div>
			</div>
			
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p>
			<div class="form-label form-inline">
				<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</div>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				<b>Note:</b> Report Maximum Shown 31 days
			</div>
			</form>
	</div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}
				<!--Branch: {$branch_name|default:"All"}
Category : {$cname|default:"All"}
From: {$smarty.request.date_from}
To: {$smarty.request.date_to}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $err}
	<div class="alert alert-danger mx-3 rounded">
		<ul style="color:red;">
			{foreach from=$err item=e}
				<li>{$e}</li>
			{/foreach}
		</ul>
	</div>
{/if}


{assign var=day_count value=''}
{assign var=cols_left value=''}
{assign var=rowspan value=1}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover" width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th {if $label|@count>16}rowspan=2{/if}>ARMS Code<br>Art.No</th>
						<th {if $label|@count>16}rowspan=2{/if}>Description</th>
						{foreach from=$label item=d}
							{if $day_count eq 16}
								<th {if $label|@count>16}rowspan=2{/if}>Total</th>
					</tr>
								<tr class=header>
							{/if}
							<th>{$d}</th>
							{assign var=day_count value=$day_count+1}
					
						{/foreach}
						{if $day_count<=16}
							{assign var=cols_left value=16}
							{assign var=cols_left value=$cols_left-$day_count}
							<th>Total</th>
						{else}
							{assign var=rowspan value=2}
							{assign var=cols_left value=32}
							{assign var=cols_left value=$cols_left-$day_count}
							<th colspan="{$cols_left}"></th>
						{/if}
					
						</tr>
					</tr>
				</thead>
				{foreach from=$table key=code item=c}
				<tbody class="fs-08">
					<tr>
						<td class=r4 rowspan="{$rowspan}" colspan=2>{$category2.$code.description}
						{if !$no_header_footer}
						<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$code}',this)">
						{/if}
						</td>
						{assign var=day_count value=''}
						{foreach from=$label key=lbl item=day}
							{if $day_count eq 16}
								<th rowspan="{$rowspan}" class="r r4">
									{$category2.$code.qty.total|qty_nf|ifzero:'-'}<br>
									{$category2.$code.amount.total|number_format:2|ifzero:'-'}
								</th>
								</tr>
								<tr>
							{/if}
							<td class="{if $day_count < 16}r1{else}r2{/if} r">
								{$category2.$code.qty.$lbl|qty_nf|ifzero:'-'}<br>
								{$category2.$code.amount.$lbl|number_format:2|ifzero:'-'}
							</td>
							{assign var=day_count value=$day_count+1}
						{/foreach}
						{if $day_count<=16}
							<th rowspan="{$rowspan}" class="r r4">
									{$category2.$code.qty.total|qty_nf|ifzero:'-'}<br>
									{$category2.$code.amount.total|number_format:2|ifzero:'-'}
								</th>
						{else}
							{assign var=cols_left value=32}
							{assign var=cols_left value=$cols_left-$day_count}
							<th colspan="{$cols_left}"></th>
						{/if}
						</tr>
				</tbody>
												<!-- Inside Tbody-->
					<tbody class="fs-08" style="display:none" id="tbody_{$code}">
						{foreach from=$c key=s item=r}
							{cycle values="c2,c1" assign=row_class}
							<tr>
								<td class="{$row_class}" rowspan={$rowspan}>{$sku.$s.sku_item_code}<br>ArtNo: {$sku.$s.artno}</td>
								<td class="{$row_class}" rowspan={$rowspan}>{$sku.$s.description}</td>
												<!-- Start of day Count-->
								{assign var=day_count value=''}
								{foreach from=$label key=lbl item=day}
									{if $day_count eq 16}
										<th rowspan="{$rowspan}" class="r {$row_class}">
											{$r.qty.total|qty_nf|ifzero:'-'}<br>
											{$r.amount.total|number_format:2|ifzero:'-'}
										</th>
										</tr>
										<tr>
									{/if}
									<td class="{if $day_count < 16}r1{else}r2{/if} r">
										{$r.qty.$lbl|qty_nf|ifzero:'-'}<br>
										{$r.amount.$lbl|number_format:2|ifzero:'-'}
									</td>
									{assign var=day_count value=$day_count+1}
								{/foreach}
											<!-- End of day Count-->
										{if $day_count<=16}
									<th rowspan="{$rowspan}" class="r {$row_class}">
											{$r.qty.total|qty_nf|ifzero:'-'}<br>
											{$r.amount.total|number_format:2|ifzero:'-'}
										</th>
								{else}
									{assign var=cols_left value=32}
									{assign var=cols_left value=$cols_left-$day_count}
									<th colspan="{$cols_left}"></th>
								{/if}
							</tr>
						{/foreach}
					</tbody>
												<!-- End of Tbody-->
				{/foreach}
				<tr>
					<td class="r4 r" rowspan="{$rowspan}" colspan=2>Total</td>
					{assign var=day_count value=''}
					{foreach from=$label key=lbl item=day}
						{if $day_count eq 16}
							<th rowspan="{$rowspan}" class="r r4">
								{$category2.total.qty.total|qty_nf|ifzero:'-'}<br>
								{$category2.total.amount.total|number_format:2|ifzero:'-'}
							</th>
							</tr>
							<tr>
						{/if}
						<td class="{if $day_count < 16}r1{else}r2{/if} r">
							{$category2.total.qty.$lbl|qty_nf|ifzero:'-'}<br>
							{$category2.total.amount.$lbl|number_format:2|ifzero:'-'}
						</td>
						{assign var=day_count value=$day_count+1}
					{/foreach}
					{if $day_count<=16}
						<th rowspan="{$rowspan}" class="r r4">
								{$category2.total.qty.total|qty_nf|ifzero:'-'}<br>
								{$category2.total.amount.total|number_format:2|ifzero:'-'}
							</th>
					{else}
						{assign var=cols_left value=32}
						{assign var=cols_left value=$cols_left-$day_count}
						<th colspan="{$cols_left}"></th>
					{/if}
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

