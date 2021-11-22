{*
7/15/2011 3:00:36 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

11/11/2011 3:04:31 PM Andy
- Add a notice for user to know "Promotion Summary" is not included "Mix and Match Promotion".

2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

10/17/2014 9:45 AM Fithri
- add option to select "Items in Promotion"
- improve formatting

06/30/2020 04:00 PM Sheila
- Updated button css.
*}

{if !$smarty.request.print}
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
		.c0 { background:#eff; }
		.c1 { background:#efa; }
		.csunday { color:#f00; }
		.report_table td{ font-size:10px; }
		</style>
		<script>
		var LOADING = '<img src=/ui/clock.gif align=absmiddle> ';
		function get_brand(val,selected_id)
		{
			$('brand_select').innerHTML = LOADING;
		//	new Ajax('report.get_brand.php?brand_id='+val+'&selected_code='+selected_code,{evalScripts:true, update:$('brand_select')}).request();
			new Ajax.Updater('brand_select','?a=ajax_get_brand_by_category_id&show_all=1&category_id='+val+'&selected='+selected_id);
		}

		function change_type()
		{
			obj = document.f_a.type;
			/*
			if(obj.value == 'sku')
			{
				$('category_select').style.display = 'none';
				$('sku_select').style.display = '';
			}
			else if(obj.value == 'category')
			{
				$('category_select').style.display = '';
				$('sku_select').style.display = 'none';
			}
			else
			{
				$('category_select').style.display = 'none';
				$('sku_select').style.display = 'none';
			}
			*/
			
			switch (obj.value)
			{
				case 'sku':
					$('category_select').hide();
					$('sku_select').show();
					break;
					
				case 'category':
					$('category_select').show();
					$('sku_select').hide();
					break;
					
				case 'items_in_promotion':
					$('category_select').hide();
					$('sku_select').hide();
					break;
					
				default:
					alert('unknown option');
			}
		}

		function do_print()
		{
			document.f_a.target = 'ifprint';
			document.f_a.print.value = 1;
		}

		function do_excel()
		{
			document.f_a.target = '';
			document.f_a.print.value = 0;
		}

		function do_show_report()
		{
			document.f_a.target = '';
			document.f_a.print.value = 0;
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
		<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
		<div class="card mx-3">
			<div class="card-body">
				<form method=post class=form name="f_a" onSubmit="passArrayToInput()">
					<input type=hidden name=print value=0>

					<div class="row">
						<div class="col">
							<b class="form-label">Date</b> 
					<div class="form-inline">
						<input class="form-control"  size=15 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
					&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
					</div>
						</div>
					
			
					<div class="col">
						{if $BRANCH_CODE eq 'HQ'}
						<b class="form-label">Branch</b> 
						{dropdown name=branch_id all="-- All --" values=$branches selected=$smarty.request.branch_id key=id value=code}
					{/if}
					</div>
					
					<div class="col">
						<b class="form-label">Approval Status</b>
					<select  class="form-control" name="approved">
					<option value="1" {if $smarty.request.approved}selected{/if}>Approved</option>
					<option value="0" {if !$smarty.request.approved}selected{/if}>Not approved</option>
					</select>
					</div>
					
					<div class="col">
						<b class="form-label">Search by</b> 
					<select class="form-control" name=type onchange="change_type();">
					<option value="sku" {if $smarty.request.type eq 'sku'}selected{/if}>SKU</option>
					<option value="category" {if $smarty.request.type eq 'category'}selected{/if}>Category/Brand</option>
					<option value="items_in_promotion" {if $smarty.request.type eq 'items_in_promotion'}selected{/if}>Items in promotion</option>
					</select>
					</div>
					</div>
					<br>
					<div id="sku_select" style="display:none;">{include file="sku_items_autocomplete_multiple.tpl"}</div>
					<div id="category_select" style="display:none;">{include file="category_autocomplete.tpl"  autocomplete_callback="get_brand($('category_id').value,'')"}<br>
					<b class="form-label">Brand</b>
					<span id="brand_select"></span>
					
					</div>
			
					<input type=hidden name=submit value=1>
					<button class="btn btn-primary" name=show_report onclick="do_show_report();">{#SHOW_REPORT#}</button>
					<button class="btn btn-info" name=output_excel onclick="do_excel();">{#OUTPUT_EXCEL#}</button>
					<button class="btn btn-primary" name=print_report onclick="do_print();">Print</button>
					
					<div class="alert alert-primary rounded mt-2" style="max-width: 400px;">
						<ul style="list-style-type:none;">
							{*{if $config.enable_mix_and_match_promotion}{/if}*}
							<li> This report does not include Mix & Match Promotion</li>
						</ul>
					</div>
					
					</form>
			</div>
		</div>
	{/if}
{else}
	{include file=report_header.landscape.tpl print_me=1}
	{literal}
	<style>
	table.report_table {
		border-collapse: collapse;
		border-left:1px solid #000;
		border-top:1px solid #000;
		font-size:9px;
	}

	table.report_table td, table.report_table th{
		padding:4px;
		border-bottom: 1px solid #000;
		border-right:1px solid #000;
	}

	table.report_table tr.header td, table.report_table tr.header th{
		background:#fe9;
		padding:6px 4px;
	}
	</style>
	{/literal}
	<h1>{$PAGE_TITLE}</h1>
	
{/if}
{if !$data}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
{if $smarty.request.print}
{assign var=branch_title value=$branches[$smarty.request.branch_id].code}
<b style="color:#ccc">
Date: {$smarty.request.date_from},
Branch: {$branch_title|default:"All"},
Approval Status: {if $smarty.request.approved}Approved{else}Not Approved{/if},
{if $smarty.request.type eq 'category'}
	Category: {$smarty.request.category},
	Brand: {if $smarty.request.brand_id > 0}{$selected_brand.description}{elseif $smarty.request.brand_id eq 'All'}All{else}UNBRANDED{/if}
{elseif $smarty.request.type eq 'sku'}
	<br>Arms code: {$smarty.request.sku_code_list_2}
{/if}
</b>
{/if}
{foreach from=$data key=promo_id item=promo}
<h3>{$header.$promo_id.title} ({$header.$promo_id.date_from|date_format:"%Y-%m-%d"} {$header.$promo_id.time_from|date_format:"%H:%M"} to {$header.$promo_id.date_to|date_format:"%Y-%m-%d"} {$header.$promo_id.time_to|date_format:"%H:%M"})</h3>

	<table class=report_table width={if $smarty.request.print}95{else}100{/if}% cellpadding=0 cellspacing=0>
	<tr class=header>
		<th rowspan=2>&nbsp;</th>
		<th rowspan=2 width=80>SKU</th>
		{if $BRANCH_CODE eq 'HQ'}
		<th rowspan=2>Branch</th>
		{/if}
		<th rowspan=2>Selling<br>Price</th>
		<th colspan=5>Member</th>
		<th colspan=5>Non Member</th>
	</tr>
	<tr class=header>
		<th>Disc</th>
		<th>Price</th>
		<th>Min Items</th>
		<th>Qty From</th>
		<th>Qty To</th> 
		<th>Disc</th>
		<th>Price</th>
		<th>Min Items</th>
		<th>Qty From</th>
		<th>Qty To</th>	
	</tr>
	{foreach name=i from=$promo item=p}
		<tr>
			<td>{$smarty.foreach.i.iteration}</td>
			<td>
				{$p.description|truncate:28:''}<br>
				({$p.sku_item_code}{if $p.categoryname ne 'SOFTLINE'}{if $p.mcode}, {$p.mcode}{/if}{/if}{if $p.artno}, {$p.artno}{/if})	
			</td>
			{if $BRANCH_CODE eq 'HQ'}
				<td>{$p.code}</td>
			{/if}
			<td align=right>{$p.price|number_format:2}</td>
			{if strstr($p.member_disc_p,'%')}
				<td align=right>{$p.member_disc_p|ifzero:""}</td>
			{else}
				{if $p.member_disc_p}
				<td align=right>{$p.member_disc_p|number_format:2|ifzero:""}</td>
				{else}
				<td align=right>&nbsp;</td>
				{/if}
			{/if}
			<td align=right>{$p.member_disc_a|ifzero:""}</td>
			<td align=right>{$p.member_min_item|ifzero:""}</td>
			<td align=right>{$p.member_qty_from|ifzero:""}</td>
			<td align=right>{$p.member_qty_to|ifzero:""}</td>
			<td align=right>{$p.non_member_disc_p|ifzero:""}</td>
			<td align=right>{$p.non_member_disc_a|ifzero:""}</td>
			<td align=right>{$p.non_member_min_item|ifzero:""}</td>
			<td align=right>{$p.non_member_qty_from|ifzero:""}</td>
			<td align=right>{$p.non_member_qty_to|ifzero:""}</td>

		</tr>
	{/foreach}	
</table>
{/foreach}
{*
{assign var=i value=0}
{assign var=page_n value=0}
{foreach name=i from=$data item=r}

	{if (($smarty.foreach.i.iteration-1)%$page_size==0)}
		{if !$smarty.foreach.i.first}
			</table><br>
			{if $smarty.request.print}{include file=report_footer.landscape.tpl}{/if}
		{/if}
		{if $smarty.request.print}
			{if !$skip_header}
			{include file='header.print.tpl'}

			<body onload="window.print()">
			{/if}
		{/if}
		{if !$smarty.foreach.i.last}
			{if $smarty.request.print}
				{assign var=page_n value=$page_n+1}
				{config_load file="site.conf"}
	


				<div class=printarea>
				<table width=100% cellspacing=0 cellpadding=0 border=0>
				<tr>
					<td><img src="{get_logo_url mod='promotion'}" height=50 hspace=5 vspace=5></td>
					<td class="small" nowrap>
						<h2>{$branch.description}</h2>
						{$branch.address}<br>
						Tel: {$branch.phone_1}{if $branch.phone_2} / {$branch.phone_2}{/if} &nbsp;&nbsp; Fax: {$branch.phone_3}
					</td>

					<td width=33% align=right nowrap>
				  		<h4>{$subtitle_r}</h4>
				  		<b>Page {$page_n}/{$page_total}</b>
					</td>
				</tr>
				<tr>
					<td colspan=3 align=center nowrap>
						<h1>{$title}</h1>
					</td>
				</tr>
			{/if}
		{/if}
	<table class=report_table width={if $smarty.request.print}95{else}100{/if}% cellpadding=0 cellspacing=0>
	<tr class=header>
		<th rowspan=2>&nbsp;</th>
		<th rowspan=2 width=80>SKU</th>
		<th rowspan=2>Promotion Title</th>
		{if $BRANCH_CODE eq 'HQ'}
		<th rowspan=2>Branch</th>
		{/if}
		<th rowspan=2>Promotion Period</th>
		<th rowspan=2>Selling<br>Price</th>
		<th colspan=5>Member</th>
		<th colspan=5>Non Member</th>
	</tr>
	<tr class=header>
	<th>Disc</th>
	<th>Price</th>
	<th>Min Items</th>
	<th>Qty From</th>
	<th>Qty To</th> 
	<th>Disc</th>
	<th>Price</th>
	<th>Min Items</th>
	<th>Qty From</th>
	<th>Qty To</th>	
	</tr>
	{/if}
<tr>
{if $is_used != $r.sku_item_id}
	{assign var=sku_item_id value=$r.sku_item_id}
	{assign var=i value=$i+1}
	<td rowspan={$count.$sku_item_id}>{$i}</td>
	<td rowspan={$count.$sku_item_id}>
		{if $smarty.request.print}<div style="width:150px;height:25px;overflow:hidden">{/if}
		{$r.description|truncate:28:''}<br>
		({$r.sku_item_code}{if $r.categoryname ne 'SOFTLINE'}{if $r.mcode}, {$r.mcode}{/if}{/if}{if $r.artno}, {$r.artno}{/if})
		{if $smarty.request.print}</div>{/if}
	</td>
	{assign var=is_used value=$r.sku_item_id}
{/if}
<td>{$r.title}<br>({$r.promo_id})</td>
{if $BRANCH_CODE eq 'HQ'}
	<td>{$r.code}</td>
{/if}
<td>{$r.date_from|date_format:"%y/%m/%d"}-{$r.date_to|date_format:"%y/%m/%d"} {$r.time_from|date_format:"%H:%M"}-{$r.time_to|date_format:"%H:%M"}</td>
<td align=right>{$r.price|number_format:2}</td>
{if strstr($r.member_disc_p,'%')}
	<td align=right>{$r.member_disc_p|ifzero:""}</td>
{else}
	<td align=right>{$r.member_disc_p|number_format:2|ifzero:""}</td>
{/if}
<td align=right>{$r.member_disc_a|number_format:2}</td>
<td align=right>{$r.member_min_item|ifzero:""}</td>
<td align=right>{$r.member_qty_from|ifzero:""}</td>
<td align=right>{$r.member_qty_to|ifzero:""}</td>
<td align=right>{$r.non_member_disc_p|ifzero:""}</td>
<td align=right>{$r.non_member_disc_a|ifzero:""}</td>
<td align=right>{$r.non_member_min_item|ifzero:""}</td>
<td align=right>{$r.non_member_qty_from|ifzero:""}</td>
<td align=right>{$r.non_member_qty_to|ifzero:""}</td>
</tr>
{/foreach}
</table>
*}
{/if}
{if !$no_header_footer and !$smarty.request.print}
<script type="text/javascript">
get_brand($('category_id').value,'{$smarty.request.brand_id}');
{literal}

function selectAll()
{
	for(i=0;i<document.f_a.sku_code_list.length;i++)
	{
		document.f_a.sku_code_list[i].selected = true;
	}
}

change_type();

    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });


{/literal}
</script>
{include file=footer.tpl}
{else}
{include file=report_footer.landscape.tpl}
{/if}


