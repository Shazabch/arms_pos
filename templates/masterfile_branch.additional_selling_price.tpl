{*
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var MST_BRANCH_ASP = {
	curr_id: undefined,
	form_element: undefined,
	initialize : function(){
		form_element = document.f_a;
	},

	form_submit: function(){
		if(!confirm("Are you sure want to save?")) return;

		form_element.submit();
	},

	show_branches_list: function(region_code, obj){
		if(!region_code || region_code == 0) return;
		
		if(obj.src.indexOf('expand')>0) obj.src = '/ui/collapse.gif';
		else obj.src = '/ui/expand.gif';
		
		var tr_branch_row_list = $$('#tbl_branch_list .branch_list_'+region_code);
		for(var i=0; i<tr_branch_row_list.length; i++){
			if(obj.src.indexOf('expand')>0){
				tr_branch_row_list[i].hide();
			}else{
				tr_branch_row_list[i].show();
			}
		}
	},
	
	region_sp_changed: function(region_code, obj){
		if(!region_code) return;
		
		if(!confirm("This will change all branches under this Branch Group automatically, \nare you sure want to proceed?")) return;
		
		var branch_row_list = $$('#tbl_branch_list .region_branch_'+region_code);
		for(var i=0; i<branch_row_list.length; i++){
			branch_row_list[i].value = obj.value;
		}
	}
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>
<form method="post" class="form" name="f_a">
<input type="hidden" name="a" value="save" />
<table width="30%" id="tbl_branch_list" cellpadding="4" cellspacing="1" border="0" style="border:1px solid #999; padding:5px; background-color:#fe9">
	<tr height="32" bgcolor="#ffffff">
		<th>Branch</th>
		<th>Additional<br />Selling Price</th>
	</tr>
	{foreach from=$branches key=bid item=r}
		<tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';">
			<td>{$r.code} {if $r.description}- {$r.description}{/if}</td>
			<td align="center">
				<input type="text" name="b_add_selling_price[{$bid}]" size="12" value="{$branch_data.$bid.additional_sp|number_format:2:'.':''}" class="r" onchange="mf(this);" />
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="2" align="center">- Branches not found -</td>
		</tr>
	{/foreach}
	{if $region}
		{foreach from=$region key=region_code item=b_list}
			<tr bgcolor="#abcccc" onmouseover="this.bgColor='#dddeee';" onmouseout="this.bgColor='#abcccc';">
				<td>
					<img src="/ui/expand.gif" onclick="javascript:void(MST_BRANCH_ASP.show_branches_list('{$region_code}', this));" align="absmiddle" height="12">
					{$config.masterfile_branch_region.$region_code.name}
				</td>
				<td align="center">
					<input type="text" name="region_add_selling_price[{$region_code}]" size="12" value="{$region_data.$region_code.additional_sp|number_format:2:'.':''}" class="r" onchange="mf(this); MST_BRANCH_ASP.region_sp_changed('{$region_code}', this);" />
				</td>
			</tr>
			{foreach from=$b_list key=bid item=b}
				<tr class="branch_list_{$region_code}" onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" style="display:none;">
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$b.code} {if $b.description}- {$b.description}{/if}</td>
					<td align="center">
						<input type="text" name="b_add_selling_price[{$bid}]" size="12" value="{$branch_data.$bid.additional_sp|number_format:2:'.':''}" class="r region_branch_{$region_code}" onchange="mf(this);" />
					</td>
				</tr>
			{/foreach}
		{/foreach}
	{/if}
</table>
<br />
<div>
<input type="button" name="save" value="Save" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="MST_BRANCH_ASP.form_submit();">
</div>
</form>
<script>
//init_chg(document.f_b);
MST_BRANCH_ASP.initialize();
</script>

{include file=footer.tpl}
