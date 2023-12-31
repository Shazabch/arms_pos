{*
10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

11/15/2011 4:47:30 PM Andy
- Fix toggle "Use GRN" checkbox error.

11/24/2011 2:45:50 PM Andy
- Change "Use GRN" popup information message.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

3/4/2020 3:14 PM William
- Fixed bug colspan of table not correct.

06/30/2020 02:08 PM Sheila
- Updated button css.
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

function check_use_grn(){
	var allow_use_grn = true;
	
	if(document.f_a['branch_id']){
		if(document.f_a['branch_id'].value.indexOf('bg')>=0 || !document.f_a['branch_id'].value)	allow_use_grn = false;
	}
	
	if(document.f_a['vendor_id'].value=='all')	allow_use_grn = false;
	
	if(allow_use_grn){
		$('inp_use_grn').disabled = false;
	}else{
		$('inp_use_grn').disabled = true;
		$('inp_use_grn').checked = false;
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
				<div class="col">
					<b class="form-label">Date From</b>
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			
				</div>
			
			<div class="col">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size=23 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> 
			<select class="form-control" name="branch_id" onChange="check_use_grn();">
					<option value="" {if $smarty.request.branch_id eq ''}selected{/if}>-- All --</option>
					{foreach from=$branches item=b}
						<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
					{/foreach}
				</select>
			{/if}
			</div>
			</div>
			<p>
			{include file="category_autocomplete.tpl" all=true}
			
			<div class="row">
				<div class="col">
					<b class="form-label">SKU_Type</b>
				<select class="form-control" name="sku_type_code">
				<option value="all" {if $smarty.request.sku_type_code eq 'all'}selected{/if}>-- All --</option>
				{foreach from=$sku_type item=r}
				<option value={$r.code} {if $smarty.request.sku_type_code eq $r.code}selected{/if}>{$r.description} </option>
				{/foreach}
				
				</select>
				</div>
				
				<div class="col"><b class="form-label">Vendor</b>
					<select class="form-control" name="vendor_id" onChange="check_use_grn();">
						<option value="all" {if $smarty.request.vendor_id eq 'all'}selected{/if}>-- All --</option>
						{foreach from=$vendor item=r}
						<option value={$r.id} {if $smarty.request.vendor_id eq $r.id}selected{/if}>{$r.description} </option>
						{/foreach}
					</select>
					</div>
				<div class="col">
					<div class="form-label">
						<input type="checkbox" id="inp_use_grn" {if $smarty.request.GRN eq true}checked{/if} name="GRN" /> <b>Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
				
				<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
					</div>
				</div>
			</div>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			</form>
	</div>
</div>
<script>check_use_grn();</script>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}

{assign var=show_type value='amount'}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}

<!--From {$smarty.request.date_from} to {$smarty.request.date_to}  Vendor: {$vendor_name}SKU Type: {$sku_type_choose}Branch: {$branch_name}--></h2>
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class=report_table width=100%>
				<thead class="bg-gray-100">
					{if $sku_type_choose|lower ne 'all'}
				<tr class=header>
					<th>ARMS Code</th>
					<th>Description</th>
					{foreach from=$label item=branch_name}
						<th>{$branch_name}</th>
					{/foreach}
					<th>Total</th>
					<th>Average Selling</th>
					<!--<th colspan="2">Contribution %</th>-->
				</tr>
				{else}
				<tr class=header>
					<th rowspan="2">ARMS Code</th>
					<th rowspan="2">Description</th>
					{assign var=col_num value=$sku_type|@count}
					{assign var=col_num value=$col_num+1}
					{foreach from=$label item=branch_name}
						<th colspan="{$col_num}">{$branch_name}</th>
					{/foreach}
					<th colspan="{$col_num}">Total</th>
					<th colspan="{$col_num}">Average Selling</th>
					<!--<th colspan="2">Contribution %</th>-->
				</tr>
				<tr class="header">
					{foreach from=$label item=branch_name}
						{foreach from=$sku_type item=r}
							<th>{$r.description}</th>
						{/foreach}
						<th>Total</th>
					{/foreach}
					{foreach from=$sku_type item=r}
							<th>{$r.description}</th>
					{/foreach}
					<th>Total</th>
					{foreach from=$sku_type item=r}
							<th>{$r.description}</th>
					{/foreach}
					<th>Total</th>
				</tr>
				{/if}
				</thead>
				
				{foreach from=$table key=code item=c}
				<tr>
					<td class=r4 rowspan=2 colspan=2>{if $category2.$code.description ne ''}{$category2.$code.description}{else}{$category2.root.description}{/if}
					{if !$no_header_footer}
					<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$code}',this)">
					{/if}
					</td>
					{foreach from=$label key=lbl item=branch_name}
						{if $sku_type_choose|lower eq 'all'}
							{foreach from=$sku_type item=ss}
								{assign var=sku_code value=$ss.code}
								<td class="r c3">{$category2.$code.qty.$sku_code.$lbl|qty_nf|ifzero:'-'}</td>
							{/foreach}
						{/if}
						<td class="r c3">{$category2.$code.qty.all_type.$lbl|qty_nf|ifzero:'-'}</td>
					{/foreach}
					<!-- Total -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c3">{$category2.$code.qty.$sku_code.total|qty_nf|ifzero:'-'}</td>
						{/foreach}
					{/if}
					<td class="r c3">{$category2.$code.qty.all_type.total|qty_nf|ifzero:'-'}</td>
					<!-- Average Selling -->
						{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c3"></td>
						{/foreach}
					{/if}
					<td class="r c3"></td>
					{if $category2.total.qty.total > 0}
					   {assign var=contribution value=$category2.$code.qty.total/$category2.total.qty.total}
					   {assign var=contribution value=$contribution*100}
					{else}
						{assign var=contribution value=''}
					{/if}
					<!--<td class="r c3">{$contribution|number_format:2|ifzero:'-':'%'}</td>-->
				</tr>
				<tr>
					{foreach from=$label key=lbl item=branch_name}
						{if $sku_type_choose|lower eq 'all'}
							{foreach from=$sku_type item=ss}
								{assign var=sku_code value=$ss.code}
								<td class="r c4">{$category2.$code.amount.$sku_code.$lbl|number_format:2|ifzero:'-'}</td>
							{/foreach}
						{/if}
						<td class="r c4">{$category2.$code.amount.all_type.$lbl|number_format:2|ifzero:'-'}</td>
					{/foreach}
					<!-- Total -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c4">{$category2.$code.amount.$sku_code.total|number_format:2|ifzero:'-'}</td>
						{/foreach}
					{/if}
					<td class="r c4">{$category2.$code.amount.all_type.total|number_format:2|ifzero:'-'}</td>
					<!-- Average Selling -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c4">{$category2.$code.avg_sell.$sku_code.total|number_format:2|ifzero:'-'}</td>
						{/foreach}
					{/if}
					<td class="r c4">{$category2.$code.avg_sell.all_type.total|number_format:2|ifzero:'-'}</td>
					 
					{if $category2.total.amount.total > 0}
					   {assign var=contribution value=$category2.$code.amount.total/$category2.total.amount.total}
					   {assign var=contribution value=$contribution*100}
					{else}
						{assign var=contribution value=''}
					{/if}
					<!--<td class="r c4">{$contribution|number_format:2|ifzero:'-':'%'}</td>-->
				</tr>
				<tbody class="fs-08" style="display:none" id="tbody_{$code}">
					{foreach from=$c key=s item=r}
						{cycle values="c2,c1" assign=row_class}
						<tr>
							<td class="{$row_class}" rowspan=2>{$sku.$s.sku_item_code}</td>
							<td class="{$row_class}" rowspan=2>{$sku.$s.description}</td>
							{foreach from=$label key=lbl item=branch_name}
								{if $sku_type_choose|lower eq 'all'}
									{foreach from=$sku_type item=ss}
										{assign var=sku_code value=$ss.code}
										<td class="r3 r">{$r.qty.$sku_code.$lbl|qty_nf|ifzero:'-'}</td>
									{/foreach}
								{/if}
								<td class="r3 r">{$r.qty.all_type.$lbl|qty_nf|ifzero:'-'}</td>
							{/foreach}
							<!-- Total -->
							{if $sku_type_choose|lower eq 'all'}
								{foreach from=$sku_type item=ss}
									{assign var=sku_code value=$ss.code}
									<td class="r3 r">{$r.qty.$sku_code.total|qty_nf|ifzero:'-'}</td>
								{/foreach}
							{/if}
							<td class="r3 r">{$r.qty.all_type.total|qty_nf|ifzero:'-'}</td>
							<!-- Avg Sell -->
							{if $sku_type_choose|lower eq 'all'}
								{foreach from=$sku_type item=ss}
									{assign var=sku_code value=$ss.code}
									<td class="r3 r"></td>
								{/foreach}
							{/if}
							<td class="r3 r"></td>
							{if $category2.$code.qty.total > 0}
								{assign var=contribution value=$r.qty.total/$category2.$code.qty.total}
								{assign var=contribution value=$contribution*100}
							{else}
								{assign var=contribution value=''}
							{/if}
							<!--<td class="r3 r">{$contribution|number_format:2|ifzero:'-':'%'}</td>-->
						</tr>
						<tr>
							{foreach from=$label key=lbl item=branch_name}
								{if $sku_type_choose|lower eq 'all'}
									{foreach from=$sku_type item=ss}
										{assign var=sku_code value=$ss.code}
										<td class="c5 r">{$r.amount.$sku_code.$lbl|number_format:2|ifzero:'-'}</td>
									{/foreach}
								{/if}
								<td class="c5 r">{$r.amount.all_type.$lbl|number_format:2|ifzero:'-'}</td>
							{/foreach}
							<!-- Total -->
							{if $sku_type_choose|lower eq 'all'}
								{foreach from=$sku_type item=ss}
									{assign var=sku_code value=$ss.code}
									<td class="c5 r">{$r.amount.$sku_code.total|number_format:2|ifzero:'-'}</td>
								{/foreach}
							{/if}
							<td class="c5 r">{$r.amount.all_type.total|number_format:2|ifzero:'-'}</td>
							<!-- Avg Sell -->
							{if $sku_type_choose|lower eq 'all'}
								{foreach from=$sku_type item=ss}
									{assign var=sku_code value=$ss.code}
									<td class="c5 r">{$r.avg_sell.$sku_code.total|number_format:2|ifzero:'-'}</td>
								{/foreach}
							{/if}
							<td class="c5 r">{$r.avg_sell.all_type.total|number_format:2|ifzero:'-'}</td>
							
							{if $category2.$code.amount.total > 0}
								{assign var=contribution value=$r.amount.total/$category2.$code.amount.total}
								{assign var=contribution value=$contribution*100}
							{else}
								{assign var=contribution value=''}
							{/if}
							<!--<td class="c5 r">{$contribution|number_format:2|ifzero:'-':'%'}</td>-->
						</tr>
					{/foreach}
				</tbody>
				{/foreach}
				<tr>
					<td class="r4 r" rowspan=2 colspan=2>Total</td>
					{foreach from=$label key=lbl item=branch_name}
						{if $sku_type_choose|lower eq 'all'}
							{foreach from=$sku_type item=ss}
								{assign var=sku_code value=$ss.code}
								<td class="r c3">{$category2.total.qty.$sku_code.$lbl|qty_nf|ifzero:'-'}</td>
							{/foreach}
						{/if}
						<td class="r c3">{$category2.total.qty.all_type.$lbl|qty_nf|ifzero:'-'}</td>
					{/foreach}
					<!-- Total -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c3">{$category2.total.qty.$sku_code.total|qty_nf|ifzero:'-'}</td>
						{/foreach}
					{/if}
					<td class="r c3">{$category2.total.qty.all_type.total|qty_nf|ifzero:'-'}</td>
					<!-- Avg Sell -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c3"></td>
						{/foreach}
					{/if}
					<td class="r c3"></td>
					<!--<td class="r c3">100.00%</td>-->
				</tr>
				<tr>
					{foreach from=$label key=lbl item=branch_name}
						{if $sku_type_choose|lower eq 'all'}
							{foreach from=$sku_type item=ss}
								{assign var=sku_code value=$ss.code}
								<td class="r c4">{$category2.total.amount.$sku_code.$lbl|number_format:2|ifzero:'-'}</td>
							{/foreach}
						{/if}
						<td class="r c4">{$category2.total.amount.all_type.$lbl|number_format:2|ifzero:'-'}</td>
					{/foreach}
					<!-- Total -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c4">{$category2.total.amount.$sku_code.total|number_format:2|ifzero:'-'}</td>
						{/foreach}
					{/if}
					<td class="r c4">{$category2.total.amount.all_type.total|number_format:2|ifzero:'-'}</td>
					<!-- Avg Sell -->
					{if $sku_type_choose|lower eq 'all'}
						{foreach from=$sku_type item=ss}
							{assign var=sku_code value=$ss.code}
							<td class="r c4">{$category2.total.avg_sell.$sku_code.total|number_format:2|ifzero:'-'}</td>
						{/foreach}
					{/if}
					<td class="r c4">{$category2.total.avg_sell.all_type.total|number_format:2|ifzero:'-'}</td>
					<!--<td class="r c4">100.00%</td>-->
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
