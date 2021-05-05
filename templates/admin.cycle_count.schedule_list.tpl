{*
06/25/2020 04:43 PM Sheila
- Updated button css
*}

{include file='header.tpl'}

{if !$no_header_footer}
<style>
{literal}
.status_approved{
	background-color:#f90;
	color:#fff;
	font-weight: bold;
}

.status_printed{
	background-color:#09c;
	color:#fff;
	font-weight: bold;
}

.status_wip{
	background-color: yellow;
	color:#000;
	font-weight: bold;
}

.status_completed{
	background-color: #C9FFE3;
	color:#000;
	font-weight: bold;
}

.status_sent_to_stock_take{
	background-color: #091;
	color:#fff;
	font-weight: bold;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var CC_SHCEDULE = {
	f: undefined,
	initialise: function(){
		this.f = document.f_a;
	},
	// function when user click on show data
	show_report: function(t){
		this.f['export_excel'].value = '';
		
		if(t){
			if(t == 'excel'){
				this.f['export_excel'].value = 1;
			}
		}
		
		if(!check_required_field(this.f))	return;
		
		this.f.submit();
	}
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li>{$e}</li>
		{/foreach}
	</ul>
{/if}

{if !$no_header_footer}
	<div class="stdframe noprint" style="background-color: #fff;">
		<form name="f_a" onSubmit="return false;" method="post">
			<input type="hidden" name="load_data" value="1" />
			<input type="hidden" name="export_excel" value="" />
		
			{if $BRANCH_CODE eq 'HQ'}
				<span>
					<b>Stock Take Branch: </b>
					<select name="st_branch_id">
						<option value="">-- All --</option>
						{foreach from=$branches key=bid item=b}
							<option value="{$bid}" {if $smarty.request.st_branch_id eq $bid}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				</span>&nbsp;&nbsp;&nbsp;&nbsp;
			{/if}
			
			<span>
				<b>Year / Month: </b>
				<select name="ym" class="required" title="Month / Year">
					<option value="">-- Please Select --</option>
					{foreach from=$year_month_list key=ym item=desc}
						<option value="{$ym}" {if $smarty.request.ym eq $ym}selected {/if}>{$desc}</option>
					{/foreach}
				</select>
			</span>
			
			<p>
				<input class="btn btn-primary" type="button" value='Show Data' onClick="CC_SHCEDULE.show_report();" /> &nbsp;&nbsp;

				{if $sessioninfo.privilege.EXPORT_EXCEL}
					<button class="btn btn-primary" onClick="CC_SHCEDULE.show_report('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle" /> Export</button>
				{/if}
			</p>
		</form>
	</div>
{/if}

{if $smarty.request.load_data and !$err}
	<br />
	{if !$data}
		* No Data *
	{else}
		<h3>{$report_title}</h3>
		
		<table width="100%" class="report_table xlarge">			
			<tr>
				<td align="center" class="col_header"><b>Total<b/></td>
				<td align="center">{$data.total_count|number_format}</td>
				
				{foreach from=$status_list key=status_key item=v}
					<td align="center" class="status_{$status_key}">{$v}</td>
					<td align="center">{$data.status_list.$status_key.count|number_format}</td>
				{/foreach}
			</tr>
		</table>
		<br />
		
		<table width="100%" class="report_table">
			<tr class="header">
				<th width="40">&nbsp;</th>
				<th width="100">Document No</th>
				<th width="60">Stock Take Branch</th>
				<th>Stock Take Covered / Content</th>
				<th width="80">Estimate SKU Count</th>
				<th width="80">Propose Stock Take Date</th>
				<th width="80">Assigned Stock Take Person</th>
				<th width="120">Status</th>
			</tr>
			
			{foreach from=$data.cc_list key=cc_key item=r name=fcc}
				<tr>
					<td>{$smarty.foreach.fcc.iteration}.</td>
					<td align="center">
						<a href="admin.cycle_count.assignment.php?a=view&branch_id={$r.branch_id}&id={$r.id}" target="_blank">
						{$r.doc_no}
						</a>
					</td>
					<td align="center">{$r.st_bcode}</td>
					<td>
						{if $r.st_content_type eq 'cat_vendor_brand'}
							{if $r.category_id}
								<b>Category: </b>{$r.cat_desc}<br />
							{/if}
							{if $r.vendor_id}
								<b>Vendor: </b>{$r.vendor_desc}<br />
							{/if}
							{if $r.brand_id>=0}
								<b>Brand: </b>{if $r.brand_id eq 0}UN-BRANDED{else}{$r.brand_desc}{/if}<br />
							{/if}
						{elseif $r.st_content_type eq 'sku_group'}
							<b>SKU Group: </b>{$r.sg_code} - {$r.sg_desc}
						{/if}
					</td>
					<td align="right">{$r.estimate_sku_count|number_format}</td>
					<td align="center">{$r.propose_st_date}</td>
					<td align="center">{$r.pic_username}</td>
					<td align="center" class="status_{$r.status_key}">{$status_list[$r.status_key]}</td>
				</tr>
			{/foreach}
		</table>
	{/if}
{/if}


{if !$no_header_footer}
<script>CC_SHCEDULE.initialise();</script>
{include file='footer.tpl'}
{/if}