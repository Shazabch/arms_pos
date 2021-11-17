{*
10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

2/26/2019 5:48 PM Andy
- Enhanced the report to show item Old Code.
*}

{include file=header.tpl}
{if !$no_header_footer}
<style>
{literal}
.c1 { background:#ff9; }
.c2 { background:none; }
.c3 { background:#aff; }
.c4 { background:#faf; }
.c5 { background:#aaffaa; }
.c6 { background:#33ffaa; }
.r1 { background:#33ff99;}
.r2 { background:#ff99ff;}
.r3 { background:#33ff00;}
.r4 { background:#3399ff;}
{/literal}
</style>

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
		<form method=post class=form name="f_a">
			<input type=hidden name=report_title value="{$report_title}">

			<div class="row">
				<div class="col-md-4">
					<b class="form-label">Date</b>
			<div class="form-inline">
				<input class="form-control" type=text name=date id=date value="{$smarty.request.date|ifzero:$smarty.now|date_format:'%Y-%m-%d'}" size=23>
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
				</div>

			
				{if $branch_group.header}
			<div class="col-md-4">
				<b class="form-label">Branch Group</b>
				<select class="form-control" name="branch_group">
				<option value="">-- All --</option>
				{foreach from=$branch_group.header item=r}
					<option value="{$r.id}" {if $smarty.request.branch_group eq $r.id}selected {/if}>{$r.code}</option>
				{/foreach}
				</select>
			</div>
			{/if}
			
			<div class="col-md-4">
			<div class="form-label mt-4">
				<input type=radio name=filter_date value="mtd" {if $smarty.request.filter_date ne 'ytd'}checked{/if}><b>&nbsp;MTD</b>
				<input type=radio name=filter_date value="ytd" {if $smarty.request.filter_date eq 'ytd'}checked{/if}><b>&nbsp;YTD</b>
			</div>
			</div>
			
			</div>
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p>

			<div class="row">
				<div class="col">
					<b class="form-label">Minimum Transaction Count</b> 
			<input class="form-control" size=23 type=text name=min_tran value="{$smarty.request.min_tran}">
			
				</div>

			<div class="col">
				<b class="form-label">Minimum Amount</b> 
			<input class="form-control" size=23 type=text name=min_amount value="{$smarty.request.min_amount}">
			</div>

			<div class="col">
				<b class="form-label">From</b>
			<div class="form-inline">
				<select class="form-control" name=order_type>
					<option value=top {if $smarty.request.order_type eq 'top'}selected{/if}>Top</option>
					<option value=bottom {if $smarty.request.order_type eq 'bottom'}selected{/if}>Bottom</option>
					</select>
					&nbsp;<input class="form-control" size=5 type=text name=filter_number value="{$filter_number|default:10}">
			</div>
			
			</div>
		</div>
			
			
			
			<div class="row mt-2">
				<div class="col">
					<b class="form-label"> By </b>
			<select class="form-control" name=quantity_amount_type id=quantity_amount_type>
			<option value="amount" {if $smarty.request.quantity_amount_type eq 'amount'}selected{/if}>Amount</option>
			<option value="qty" {if $smarty.request.quantity_amount_type eq 'qty'}selected{/if}>Quantity</option>
			<option value="gp" {if $smarty.request.quantity_amount_type eq 'gp'}selected{/if}>GP Amount</option>
			<option value="cost" {if $smarty.request.quantity_amount_type eq 'cost'}selected{/if}>Cost</option>
			</select>
			(Max 1000)
				</div>
			
			
			<div class="col">
				<b class="form-label">Display Item Code:</b> 
			<div class="form-label form-inline mt-2">
				<input type="checkbox" name="display_item_code[mcode]" value="1" {if $smarty.request.display_item_code.mcode}checked {/if} /> MCode &nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="display_item_code[artno]" value="1" {if $smarty.request.display_item_code.artno}checked {/if} /> Artno &nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="display_item_code[link_code]" value="1" {if $smarty.request.display_item_code.link_code}checked {/if} /> {$config.link_code_name} &nbsp;&nbsp;&nbsp;
			</div>
			
			</div>
			</div>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			<div class="form-label mt-2">
				<input type="checkbox" name="group_sku" {if $smarty.request.group_sku}checked {/if}> <b>&nbsp;Group by SKU</b>
			
			<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			</div>
			</form>
	</div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
{if $smarty.request.quantity_amount_type eq 'qty'}
    {assign var=show_type value='amount'}
{else}
    {assign var=show_type value=$smarty.request.quantity_amount_type}
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}
<!--Start Date: {$start_date}
End Date: {$end_date} 
{if $smarty.request.branch_group}
Branch Group: {$branch_group.header[$smarty.request.branch_group].code}
{/if}
List by: {$smarty.request.quantity_amount_type}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $smarty.request.display_item_code.mcode}
	{assign var=show_mcode value=1}
{/if}

{if $smarty.request.display_item_code.artno}
	{assign var=show_artno value=1}
{/if}

{if $smarty.request.display_item_code.link_code}
	{assign var=show_old_code value=1}
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=report_table width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th></th>
						<th>{if $smarty.request.group_sku}SKU ID{else}ARMS Code{/if}</th>
						{if $show_mcode}<th>MCode</th>{/if}
						{if $show_artno}<th>Artno</th>{/if}
						{if $show_old_code}<th>{$config.link_code_name}</th>{/if}
						
						<th>Description</th>
						{foreach from=$label item=branch_name}
							<th>{$branch_name}</th>
							<th>%</th>
						{/foreach}
						<th>Total</th>
						<th>%</th>
					</tr>
				</thead>
				
				{section loop=$table name=i max=$filter_number}
				{assign var=temp value=''}
				{cycle values="c2,c1" assign=row_class}
				<tbody class="fs-08">
					<tr>
						<td rowspan=2 class="{$row_class}">{$smarty.section.i.iteration}</td>
						<td rowspan=2 class="{$row_class}">{if $smarty.request.group_sku}{$table[i].sku_id}{else}{$table[i].sku_item_code}{/if}</td>
						{if $show_mcode}
							<td rowspan=2 class="{$row_class}">{$table[i].mcode}</td>
						{/if}
						{if $show_artno}
							<td rowspan=2 class="{$row_class}">{$table[i].artno}</td>
						{/if}
						{if $show_old_code}
							<td rowspan=2 class="{$row_class}">{$table[i].link_code}</td>
						{/if}
						
						
						<td rowspan=2 class="{$row_class}">{$table[i].description}</td>
						{foreach from=$label key=code item=r}
							<td class="c3 r">{$table[i].qty.$code|qty_nf|ifzero:'-'}</td>
							{if $table2.qty.$code >0}
								{assign var=temp value=$table[i].qty.$code/$table2.qty.$code}
							{/if}
							
							<td class="c5 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
							{assign var=temp value=''}
						{/foreach}
						<td class="c3 r">{$table[i].qty.total|qty_nf|ifzero:'-'}</td>
						{if $table2.qty.total >0}
							{assign var=temp value=$table[i].qty.total/$table2.qty.total}
						{/if}
						<td class="c5 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
						{assign var=temp value=''}
					</tr>
				</tbody>
				<tr>
					{foreach from=$label key=code item=r}
						<td class="c4 r">{$table[i].$show_type.$code|number_format:2|ifzero:'-'}</td>
						{if $table2.$show_type.$code >0}
							{assign var=temp value=$table[i].$show_type.$code/$table2.$show_type.$code}
						{/if}
						<td class="c6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
						{assign var=temp value=''}
					{/foreach}
					<td class="c4 r">{$table[i].$show_type.total|number_format:2|ifzero:'-'}</td>
					{if $table2.$show_type.total >0}
						{assign var=temp value=$table[i].$show_type.total/$table2.$show_type.total}
					{/if}
					<td class="c6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
					 {assign var=temp value=''}
				</tr>
				{/section}
				<tr>
					{assign var=cols value=3}
					{if $show_mcode}{assign var=cols value=$cols+1}{/if}
					{if $show_artno}{assign var=cols value=$cols+1}{/if}
					{if $show_old_code}{assign var=cols value=$cols+1}{/if}
					
					<th colspan="{$cols}" rowspan=2 class="c1 r">Total</th>
					{foreach from=$label key=code item=r}
						<td class="c3 r">{$table2.qty.$code|qty_nf|ifzero:'-'}</td>
						<td class="c5 r">{if $table2.qty.$code ne ''}100.00%{else}-{/if}</td>
					{/foreach}
					<td class="c3 r">{$table2.qty.total|qty_nf|ifzero:'-'}</td>
					<td class="c5 r">{if $table2.qty.total ne ''}100.00%{else}-{/if}</td>
				</tr>
				<tr>
					{foreach from=$label key=code item=r}
						<td class="c4 r">{$table2.$show_type.$code|number_format:2|ifzero:'-'}</td>
						<td class="c6 r">{if $table2.$show_type.$code ne ''}100.00%{else}-{/if}</td>
					{/foreach}
					<td class="c4 r">{$table2.$show_type.total|number_format:2|ifzero:'-'}</td>
					<td class="c6 r">{if $table2.$show_type.total ne ''}100.00%{else}-{/if}</td>
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
        inputField     :    "date",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
</script>
{/literal}
{/if}
{include file=footer.tpl}

