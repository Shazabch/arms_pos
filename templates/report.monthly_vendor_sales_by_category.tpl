{*
3/11/2011 10:35:35 AM Justin
- Added the value = 1 for use GRN.

10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

10/27/2011 5:33:55 PM Andy
- Fix "Use GRN" script.

11/14/2011 11:37:37 AM Andy
- Add sorting by ARMS Code, MCode, Artno, Description and Old Code.

11/23/2011 5:45:37 PM Andy
- Fix sorting does not work correctly when choose "All branches".

11/24/2011 2:34:30 PM Andy
- Change "Use GRN" popup information message.

10/24/2012 1:58:00 PM Fithri
- can group by SKU - affect total qty calculation (based on uom fraction)
- remove (hide) sorting by description

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

06/30/2020 11:47 AM Sheila
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

function chk_vd_filter(){
	var allow_use_grn = false;
	if(document.report_form['branch_id']){
		if(document.report_form['branch_id'].value>0){
			allow_use_grn = true;
		}
	}else	allow_use_grn = true;
	
	if(!int(document.report_form['vendor_id'].value))	allow_use_grn = false;	
	
	if(allow_use_grn)	document.report_form['GRN'].disabled = false;
	else	document.report_form['GRN'].disabled = true;
}

function change_sort_by(ele){
	if(ele.value=='')   $('span_sort_order').hide();
	else    $('span_sort_order').show();
}
{/literal}
</script>

<style>
{literal}
.r1 { background:#f9a; }
.r2 { background:#6cf; }
.c1 { background:#ff9; }
.c2 { background:#aff; }
.c3 { background:#faf; }
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
			<div class="row">
				
			<div class="col-md-3">
				<b class="form-label">From</b>
			<div class="form-inline">
				<input class="form-control" size=14 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
				&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			<div class="col-md-3">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" size=14 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
			&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			
			<div class="col-md-3">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> <select class="form-control" name="branch_id" onChange="chk_vd_filter();">
					<option value="">-- All --</option>
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
			
			<div class="col-md-3">
				<b class="form-label">Sort by</b>
					<select class="form-control" name="sort_by" onChange="change_sort_by(this);">
						<option value="">--</option>
						<option value="sku_item_code" {if $smarty.request.sort_by eq 'sku_item_code'}selected {/if}>ARMS Code</option>
						<option value="artno" {if $smarty.request.sort_by eq 'artno'}selected {/if}>Art No</option>
						<option value="mcode" {if $smarty.request.sort_by eq 'mcode'}selected {/if}>MCode</option>
						<option value="description" {if $smarty.request.sort_by eq 'description'}selected {/if}>Description</option>
						{* if $config.link_code_name}
							<option value="linkcode" {if $smarty.request.sort_by eq 'linkcode'}selected {/if}>{$config.link_code_name}</option>
						{/if *}
					</select>
					<span id="span_sort_order" style="{if !$smarty.request.sort_by}display:none;{/if}">
					<select class="form-control mt-2" name="sort_order">
						<option value="asc" {if $smarty.request.sort_order eq 'asc'}selected {/if}>Ascending</option>
						<option value="desc" {if $smarty.request.sort_order eq 'desc'}selected {/if}>Descending</option>
					</select>
					</span>
			
			</div>
			</div>
			<p>
			{include file="category_autocomplete.tpl" all=true}
			</p><p>
			
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">SKU_Type</b>
				<select class="form-control" name="sku_type_code">
				<option value="all" {if $smarty.request.sku_type_code eq 'all'}selected{/if}>All </option>
				{foreach from=$sku_type item=r}
				<option value={$r.code} {if $smarty.request.sku_type_code eq $r.code}selected{/if}>{$r.description} </option>
				{/foreach}
				
				</select>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">Vendor</b>
				<select class="form-control" name="vendor_id" onChange="chk_vd_filter();">
				<option value="all" {if $smarty.request.vendor_id eq 'all'} selected {/if}>-- All --</option>
				{foreach from=$vendor item=r}
				<option value={$r.id} {if $smarty.request.vendor_id eq $r.id}selected{/if}>{$r.description} </option>
				{/foreach}
				</select>
				</div>
			</div>
			
			<div class="form-label mt-2">
				<input type=checkbox {if $smarty.request.GRN}checked{/if} name=GRN value="1" {if !$smarty.request.vendor_id or !$smarty.request.branch_id}disabled {/if} /> <b>&nbsp;Use GRN</b> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
			&nbsp;&nbsp;
			<input type=checkbox {if $smarty.request.group_by_sku}checked{/if} name=group_by_sku value="1" /><b>&nbsp;Group By SKU</b>
			
			
			<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>&nbsp;Exclude inactive SKU</b></label>
			
			</div>
			
			<input type=hidden name=submit value=1>
			<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
			{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
			<button class="btn btn-info" name=output_excel>{#OUTPUT_EXCEL#}</button>
			{/if}
			</p>
			<div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
				<b>Note:</b> Report Maximum Shown 1 Year
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
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">
				{$report_title}

<!--Branch: {$branch_name}  Vendor: {$vendor_name|default:"All"}-->
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="report_table table mb-0 text-md-nowrap  table-hover" width=100%>
				<thead class="bg-gray-100">
					<tr class=header>
						<th>ARMS Code</th>
						<th>Art No</th>
						<th>MCode</th>
						<th>Description</th>
						{foreach from=$label item=lbl}
							<th>{$lbl}</th>
						{/foreach}
						<th>Total</th>
						<th>Contribution<br>(%)</th>
					</tr>
				</thead>
				
				{foreach from=$table item=t key=c}
				<tbody class="fs-08" style="display:" id="tbody_{$c}">
					<tr>
						<th colspan=4 class=r1>{$category.$c.name}
						{if !$no_header_footer}
						<img src=/ui/collapse.gif onclick="close_sub('tbody_{$c}','img_{$c}')">
						{/if}
						</th>
						<th colspan="{count var=$label}" class=r1></th>
						<th colspan=2 class=r1></th>
					</tr>
					{foreach from=$t item=r}
					<tr>
						<td rowspan=2 class=c1>{if !$r.is_parent && $group_by_sku}&nbsp;&nbsp;{/if}{$r.sku_item_code}</td>
						<td rowspan=2 class=c1>{if !$r.is_parent && $group_by_sku}&nbsp;&nbsp;{/if}{$r.artno}</td>
						<td rowspan=2 class=c1>{if !$r.is_parent && $group_by_sku}&nbsp;&nbsp;{/if}{$r.mcode}</td>
						<td rowspan=2>{if !$r.is_parent && $group_by_sku}&nbsp;&nbsp;{/if}{$r.description}</td>
						{foreach from=$label key=date item=lbl}
							<td class="c2 r">{$r.quantity.$date|number_format|ifzero:"-"}</td>
						{/foreach}
						<td class="c2 r">{$r.quantity.total|number_format|ifzero:"-"}</td>
						<td class="c2 r">{$r.quantity.total/$category.$c.quantity.total*100|number_format:2|ifzero:"-":"%"}</td>
					</tr>
					<tr>
						{foreach from=$label key=date item=lbl}
							<td class="c3 r">{$r.amount.$date|number_format:2|ifzero:"-"}</td>
						{/foreach}
						<td class="c3 r">{$r.amount.total|number_format:2|ifzero:"-"}</td>
						<td class="c3 r">{$r.amount.total/$category.$c.amount.total*100|number_format:2|ifzero:"-":"%"}</td>
					</tr>
					{/foreach}
				</tbody>
				<tbody>
					<tr>
						<th colspan=4 rowspan=2 class="r2 r"><!--Sub Total of--> {$category.$c.name}
						{if !$no_header_footer}
						<img src=/ui/expand.gif onclick="toggle_sub('tbody_{$c}',this)" id='img_{$c}'>
						{/if}
						</th>
						{foreach from=$label key=date item=lbl}
							<td class="c2 r">{$category.$c.quantity.$date|number_format|ifzero:"-"}</td>
						{/foreach}
						<td class="c2 r">{$category.$c.quantity.total|number_format|ifzero:"-"}</td>
						<td class="c2 r">{$category.$c.quantity.total/$category.total.quantity.total*100|number_format:2|ifzero:"-":"%"}</td>
					</tr>
					<tr>
						{foreach from=$label key=date item=lbl}
							<td class="c3 r">{$category.$c.amount.$date|number_format:2|ifzero:"-"}</td>
						{/foreach}
						<td class="c3 r">{$category.$c.amount.total|number_format:2|ifzero:"-"}</td>
						<td class="c3 r">{$category.$c.amount.total/$category.total.amount.total*100|number_format:2|ifzero:"-":"%"}</td>
					</tr>
				</tbody>
				{/foreach}
				<tr>
					<th colspan=4 rowspan=2 class="c1 r">Total of {$dept_name}</th>
					{foreach from=$label key=date item=lbl}
						<td class="c2 r">{$category.total.quantity.$date|number_format|ifzero:"-"}</td>
					{/foreach}
					<td class="c2 r">{$category.total.quantity.total|number_format|ifzero:"-"}</td>
				</tr>
				<tr>
					{foreach from=$label key=date item=lbl}
						<td class="c3 r">{$category.total.amount.$date|number_format:2|ifzero:"-"}</td>
					{/foreach}
					<td class="c3 r">{$category.total.amount.total|number_format:2|ifzero:"-"}</td>
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

