{*
5/25/2011 9:59:25 AM Alex
- change title of report

10/17/2011 11:09:30 AM Alex
- Modified the Ctn and Pcs round up to base on config set.
- Modified the round up for cost to base on config.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

12/2/2014 5:00 PM Andy
- Enhance to get gst and selling price after gst.

2/9/2017 10:43 AM Andy
- Enhanced to show MCode, Art No and Old Code.

4/7/2017 9:06 AM Justin
- Enhanced to change the GP% to use range filter instead of using one filter only.
- Enhanced to change the GP% filter able to select "Below" and "Between" selections.

4/10/2017 11:15 AM Justin
- Bug fixed on invalid gp percentage javascript validation.

06/30/2020 02:42 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
{literal}
<style>
.red { color:red; }
</style>
<script>
function curtain_clicked()
{
	curtain(false);
	hide_inventory_popups();
}

function data_validate(){
	if(document.f_a['gp_type'] == 2 && document.f_a['percentage_from'].value > document.f_a['percentage_to'].value){
		alert("Invalid GP percentage.");
		return false;
	}

	return true;
}

function gp_type_changed(){
	if(document.f_a['gp_type'].value == 1){
		showdiv("gp_below");
		hidediv("gp_between");
	}else{
		hidediv("gp_below");
		showdiv("gp_between");
	}
}
</script>
{/literal}
{/if}
<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}</li>
{/foreach}
</ul>
{/if}
{if !$no_header_footer}
{include file=popup.inventory_popups.tpl}

<form method="post" name="f_a" class="form" onsubmit="return data_validate();">
<input type="hidden" name="report_title" value="{$report_title}">
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
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
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
<b>GP</b>
	<select name="gp_type" onchange="gp_type_changed();">
		<option value="1" {if !$smarty.request.gp_type || $smarty.request.gp_type eq 1}selected{/if}>Below</option>
		<option value="2" {if $smarty.request.gp_type eq 2}selected{/if}>Between</option>
	</select>
	<span id="gp_below" {if $smarty.request.gp_type eq 2}style="display:none;"{/if}>
		<input type="text" name="percentage" value="{$smarty.request.percentage|default:'0'}" size="2" onchange="mf(this);">
	</span>
	<span id="gp_between" {if !$smarty.request.gp_type || $smarty.request.gp_type eq 1}style="display:none;"{/if}>
		<input type="text" name="percentage_from" value="{$smarty.request.percentage_from|default:'0'}" size="2" onchange="mf(this);">% <b>To</b> <input type="text" name="percentage_to" value="{$smarty.request.percentage_to|default:'0'}" size="2" onchange="mf(this);">
	</span>	
	%&nbsp;&nbsp;&nbsp;&nbsp;
<b>SKU Type</b> {dropdown name=sku_type all="-- All --" values=$sku_type key=code value=description selected=$smarty.request.sku_type}&nbsp;&nbsp;&nbsp;&nbsp;
<input type=checkbox name=hidezero {if $smarty.request.hidezero}checked{/if}> hide Zero qty
&nbsp;&nbsp;&nbsp;&nbsp;
<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label><br />
{include file=category_autocomplete.tpl all=true}<br>
<input type=hidden name=submit value=1>
<button class="btn btn-primary" name=show_report>{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name=output_excel>{#OUTPUT_EXCEL#}</button>
{/if}
<br>
<i>Note: GP% = (Selling-Cost)/Selling. Cost is based on Last GRN Cost.</i>
</form>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<h3>{$report_title}</h3>
{foreach from=$table key=br item=rows}

{if $rows}
<h4>Branch: {$branches.$br.code} ({count var=$rows} items)</h4>

<ul>
<li> click on item row for inventory detail</li>
</ul>
<table class=report_table width=100%>
<tr class=header>
	<th width="10" rowspan="2">&nbsp;</th>
	<th rowspan="2">ARMS Code</th>
	<th rowspan="2">MCode</th>
	<th rowspan="2">Art No</th>
	<th rowspan="2">{$config.link_code_name}</th>
	<th rowspan="2">Description</th>
	{assign var=cols value=1}
	{if $config.enable_gst}
		{assign var=cols value=$cols+2}
	{/if}
	<th colspan="{$cols}">Selling</th>
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
		<th rowspan="2">Cost</th>
		<th rowspan="2">GP</th>
		<th rowspan="2">GP(%)</th>
	{/if}
	<th rowspan="2">Balance<br>(PCS)</th>
</tr>
<tr class="header">
	<th>Normal {if $config.enable_gst}before GST{/if}</th>
	{if $config.enable_gst}
		<th>GST</th>
		<th>After GST</th>
	{/if}
</tr>

{assign var=n value=1}
{foreach from=$rows item=r}
{assign var=gp value=$r.price-$r.cost}
<tr class="thover clickable" onclick="show_inventory('sku_item_id',{$r.id},{$r.bid})">
	<td>{$n++}.</td><td>{$r.sku_item_code}</td>
	<td>{$r.mcode|default:'-'}</td>
	<td>{$r.artno|default:'-'}</td>
	<td>{$r.link_code|default:'-'}</td>
	<td>{$r.description}</td>
	<td align=right>{$r.price|round2}</td>
	{if $config.enable_gst}
		<td align="right">{$r.gst_amt|number_format:2}</td>
		<td align="right">{$r.price_after_gst|number_format:2}</td>
	{/if}
	{if $sessioninfo.privilege.SHOW_REPORT_GP}
		<td align=right>{$r.cost|number_format:$config.global_cost_decimal_points}</td>
		<td {if $gp<=0}class="red"{/if} align="right">{$gp|round2}</td>
		<td {if $gp<=0}class="red"{/if} align="right">{$gp/$r.price*100|round2}%</td>
	{/if}
	<td align=right>{$r.qty|qty_nf}{if $r.changed}<font color=red>*</font>{/if}</td>
</tr>
{/foreach}
</table>
<br><br>
{/if}
{/foreach}

{/if}

{include file=footer.tpl}

