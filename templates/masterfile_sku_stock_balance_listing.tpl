{*
2/20/2017 1:40 PM Andy
- Change to have sample data in php class.
- Enhance to able to load multiple branch stock.

06/26/2020 Sheila 02:26 PM
- Updated button css.
*}

{include file='header.tpl'}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var SKU_STOCK_BALANCE_LISTING = {
    f_a: undefined,
    initialize: function() {
        this.f_a = document.f_a;
	},
    download_report: function() {
		this.f_a.submit();
		
    },
	hide_errmsg: function(){
		$("data").style.display = "none";
	},
	// select/de-select branch
	check_branch_by_group: function(is_select){
		var bgid = $('sel_brn_grp').value;
		
		if(bgid){	// got select branch group
			$$('#div_branch_list input.inp_branch_group-'+bgid).each(function(ele){
				ele.checked = is_select;
			});
		}else{	// all
			$$('#div_branch_list input.inp_branch').each(function(ele){
				ele.checked = is_select;
			});
		}
	}
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" class="stdframe" method="post">
    <input type="hidden" name="download_report" value="1" />
	
	<div>
		<b>Select Branch By:</b>
		<select id="sel_brn_grp" >
			<option value="">-- All --</option>
			{foreach from=$branch_group.header key=bgid item=bg}
				<option value="{$bgid}" >{$bg.code} - {$bg.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;
		<input class="btn btn-success" type="button" style="width:70px;" value="Select " onclick="SKU_STOCK_BALANCE_LISTING.check_branch_by_group(true);" />&nbsp;
		<input class="btn btn-error" type="button" style="width:70px;" value="De-select" onclick="SKU_STOCK_BALANCE_LISTING.check_branch_by_group(false);" /><br /><br />
		
		<div id="div_branch_list" style="width:100%;height:200px;border:1px solid #ddd;overflow:auto;">
			<table>
			{foreach from=$branches key=bid item=b}
				{assign var=bgid value=$branch_group.have_group.$bid.branch_group_id}
				<tr>
					<td>
						<input class="inp_branch {if $bgid}inp_branch_group-{$bgid}{/if}" type="checkbox" name="branch_id_list[]" value="{$bid}" {if (is_array($smarty.request.branch_id_list) and in_array($bid,$smarty.request.branch_id_list))}checked {/if} id="inp_branch-{$bid}" />&nbsp;
						<label for="inp_branch-{$bid}">{$b.code} - {$b.description}</label>
					</td>
				</tr>
			{/foreach}
			</table>
		</div>
	</div>
	
	
	<p>
		<b>Vendor</b>
		<select name="vendor_id" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
			<option value="">-- All --</option>
			{foreach from=$vendors item=r}
				<option value="{$r.id}" {if $smarty.request.vendor_id eq $r.id}selected {/if}>{$r.description}</option>
			{/foreach}
		</select>
	</p>
	<p onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">{include file="category_autocomplete.tpl" all=true}</p>
	<p>
		<b>Brand</b>
		<select name="brand_id" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
			<option value="">-- All --</option>
			{foreach from=$brand item=r}
				<option value="{$r.id}" {if $smarty.request.brand_id eq $r.id}selected{/if}>{$r.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		<b>SKU Type</b>
		<select name="sku_type" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
			<option value="">-- All --</option>
			{foreach from=$sku_type item=r}
				<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected{/if}>{$r.description}</option>
			{/foreach}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
		<b>Status</b>
		<select name="active" onchange="SKU_STOCK_BALANCE_LISTING.hide_errmsg();">
			<option value="">-- All --</option>
			<option value="1" {if $smarty.request.active eq '1'}selected{else}selected{/if}>Active</option>
			<option value="0" {if $smarty.request.active eq '0'}selected{/if}>Inactive</option>
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	</p>
    <br />
	<span style="color:blue">NOTE: The document will download in zip file format. Each csv file contains 30000 records.</span>
	<br/>
    <input class="btn btn-primary" type="button" onclick="SKU_STOCK_BALANCE_LISTING.download_report();" value="Download All"/>
    <br><br>
	<div class="div_tbl">
		<h3>Sample</h3>
		<table id="si_tbl" width="100%">
			<tr bgcolor="#ffffff">
				{foreach from=$sample_header item=v}
					<th>{$v}</th>
				{/foreach}
			</tr>
			{foreach from=$sample_list item=r}
				<tr>
					{foreach from=$r item=v}
						<td>{$v}</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
	<br><br>
</form>
<br>
{if $data}
	<p id="data">{$data}</p>
{/if}
{include file='footer.tpl'}
<script type="text/javascript">
{literal}
SKU_STOCK_BALANCE_LISTING.initialize();
{/literal}
</script>